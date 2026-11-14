<?php

namespace App\Http\Requests\Household\Transactions;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRequest extends FormRequest
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
            'date' => ['required', 'date_format:Y-m-d'],
            'categoryId' => ['required', 'integer', Rule::exists('household_categories', 'id')],
            'memo' => ['nullable', 'string', 'max:50'],
            'amount' => ['required', 'integer', 'gt:0'],
        ];
    }
}
