<?php

namespace App\Http\Requests\LoanType;

use App\Enums\LoanTypeStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLoanTypeRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'prefix' => [
                'required',
                'string',
                'size:4',
                Rule::unique('loan_types', 'prefix')
                    ->ignore($this->route('loanType')),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                Rule::enum(LoanTypeStatus::class),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'نام نوع وام الزامی است.',

            'name.string' =>
                'نام نوع وام باید به صورت متن وارد شود.',

            'name.max' =>
                'نام نوع وام نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'prefix.required' =>
                'پیشوند نوع وام الزامی است.',

            'prefix.string' =>
                'پیشوند نوع وام باید به صورت متن وارد شود.',

            'prefix.size' =>
                'پیشوند نوع وام باید دقیقاً ۴ کاراکتر باشد.',

            'prefix.unique' =>
                'این پیشوند قبلاً برای یک نوع وام دیگر ثبت شده است.',

            'description.string' =>
                'توضیحات باید به صورت متن وارد شود.',

            'status.required' =>
                'وضعیت نوع وام الزامی است.',

            'status.enum' =>
                'وضعیت انتخاب‌شده معتبر نیست.',
        ];
    }
}
