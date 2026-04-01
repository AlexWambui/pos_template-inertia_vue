<?php

namespace App\Http\Requests\Products;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:200', Rule::unique('product_categories')->ignore($this->product_category)],
            // 'slug' => ['required', 'string', 'max:255', Rule::unique('product_categories')->ignore($this->product_category)],
            // 'sort_order' => ['integer'],
            'is_active' => ['boolean'],
        ];
    }
}
