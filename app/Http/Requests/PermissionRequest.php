<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Permission Form Request
 * 
 * Handles validation for permission creation and updates
 */
class PermissionRequest extends FormRequest
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
        $permissionId = $this->route('permission') ? $this->route('permission')->id : null;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9._-]+$/', // Only lowercase, numbers, dots, hyphens, underscores
                Rule::unique('idnbi_permissions', 'name')->ignore($permissionId),
            ],
            'display_name' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'group' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:100',
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
            'name' => 'permission name',
            'display_name' => 'display name',
            'description' => 'description',
            'group' => 'group',
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
            'name.regex' => 'The permission name may only contain lowercase letters, numbers, dots, hyphens, and underscores.',
            'name.unique' => 'A permission with this name already exists.',
        ];
    }
}
