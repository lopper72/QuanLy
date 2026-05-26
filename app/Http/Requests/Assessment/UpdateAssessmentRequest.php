<?php

namespace App\Http\Requests\Assessment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'child_id' => [
                'required',
                Rule::exists('children', 'id')->where(fn ($query) => $query
                    ->where('status', 'active')
                    ->whereNull('deleted_at')),
            ],
            'assessment_date' => ['required', 'date'],
            'overall_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.skill_name' => [
                'required',
                'string',
                Rule::in([
                    'gross_motor',
                    'fine_motor',
                    'receptive_language',
                    'expressive_language',
                    'social_interaction',
                    'self_care',
                    'sensory_processing',
                    'attention',
                    'imitation',
                    'play_skill',
                ]),
            ],
            'items.*.score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'items.*.level' => ['nullable', 'string', Rule::in(['emerging', 'developing', 'achieved', 'regression'])],
            'items.*.note' => ['nullable', 'string'],
        ];
    }
}
