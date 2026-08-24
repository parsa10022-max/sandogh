<?php

namespace App\Http\Requests\LoanRequest;

use Illuminate\Foundation\Http\FormRequest;

class ApproveLoanRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'approved_amount' => [
                'required',
                'integer',
                'min:1',
            ],

            'loan_type_id' => [
                'required',
                'exists:loan_types,id',
            ],

            'approved_installment_count' => [
                'required',
                'integer',
                'min:1',
            ],

            'approved_installment_interval' => [
                'required',
                'integer',
                'in:1,2,3',
            ],

            'review_note' => [
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'approved_amount.required' =>
                'مبلغ تایید شده الزامی است.',

            'approved_amount.integer' =>
                'مبلغ تایید شده باید عدد صحیح باشد.',

            'approved_amount.min' =>
                'مبلغ تایید شده باید بیشتر از صفر باشد.',

            'loan_type_id.required' =>
                'نوع وام را انتخاب کنید.',

            'loan_type_id.exists' =>
                'نوع وام انتخاب شده معتبر نیست.',

            'approved_installment_count.required' =>
                'تعداد اقساط را وارد کنید.',

            'approved_installment_count.integer' =>
                'تعداد اقساط باید عدد صحیح باشد.',

            'approved_installment_count.min' =>
                'تعداد اقساط باید حداقل یک باشد.',

            'approved_installment_interval.required' =>
                'دوره بازپرداخت را انتخاب کنید.',

            'approved_installment_interval.in' =>
                'دوره بازپرداخت انتخاب شده معتبر نیست.',

            'review_note.required' =>
                'پیام تایید را وارد کنید.',
        ];
    }
}
