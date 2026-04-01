<?php

namespace App\Http\Requests\Products;

use Illuminate\Contracts\Validation\ValidationRule;
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($this->product)],
            'sku' => ['nullable', 'string', 'max:50', Rule::unique('products')->ignore($this->product)],
            'product_category_id' => ['nullable', 'exists:product_categories,id'],
            'buying_price' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'selling_price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'barcode' => ['nullable', 'string', 'max:50', Rule::unique('products')->ignore($this->product)],
            'is_active' => ['boolean'],
            'current_stock' => ['nullable', 'integer', 'min:0'],
            'weight_value' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'weight_unit' => ['nullable', 'string', 'in:kg,g,lbs,oz,pcs'],
            // 'sort_order' => ['integer'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'selling_price.required' => 'The selling price is required.',
            'selling_price.min' => 'The selling price must be at least 0.',
            'buying_price.decimal' => 'The buying price must have at most 2 decimal places.',
            'current_stock.integer' => 'The current stock must be a whole number.',
        ];
    }
}
