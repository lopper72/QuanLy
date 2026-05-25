<?php

namespace App\Http\Requests\Exercise;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExerciseRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $exercise = $this->route('exercise');
        $exerciseId = $exercise instanceof \App\Models\Exercise ? $exercise->id : $exercise;

        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:exercises,slug,' . $exerciseId,
            'category' => 'required|string|max:100|in:gross_motor,fine_motor,sensory,communication,cognitive,social,self_care',
            'difficulty' => 'nullable|string|max:100|in:easy,medium,hard',
            'instructions' => 'nullable|string',
            'description' => 'nullable|string',
            'target_skill' => 'nullable|string|max:100',
            'recommended_age' => 'nullable|string|max:100',
            'required_tools' => 'nullable|string',
            'expected_benefits' => 'nullable|string',
            'safety_notes' => 'nullable|string',
            'parent_tips' => 'nullable|string',
            'weekly_expectation' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:2048',
            'video' => 'nullable|file|mimetypes:video/mp4,video/quicktime|max:20480',
            'video_url' => 'nullable|url|max:255',
            'estimated_minutes' => 'nullable|integer|min:1|max:180',
            'is_active' => 'boolean',
            'steps' => 'nullable|array',
            'steps.*.title' => 'required|string|max:255',
            'steps.*.instruction' => 'nullable|string',
            'steps.*.image' => 'nullable|image|max:2048',
            'steps.*.image_path' => 'nullable|string',
        ];
    }
}
