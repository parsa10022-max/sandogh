<?php

namespace App\Http\Requests\LoanRequest;

use Illuminate\Foundation\Http\FormRequest;

class ApproveLoanRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'approved_amount' => clean_money($this->approved_amount),
        ]);
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
                'مبلغ تأییدشده الزامی است.',

            'approved_amount.integer' =>
                'مبلغ تأییدشده باید عدد صحیح باشد.',

            'approved_amount.min' =>
                'مبلغ تأییدشده باید بیشتر از صفر باشد.',

            'loan_type_id.required' =>
                'نوع وام را انتخاب کنید.',

            'loan_type_id.exists' =>
                'نوع وام انتخاب‌شده معتبر نیست.',

            'approved_installment_count.required' =>
                'تعداد اقساط را وارد کنید.',

            'approved_installment_count.integer' =>
                'تعداد اقساط باید عدد صحیح باشد.',

            'approved_installment_count.min' =>
                'تعداد اقساط باید حداقل یک باشد.',

            'approved_installment_interval.required' =>
                'دوره بازپرداخت را انتخاب کنید.',

            'approved_installment_interval.integer' =>
                'دوره بازپرداخت باید عدد صحیح باشد.',

            'approved_installment_interval.in' =>
                'دوره بازپرداخت انتخاب‌شده معتبر نیست.',

            'review_note.required' =>
                'پیام تأیید را وارد کنید.',

            'review_note.string' =>
                'پیام تأیید باید به صورت متن وارد شود.',
        ];
    }
}
