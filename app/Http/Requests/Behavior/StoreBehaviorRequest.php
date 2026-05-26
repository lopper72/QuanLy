<?php

namespace App\Http\Requests\Behavior;

use App\Models\TrainingSession;
use App\Models\TrainingSessionItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
                Rule::exists('children', 'id')->where(fn ($query) => $query
                    ->where('status', 'active')
                    ->whereNull('deleted_at')),
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
            'training_session_id' => ['nullable', 'integer', 'exists:training_sessions,id'],
            'training_session_item_id' => ['nullable', 'integer', 'exists:training_session_items,id'],
            'trigger' => ['nullable', 'string'],
            'response' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
            'recorded_at' => ['required', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $childId = (int) $this->input('child_id');
            $sessionId = $this->filled('training_session_id') ? (int) $this->input('training_session_id') : null;
            $itemId = $this->filled('training_session_item_id') ? (int) $this->input('training_session_item_id') : null;

            if ($sessionId) {
                $session = TrainingSession::find($sessionId);
                if ($session && (int) $session->child_id !== $childId) {
                    $validator->errors()->add('training_session_id', 'Buổi tập liên quan không thuộc trẻ đã chọn.');
                }
            }

            if ($itemId) {
                $item = TrainingSessionItem::find($itemId);

                if ($item && !$sessionId) {
                    $validator->errors()->add('training_session_id', 'Cần chọn buổi tập liên quan trước khi chọn bài tập.');
                }

                if ($item && $sessionId && (int) $item->training_session_id !== $sessionId) {
                    $validator->errors()->add('training_session_item_id', 'Bài tập liên quan không thuộc buổi tập đã chọn.');
                }
            }
        });
    }
}
