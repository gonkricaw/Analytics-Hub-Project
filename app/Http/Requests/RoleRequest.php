<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Role Form Request
 * 
 * Handles validation for role creation and updates
 */
class RoleRequest extends FormRequest
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
        $roleId = $this->route('role') ? $this->route('role')->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-_]+$/', // Only lowercase, numbers, hyphens, underscores
                Rule::unique('idnbi_roles', 'name')->ignore($roleId),
            ],
            'display_name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'permission_ids' => [
                'sometimes',
                'array',
            ],
            'permission_ids.*' => [
                'exists:idnbi_permissions,id',
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
            'name' => 'role name',
            'display_name' => 'display name',
            'description' => 'description',
            'permission_ids' => 'permissions',
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
            'name.regex' => 'The role name may only contain lowercase letters, numbers, hyphens, and underscores.',
            'name.unique' => 'A role with this name already exists.',
            'permission_ids.*.exists' => 'One or more selected permissions do not exist.',
        ];
    }
}
