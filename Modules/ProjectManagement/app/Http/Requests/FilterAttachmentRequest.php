<?php

namespace Modules\ProjectManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
