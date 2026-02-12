<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Products\ProductCategory;
use App\Http\Requests\Products\ProductCategoryRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        if ($search) {
            // When searching, get flat list with parent info
            $categories = ProductCategory::with('parent')
                ->where('name', 'like', "%{$search}%")
                ->orderBy('name')
                ->paginate(20)
                ->through(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'parent_id' => $category->parent_id,
                        'parent_name' => $category->parent?->name,
                        'is_active' => $category->is_active,
                        'sort_order' => $category->sort_order,
                        'path' => $this->getPath($category),
                    ];
                });
            
            return Inertia::render('products/categories/Index', [
                'categories' => $categories,
                'filters' => $request->only(['search']),
                'isSearching' => true,
            ]);
        } else {
            // FIXED: Get ONLY root categories (no parent)
            // The children will be loaded via the with() clause
            $rootCategories = ProductCategory::with(['children' => function ($query) {
                $query->orderBy('sort_order')->orderBy('name')
                    ->with(['children' => function ($q) {
                        $q->orderBy('sort_order')->orderBy('name')
                            ->with(['children' => function ($qq) {
                                $qq->orderBy('sort_order')->orderBy('name');
                            }]);
                    }]);
            }])
            ->whereNull('parent_id') // THIS IS CRITICAL - ONLY GET ROOT CATEGORIES
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return $this->formatCategoryTree($category);
            });

            return Inertia::render('products/categories/Index', [
                'categories' => $rootCategories->toArray(), // Now this is ONLY root categories
                'filters' => $request->only(['search']),
                'isSearching' => false,
            ]);
        }
    }

    public function create()
    {
        $parentCategories = ProductCategory::active()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('products/categories/Create', [
            'parentCategories' => $parentCategories,
        ]);
    }

    public function store(ProductCategoryRequest $request)
    {
        ProductCategory::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('product-categories.index')
            ->with('message', 'Category created successfully.')
            ->with('type', 'success');
    }

    public function edit(ProductCategory $productCategory)
    {
        $productCategory->load('children');
        
        $parentCategories = ProductCategory::active()
            ->whereNull('parent_id')
            ->where('id', '!=', $productCategory->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('products/categories/Edit', [
            'category' => $productCategory,
            'parentCategories' => $parentCategories,
            'hasChildren' => $productCategory->children()->exists(),
        ]);
    }

    public function update(ProductCategoryRequest $request, ProductCategory $productCategory)
    {
        if ($request->parent_id == $productCategory->id) {
            return back()->withErrors(['parent_id' => 'Category cannot be its own parent']);
        }

        if ($request->parent_id && $productCategory->isDescendantOf($request->parent_id)) {
            return back()->withErrors(['parent_id' => 'Cannot set parent to a child category']);
        }

        $productCategory->update([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->sort_order ?? $productCategory->sort_order,
        ]);

        return redirect()->route('product-categories.index')
            ->with('message', 'Category updated successfully.')
            ->with('type', 'success');
    }

    public function destroy(ProductCategory $productCategory)
    {
        if ($productCategory->products()->exists()) {
            return redirect()->route('product-categories.index')
                ->with('message', 'Cannot delete category that has products.')
                ->with('type', 'error');
        }

        if ($productCategory->children()->exists()) {
            return redirect()->route('product-categories.index')
                ->with('message', 'Cannot delete category that has subcategories.')
                ->with('type', 'error');
        }

        $productCategory->delete();

        return redirect()->route('product-categories.index')
            ->with('message', 'Category deleted successfully.')
            ->with('type', 'success');
    }

    private function formatCategoryTree($category)
    {
        $formatted = [
            'id' => $category->id,
            'name' => $category->name,
            'parent_id' => $category->parent_id,
            'is_active' => $category->is_active,
            'sort_order' => $category->sort_order,
            'children' => [],
        ];

        if ($category->children && $category->children->count() > 0) {
            $formatted['children'] = $category->children
                ->sortBy('sort_order')
                ->sortBy('name') // Secondary sort by name
                ->values()
                ->map(function ($child) {
                    return $this->formatCategoryTree($child);
                })
                ->toArray();
        }

        return $formatted;
    }

    private function getDepth($category, $depth = 0)
    {
        if (!$category->parent) {
            return $depth;
        }
        
        return $this->getDepth($category->parent, $depth + 1);
    }

    private function getPath($category)
    {
        $path = [];
        $current = $category;
        
        while ($current->parent) {
            array_unshift($path, $current->parent->name);
            $current = $current->parent;
        }
        
        return implode(' → ', $path);
    }
}