<?php

namespace Modules\ActivityLog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterActivityLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event' => ['sometimes', 'string'],
            'subject_type' => ['sometimes', 'string'],
            'subject_id' => ['sometimes', 'integer'],
            'causer_id' => ['sometimes', 'integer'],
        ];
    }
}
