<?php

namespace Modules\ProjectManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stage_id' => ['sometimes', 'integer', 'exists:project_stages,id'],
            'field_values' => ['sometimes', 'array'],
            'field_values.*.field_id' => ['required', 'integer', 'exists:project_form_fields,id'],
            'field_values.*.value' => ['required'],
        ];
    }
}
