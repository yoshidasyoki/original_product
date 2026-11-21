<?php

namespace App\Http\Requests\Investment\PastSimulations;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
            'startMonth' => [
                'required',
                'date_format:Y-m',
                'before_or_equal:endMonth'
            ],
            'endMonth' => [
                'required',
                'date_format:Y-m',
                'after_or_equal:startMonth'
            ],
            'monthlyAmount' => ['required', 'integer', 'min:1000'],
            'currency' => ['required', 'string'],
        ];
    }
}
