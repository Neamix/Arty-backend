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
            'project_stage_id' => [
                'required',
                'integer',
                Rule::exists('project_stages', 'id')
                    ->where('project_id', $this->route('project')->id),
            ],
            'before_lead_id' => [
                'nullable',
                'integer',
                Rule::exists('project_leads', 'id')
                    ->where('project_stage_id', $this->input('project_stage_id')),
            ],
            'after_lead_id' => [
                'nullable',
                'integer',
                Rule::exists('project_leads', 'id')
                    ->where('project_stage_id', $this->input('project_stage_id')),
            ],
        ];
    }
}
