<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Products\Product;
use App\Models\Products\ProductCategory;
use App\Http\Requests\Products\ProductRequest;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('categories')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "{%$search%}")
                    ->orWhere('barcode', 'like', "{%$search%}");
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('products/Index', [
            'products' => $products,
            'filters' => request()->only(['search']),
        ]);
    }

    public function create()
    {
        $categories = ProductCategory::active()->get(['id', 'name', 'parent_id']);

        return Inertia::render('products/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(ProductRequest $request)
    {
        $product = Product::create([
            'name' => $request->name,
            'sku' => $request->sku,
            'barcode' => $request->barcode,
            'buying_price' => $request->buying_price,
            'selling_price' => $request->selling_price,
            'unit_of_measurement' => $request->unit_of_measurement,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        }

        return redirect()->route('products.index')->with(['message' => 'Product created successfully', 'type' => 'success']);
    }

    public function edit(Product $product)
    {
        $product->load('categories');
        $categories = ProductCategory::active()->get(['id', 'name', 'parent_id']);

        return Inertia::render('products/Edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update([
            'name' => $request->name,
            'sku' => $request->sku,
            'barcode' => $request->barcode,
            'buying_price' => $request->buying_price,
            'selling_price' => $request->selling_price,
            'unit_of_measurement' => $request->unit_of_measurement,
            'current_stock' => $request->current_stock ?? $product->current_stock,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        }

        return redirect()->route('products.index')
            ->with('message', 'Product updated successfully.')
            ->with('type', 'success');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('message', 'Product deleted successfully.')
            ->with('type', 'success');
    }
}
