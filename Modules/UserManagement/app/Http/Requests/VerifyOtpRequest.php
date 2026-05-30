<?php

namespace Modules\UserManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\UserManagement\Enums\OtpUsage;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'digits:'.config('usermanagement.otp.length', 6)],
            'usage' => ['required', 'string', 'in:'.implode(',', array_column(OtpUsage::cases(), 'value'))],
        ];
    }
}
