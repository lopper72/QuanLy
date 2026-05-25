<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'child_id' => ['required', 'exists:children,id'],
            'report_type' => ['required', 'string', 'in:daily,weekly,monthly,custom'],
            'report_date' => ['required', 'date'],
            'summary' => ['nullable', 'string'],
        ];
    }
}