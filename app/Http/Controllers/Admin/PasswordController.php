<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    /**
     * نمایش صفحه تغییر رمز عبور
     */
    public function edit()
    {
        return view('admin.password.edit');
    }

    /**
     * بروزرسانی رمز عبور
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate(
            [
                'current_password' => [
                    'required',
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
            ],
            [
                'current_password.required' =>
                    'وارد کردن رمز عبور فعلی الزامی است.',

                'password.required' =>
                    'وارد کردن رمز عبور جدید الزامی است.',

                'password.string' =>
                    'رمز عبور جدید باید به صورت متن وارد شود.',

                'password.min' =>
                    'رمز عبور جدید باید حداقل ۸ کاراکتر باشد.',

                'password.confirmed' =>
                    'تکرار رمز عبور جدید با رمز عبور وارد شده مطابقت ندارد.',
            ]
        );

        if (! Hash::check(
            $validated['current_password'],
            $user->password
        )) {
            throw ValidationException::withMessages([
                'current_password' => 'رمز عبور فعلی صحیح نیست.',
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('admin.password.edit')
            ->with(
                'success',
                'رمز عبور با موفقیت تغییر کرد.'
            );
    }
}
