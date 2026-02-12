<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $product_id = $this->route('product') ? $this->route('product')->id : null;

        return [
            'name' => ['required', 'string', 'max:120'],
            'sku' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->ignore($product_id)
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'barcode')->ignore($product_id)
            ],
            'buying_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'unit_of_measurement' => ['nullable', 'string', 'max:20'],
            'current_stock' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:product_categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required',
            'selling_price.required' => 'Selling price is required',
            'selling_price.min' => 'Selling price must be greater than 0',
            'sku.unique' => 'This SKU is already in use',
            'barcode.unique' => 'This barcode is already in use',
        ];
    }
}
