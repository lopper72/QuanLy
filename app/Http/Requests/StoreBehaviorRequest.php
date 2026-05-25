<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBehaviorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'child_id' => ['required', 'integer'],
            'event_date' => ['required', 'date'],
            'description' => ['required', 'string'],
        ];
    }
}
