<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * نمایش پروفایل مدیر
     */
    public function index()
    {
        $user = auth()->user();

        return view(
            'admin.profile.index',
            compact('user')
        );
    }

    /**
     * بروزرسانی پروفایل مدیر
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->merge([
            'username' => trim((string) $request->username),
            'mobile' => $request->mobile
                ? preg_replace(
                    '/\D/u',
                    '',
                    strtr($request->mobile, [
                        '۰' => '0',
                        '۱' => '1',
                        '۲' => '2',
                        '۳' => '3',
                        '۴' => '4',
                        '۵' => '5',
                        '۶' => '6',
                        '۷' => '7',
                        '۸' => '8',
                        '۹' => '9',
                        '٠' => '0',
                        '١' => '1',
                        '٢' => '2',
                        '٣' => '3',
                        '٤' => '4',
                        '٥' => '5',
                        '٦' => '6',
                        '٧' => '7',
                        '٨' => '8',
                        '٩' => '9',
                    ])
                )
                : null,
            'email' => $request->email
                ? trim((string) $request->email)
                : null,
        ]);

        $validated = $request->validate(
            [
                'username' => [
                    'required',
                    'string',
                    'max:255',
                    'unique:users,username,' . $user->id,
                ],

                'mobile' => [
                    'nullable',
                    'string',
                    'max:20',
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                    'unique:users,email,' . $user->id,
                ],
            ],
            [
                'username.required' =>
                    'وارد کردن نام کاربری الزامی است.',

                'username.string' =>
                    'نام کاربری باید به صورت متن باشد.',

                'username.max' =>
                    'نام کاربری نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',

                'username.unique' =>
                    'این نام کاربری قبلاً استفاده شده است.',

                'mobile.string' =>
                    'شماره موبایل باید به صورت متن باشد.',

                'mobile.max' =>
                    'شماره موبایل نمی‌تواند بیشتر از ۲۰ کاراکتر باشد.',

                'email.email' =>
                    'فرمت ایمیل واردشده صحیح نیست.',

                'email.max' =>
                    'ایمیل نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',

                'email.unique' =>
                    'این ایمیل قبلاً استفاده شده است.',
            ]
        );

        $user->update($validated);

        return redirect()
            ->route('admin.profile.index')
            ->with(
                'success',
                'اطلاعات پروفایل با موفقیت بروزرسانی شد.'
            );
    }
}
