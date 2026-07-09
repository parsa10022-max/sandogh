<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
           'member_code' => 'required|unique:users,member_code|max:10',
            'name' => 'required|max:150',
            'family' => 'required|max:255',
            'national_code' => 'required|digits:10|unique:users,national_code',
            'mobile' => 'required|digits:11|regex:/^09[0-9]{9}$/|unique:users,mobile',
            'sheba' => 'nullable|size:26',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'password' => 'required|min:6',
        ];
    }
}
