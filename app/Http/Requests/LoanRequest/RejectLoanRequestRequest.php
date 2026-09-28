<?php

namespace App\Http\Requests\LoanRequest;

use Illuminate\Foundation\Http\FormRequest;

class RejectLoanRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'review_note' => [
                'required',
                'string',
            ],

            'next_review_date' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'review_note.required' =>
                'پیام رد درخواست را وارد کنید.',

            'review_note.string' =>
                'پیام رد درخواست باید به صورت متن وارد شود.',

            'next_review_date.string' =>
                'تاریخ بررسی بعدی معتبر نیست.',
        ];
    }
}
