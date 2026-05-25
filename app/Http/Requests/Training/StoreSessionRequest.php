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
            
            // Nested items validation
            'items' => ['nullable', 'array'],
            'items.*.exercise_id' => ['required', 'exists:exercises,id'],
            'items.*.sort_order' => ['nullable', 'integer'],
            'items.*.duration_minutes' => ['nullable', 'integer', 'min:0'],
            'items.*.completion_status' => ['nullable', 'string', 'in:not_started,completed,partially_completed,skipped'],
            'items.*.therapist_note' => ['nullable', 'string'],
        ];
    }
}
