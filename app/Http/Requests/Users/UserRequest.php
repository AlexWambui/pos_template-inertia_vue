<?php

namespace App\Http\Requests\Users;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\UserRoles;
use Illuminate\Validation\Rules\Enum;

class UserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', new Enum(UserRoles::class)],

            // Common fields (for multiple roles)
            'branch_id' => ['nullable', 'exists:branches,id'],

            // Cashier validation (role = 2)
            'position' => ['required_if:role,' . UserRoles::CASHIER->value, 'nullable', 'string'],
            'hired_at' => ['nullable', 'date'],

            // Supplier validation (role = 3)
            'company_name' => ['required_if:role,' . UserRoles::SUPPLIER->value, 'nullable', 'string', 'max:255'],
            'payment_terms' => ['required_if:role,' . UserRoles::SUPPLIER->value, 'nullable', 'string'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],

            // Customer validation (role = 4)
            'loyalty_points' => ['nullable', 'integer', 'min:0'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'position.required_if' => 'Position is required for cashier accounts',
            'company_name.required_if' => 'Company name is required for supplier accounts',
            'payment_terms.required_if' => 'Payment terms are required for supplier accounts',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert string role to integer if needed
        if ($this->has('role') && is_string($this->role)) {
            $this->merge([
                'role' => (int) $this->role,
            ]);
        }
    }
}
