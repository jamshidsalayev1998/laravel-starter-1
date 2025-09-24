<?php

namespace App\Http\Requests\Api\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class CreateRoleRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
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
            'name.required' => 'Role nomi kiritilishi shart',
            'name.string' => 'Role nomi matn ko\'rinishida bo\'lishi kerak',
            'name.max' => 'Role nomi 255 ta belgidan ko\'p bo\'lmasligi kerak',
            'name.unique' => 'Bu role nomi allaqachon mavjud',
            'permissions.array' => 'Permissions array ko\'rinishida bo\'lishi kerak',
            'permissions.*.string' => 'Har bir permission matn ko\'rinishida bo\'lishi kerak',
            'permissions.*.exists' => 'Tanlangan permission mavjud emas',
        ];
    }
}
