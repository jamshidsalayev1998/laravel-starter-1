<?php

namespace App\Http\Requests\Api\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class AssignPermissionToRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('users.edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
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
            'permissions.required' => 'Permissions kiritilishi shart',
            'permissions.array' => 'Permissions array ko\'rinishida bo\'lishi kerak',
            'permissions.*.string' => 'Har bir permission matn ko\'rinishida bo\'lishi kerak',
            'permissions.*.exists' => 'Tanlangan permission mavjud emas',
        ];
    }
}
