<?php

namespace App\Http\Requests\LearningProgress;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCurriculumsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'curriculums' => ['required', 'array', 'list', 'min:1'],
            'curriculums.*' => ['required', 'array:id,status'],
            'curriculums.*.id' => [
                'required',
                Rule::exists('user_learning_progress', 'curriculum_id')
                    ->where('user_id', $this->user()->id),
            ],
            'curriculums.*.status' => [
                'required',
                'string',
                Rule::in(['completed', 'not_started']),
            ],
        ];
    }
}
