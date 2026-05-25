<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSessionRequest extends FormRequest
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
            'session_date' => ['required', 'date'],
            'scheduled_time' => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:planned,in_progress,completed,skipped,not_completed,need_help'],
            'notes' => ['nullable', 'string'],
            'combo_ids' => ['nullable', 'array'],
            'combo_ids.*' => ['integer', 'exists:exercise_combos,id'],
            
            // Nested items validation
            'items' => ['nullable', 'array'],
            'items.*.exercise_id' => ['required', 'exists:exercises,id'],
            'items.*.sort_order' => ['nullable', 'integer'],
            'items.*.duration_minutes' => ['nullable', 'integer', 'min:0'],
            'items.*.completion_status' => ['nullable', 'string', 'in:not_started,completed,partially_completed,skipped'],
            'items.*.therapist_note' => ['nullable', 'string'],
            'exercise_items' => ['nullable', 'array'],
            'exercise_items.*.exercise_id' => ['required', 'exists:exercises,id'],
            'exercise_items.*.sort_order' => ['nullable', 'integer'],
            'exercise_items.*.duration_minutes' => ['nullable', 'integer', 'min:0'],
            'exercise_items.*.completion_status' => ['nullable', 'string', 'in:not_started,completed,partially_completed,skipped'],
            'exercise_items.*.therapist_note' => ['nullable', 'string'],
        ];
    }
}
