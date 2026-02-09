<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Enums\UserRoles;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** var User|null $user */
        $user = $this->route('user');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user?->id),
            ],
            'role' => ['required', 'integer', 'in:' . implode(',', array_column(UserRoles::cases(), 'value'))],

            // Staff Profile (conditional)
            'position' => 'required_if:role,' . UserRoles::CASHIER->value,
            'branch_id' => 'required_if:role,' . UserRoles::CASHIER->value . '|exists:branches,id',

            // Customer profile
            'credit_limit' => 'nullable|numeric|min:0',
            'loyalty_points' => 'nullable|integer|min:0',

            // Supplier profile
            'company_name' => 'required_if:role,' . UserRoles::SUPPLIER->value,
            'payment_terms' => 'required_if:role,' . UserRoles::SUPPLIER->value,
        ];


        if (!$user || $this->filled('password')) {
            $rules['password'] = ['required', 'string', 'min:8'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower($this->email),
        ]);
    }
}
