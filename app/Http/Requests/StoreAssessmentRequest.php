<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'child_id' => ['required', 'integer'],
            'assessment_date' => ['required', 'date'],
            'score' => ['nullable', 'numeric'],
        ];
    }
}
