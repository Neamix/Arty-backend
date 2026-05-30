<?php

namespace Modules\UserManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\UserManagement\Enums\OtpUsage;

class Otp extends Model
{
    protected $fillable = ['email', 'otp', 'usage', 'attempts', 'expires_at', 'verified_at'];

    public static int $expireTime = 10;

    public static int $maxAttempts = 5;
    protected function casts(): array
    {
        return [
            'usage' => OtpUsage::class,
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}
