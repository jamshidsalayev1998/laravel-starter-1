<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'email' => 'nullable|email|unique:users,email',
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
            'name.required' => 'Ism kiritilishi shart',
            'name.string' => 'Ism matn ko\'rinishida bo\'lishi kerak',
            'name.max' => 'Ism 255 ta belgidan ko\'p bo\'lmasligi kerak',
            'phone.required' => 'Telefon raqam kiritilishi shart',
            'phone.string' => 'Telefon raqam matn ko\'rinishida bo\'lishi kerak',
            'phone.unique' => 'Bu telefon raqam allaqachon ro\'yxatdan o\'tgan',
            'password.required' => 'Parol kiritilishi shart',
            'password.string' => 'Parol matn ko\'rinishida bo\'lishi kerak',
            'password.min' => 'Parol kamida 8 ta belgidan iborat bo\'lishi kerak',
            'password.confirmed' => 'Parol tasdiqlanmadi',
            'email.email' => 'Email to\'g\'ri formatda bo\'lishi kerak',
            'email.unique' => 'Bu email allaqachon ro\'yxatdan o\'tgan',
        ];
    }
}
