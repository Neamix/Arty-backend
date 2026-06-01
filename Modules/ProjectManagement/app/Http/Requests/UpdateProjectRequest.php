<?php

namespace Modules\ProjectManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'card_title_field_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('project_form_fields', 'id')
                    ->where('project_id', $this->route('project')->id),
            ],
        ];
    }
}
