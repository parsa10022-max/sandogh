<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_code' => [
                'required',
                'unique:users,member_code',
                'max:10',
            ],

            'name' => [
                'required',
                'max:150',
            ],

            'family' => [
                'required',
                'max:255',
            ],

            'national_code' => [
                'required',
                'digits:10',
                'unique:users,national_code',
            ],

            'mobile' => [
                'required',
                'digits:11',
                'regex:/^09[0-9]{9}$/',
                'unique:users,mobile',
            ],

            'sheba' => [
                'nullable',
                'size:26',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'password' => [
                'required',
                'min:6',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'member_code.required' =>
                'کد عضویت الزامی است.',

            'member_code.unique' =>
                'این کد عضویت قبلاً ثبت شده است.',

            'member_code.max' =>
                'کد عضویت نمی‌تواند بیشتر از ۱۰ کاراکتر باشد.',

            'name.required' =>
                'نام الزامی است.',

            'name.max' =>
                'نام نمی‌تواند بیشتر از ۱۵۰ کاراکتر باشد.',

            'family.required' =>
                'نام خانوادگی الزامی است.',

            'family.max' =>
                'نام خانوادگی نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',

            'national_code.required' =>
                'کد ملی الزامی است.',

            'national_code.digits' =>
                'کد ملی باید ۱۰ رقم باشد.',

            'national_code.unique' =>
                'این کد ملی قبلاً ثبت شده است.',

            'mobile.required' =>
                'شماره موبایل الزامی است.',

            'mobile.digits' =>
                'شماره موبایل باید ۱۱ رقم باشد.',

            'mobile.regex' =>
                'شماره موبایل باید با ۰۹ شروع شود و ۱۱ رقم باشد.',

            'mobile.unique' =>
                'این شماره موبایل قبلاً ثبت شده است.',

            'sheba.size' =>
                'شماره شبا باید ۲۶ کاراکتر باشد.',

            'image.image' =>
                'فایل انتخاب‌شده باید تصویر باشد.',

            'image.mimes' =>
                'فرمت تصویر باید jpg، jpeg، png یا webp باشد.',

            'image.max' =>
                'حجم تصویر نمی‌تواند بیشتر از ۲ مگابایت باشد.',

            'password.required' =>
                'رمز عبور الزامی است.',

            'password.min' =>
                'رمز عبور باید حداقل ۶ کاراکتر باشد.',
        ];
    }
}
