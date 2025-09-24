<?php

namespace App\Http\Requests\Api\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class BanUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('users.ban');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:500',
            'expires_at' => 'nullable|date|after:now',
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
            'reason.required' => 'Ban sababi kiritilishi shart',
            'reason.string' => 'Ban sababi matn ko\'rinishida bo\'lishi kerak',
            'reason.max' => 'Ban sababi 500 ta belgidan ko\'p bo\'lmasligi kerak',
            'expires_at.date' => 'Ban muddati to\'g\'ri formatda bo\'lishi kerak',
            'expires_at.after' => 'Ban muddati hozirgi vaqtdan keyin bo\'lishi kerak',
        ];
    }
}
