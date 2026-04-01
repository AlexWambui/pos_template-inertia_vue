<?php

namespace App\Http\Controllers\Products;

use App\Models\Products\Product;
use App\Models\Products\ProductCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\Products\ProductRequest;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category:id,name')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('products/Index', [
            'products' => $products->items(),
            'total' => $products->total(),
            'filters' => request()->only(['search'])
        ]);
    }

    public function create()
    {
        $categories = ProductCategory::active()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('products/Create', [
            'categories' => $categories
        ]);
    }

    public function store(ProductRequest $request)
    {
        $validated = $request->validated();

        Product::create($validated);

        return redirect()->route('products.index')
            ->with([
                'message' => 'Product created successfully',
                'type' => 'success'
            ]);
    }

    public function edit(Product $product)
    {
        $product->load('category');
        $categories = ProductCategory::active()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('products/Edit', [
            'product' => $product,
            'categories' => $categories
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $validated = $request->validated();

        $product->update($validated);

        return redirect()->route('products.index')
            ->with([
                'message' => 'Product updated successfully',
                'type' => 'success'
            ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('message', 'Product deleted successfully.')
            ->with('type', 'success');
    }

    public function toggleStatus( Product $product)
    {
        $product->update([
            'is_active' => !$product->is_active,
        ]);

        return redirect()->back()
            ->with([
                'message' => 'Product status updated successfully', 
                'type' => 'success'
            ]);
    }
}
