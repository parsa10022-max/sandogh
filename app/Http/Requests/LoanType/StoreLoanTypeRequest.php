<?php

namespace App\Http\Requests\LoanType;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\LoanTypeStatus;


class StoreLoanTypeRequest extends FormRequest
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
                 'name' => ['required', 'string', 'max:100'],
                 'prefix' => ['required', 'string', 'size:4', 'unique:loan_types,prefix'],
                 'description' => ['nullable', 'string'],
                 'is_active' => ['required', Rule::enum(LoanTypeStatus::class)],
             ];

    }
}
