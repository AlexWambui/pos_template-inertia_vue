<?php

namespace App\Http\Controllers\Products;

use App\Models\Products\ProductCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\Products\ProductCategoryRequest;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::query()
            ->withCount('products')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(2);

        return Inertia::render('products/categories/Index', [
            'categories' => $categories->items(),
            'total' => $categories->total(),
            'filters' => request()->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('products/categories/Create');
    }

    public function store(ProductCategoryRequest $request)
    {
        ProductCategory::create([
            'name' => $request->name,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('product-categories.index')
            ->with([
                'message' => 'Category created successfully',
                'type' => 'success'
            ]);
    }

    public function edit(ProductCategory $product_category)
    {
        return Inertia::render('products/categories/Edit', [
            'category' => $product_category
        ]);
    }

    public function update(ProductCategoryRequest $request, ProductCategory $product_category)
    {
        $product_category->update([
            'name' => $request->name,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('product-categories.index')
            ->with([
                'message' => 'Category updated successfully',
                'type' => 'success'
            ]);
    }

    public function destroy(ProductCategory $product_category)
    {
        $product_category->delete();

        return redirect()->route('product-categories.index')
            ->with([
                'message' => 'Category deleted successfully',
                'type' => 'success'
            ]);
    }
}
