<?php

namespace App\Http\Requests\CustomerActivation;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $mobile = $this->mobile;

        if ($mobile !== null) {
            $mobile = str_replace(
                ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
                ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                $mobile
            );

            $mobile = preg_replace('/\D/', '', $mobile);
        }

        $this->merge([
            'mobile' => $mobile,
        ]);
    }

    public function rules(): array
    {
        return [
            'mobile' => [
                'required',
                'string',
                'digits:11',
                'starts_with:09',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required' => 'شماره موبایل را وارد کنید.',
            'mobile.digits' => 'شماره موبایل باید ۱۱ رقم باشد.',
            'mobile.starts_with' => 'شماره موبایل باید با ۰۹ شروع شود.',
        ];
    }
}
