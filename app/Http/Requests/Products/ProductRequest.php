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
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($this->product)],
            'sku' => ['nullable', 'string', 'max:50', Rule::unique('products')->ignore($this->product)],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'buying_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'barcode' => ['nullable', 'string', 'max:50', Rule::unique('products')->ignore($this->product)],
            'is_active' => ['boolean'],
            'current_stock' => ['integer', 'min:0'],
            'weight_value' => ['nullable', 'numeric', 'min:0'],
            'weight_unit' => ['nullable', 'string', 'in:kg,g,lbs,oz'],
            'sort_order' => ['integer'],
        ];
    }
}
