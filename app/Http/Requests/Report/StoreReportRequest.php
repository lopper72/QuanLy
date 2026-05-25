<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
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
            'report_type' => ['required', 'string', 'in:daily,weekly,monthly,custom'],
            'report_date' => ['required', 'date'],
            'summary' => ['nullable', 'string'],
        ];
    }
}
