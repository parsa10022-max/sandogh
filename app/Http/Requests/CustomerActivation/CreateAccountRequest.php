<?php

namespace App\Http\Requests\CustomerActivation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CreateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $username = $this->username;

        if ($username !== null) {
            $username = trim($username);
        }

        $this->merge([
            'username' => $username,
        ]);
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:4',
                'max:100',
                'regex:/^[A-Za-z0-9_]+$/',
                'unique:users,username',
            ],

            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' =>
                'نام کاربری را وارد کنید.',

            'username.min' =>
                'نام کاربری باید حداقل ۴ کاراکتر باشد.',

            'username.max' =>
                'نام کاربری نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'username.regex' =>
                'نام کاربری فقط می‌تواند شامل حروف انگلیسی، اعداد و _ باشد.',

            'username.unique' =>
                'این نام کاربری قبلاً استفاده شده است.',

            'password.required' =>
                'رمز عبور را وارد کنید.',

            'password.confirmed' =>
                'تکرار رمز عبور با رمز عبور یکسان نیست.',
        ];
    }
}
