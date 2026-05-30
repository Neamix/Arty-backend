<?php

namespace Modules\UserManagement\Enums;

enum OtpUsage: string
{
    case PasswordReset = 'password_reset';
    case EmailVerification = 'email_verification';
    case LoginVerification = 'login_verification';
}
