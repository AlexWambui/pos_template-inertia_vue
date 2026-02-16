<?php

namespace App\Http\Requests\Branches;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Branch;

class BranchRequest extends FormRequest
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
        $branch_id = $this->route('branch') ? $this->route('branch')->id : null;
        return [
            'name' => ['required', 'string', 'max:120'],
            'code' => [
                'required', 
                'string', 
                'max:30',
                Rule::unique('branches', 'code')->ignore($branch_id),
            ],
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'string', 'email'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Branch name is required',
            'code.required' => 'Branch code is required',
        ];
    }
}
