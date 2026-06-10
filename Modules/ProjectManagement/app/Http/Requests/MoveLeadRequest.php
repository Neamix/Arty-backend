<?php

namespace Modules\ProjectManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stage_id' => [
                'required',
                'integer',
                Rule::exists('stages', 'id')
                    ->where('project_id', $this->route('project')->id),
            ],
            'before_lead_id' => [
                'nullable',
                'integer',
                Rule::exists('leads', 'id')
                    ->where('stage_id', $this->input('stage_id')),
            ],
            'after_lead_id' => [
                'nullable',
                'integer',
                Rule::exists('leads', 'id')
                    ->where('stage_id', $this->input('stage_id')),
            ],
        ];
    }
}
