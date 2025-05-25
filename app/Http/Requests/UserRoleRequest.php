<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * User Role Assignment Form Request
 * 
 * Handles validation for assigning roles to users
 */
class UserRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization will be handled by policies and middleware
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role_ids' => [
                'required',
                'array',
            ],
            'role_ids.*' => [
                'exists:idnbi_roles,id',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'role_ids' => 'roles',
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
            'role_ids.required' => 'Please select at least one role.',
            'role_ids.*.exists' => 'One or more selected roles do not exist.',
        ];
    }
}
