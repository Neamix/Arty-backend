<?php

namespace Modules\UserManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResendEmailVerificationRequest extends FormRequest
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
