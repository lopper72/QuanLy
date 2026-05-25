<?php

namespace App\Http\Requests\Behavior;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBehaviorRequest extends FormRequest
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
                Rule::exists('children', 'id')->where(fn ($query) => $query->where('status', 'active')),
            ],
            'behavior_type' => [
                'required',
                'string',
                'max:100',
                Rule::in([
                    'tantrum',
                    'avoidance',
                    'sensory_seeking',
                    'aggression',
                    'self_stimulation',
                    'difficulty_transitioning',
                    'poor_sleep',
                    'picky_eating',
                    'other',
                ]),
            ],
            'severity' => [
                'nullable',
                'string',
                'max:50',
                Rule::in(['low', 'medium', 'high']),
            ],
            'trigger' => ['nullable', 'string'],
            'response' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
            'recorded_at' => ['required', 'date'],
        ];
    }
}
