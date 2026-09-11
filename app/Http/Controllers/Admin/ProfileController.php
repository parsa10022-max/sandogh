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

        return view('admin.profile.index', compact('user'));
    }

    /**
     * بروزرسانی پروفایل مدیر
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
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
        ]);

        $user->update($validated);

        return redirect()
            ->route('admin.profile.index')
            ->with('success', 'اطلاعات پروفایل با موفقیت بروزرسانی شد.');
    }
}
