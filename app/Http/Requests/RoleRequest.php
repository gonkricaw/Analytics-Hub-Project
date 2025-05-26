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
                $this->isMethod('POST') ? 'required' : 'sometimes',
                'string',
                'max:255',
                'regex:/^[a-z0-9-_]+$/', // Only lowercase, numbers, hyphens, underscores
                Rule::unique('idnbi_roles', 'name')->ignore($roleId),
            ],
            'display_name' => [
                $this->isMethod('POST') ? 'required' : 'sometimes',
                'string',
                'max:255',
            ],
            'description' => [
                $this->isMethod('POST') ? 'required' : 'sometimes',
                'string',
                'max:1000',
            ],
            'color' => [
                $this->isMethod('POST') ? 'required' : 'sometimes',
                'string',
                'regex:/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', // Valid hex color
            ],
            'is_system' => [
                'sometimes',
                'boolean',
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
            'color' => 'color',
            'is_system' => 'system role flag',
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
            'color.regex' => 'The color must be a valid hex color code (e.g., #FF5722 or #F00).',
            'permission_ids.*.exists' => 'One or more selected permissions do not exist.',
        ];
    }
}
