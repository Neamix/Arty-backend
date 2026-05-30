<?php

namespace Modules\UserManagement\Repositories;

use Illuminate\Support\Carbon;
use Modules\UserManagement\Enums\OtpUsage;
use Modules\UserManagement\Models\Otp;

class OtpRepository
{
    public function __construct(private Otp $otp) {}

    public function create(array $attributes): Otp
    {
        return $this->otp->newQuery()->create($attributes);
    }

    public function latestUnverifiedFor(string $email, OtpUsage $usage): ?Otp
    {
        return $this->otp->newQuery()
            ->where('email', $email)
            ->where('usage', $usage->value)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();
    }

    public function deleteUnverifiedFor(string $email, OtpUsage $usage): int
    {
        return $this->otp->newQuery()
            ->where('email', $email)
            ->where('usage', $usage->value)
            ->whereNull('verified_at')
            ->delete();
    }

    public function markVerified(Otp $otp): bool
    {
        return $otp->forceFill(['verified_at' => Carbon::now()])->save();
    }

    public function incrementAttempts(Otp $otp): bool
    {
        return $otp->forceFill(['attempts' => $otp->attempts + 1])->save();
    }

    public function findVerifiedFor(string $email, OtpUsage $usage): ?Otp
    {
        return $this->otp->newQuery()
            ->where('email', $email)
            ->where('usage', $usage->value)
            ->whereNotNull('verified_at')
            ->latest('id')
            ->first();
    }

    public function deleteFor(string $email, OtpUsage $usage): int
    {
        return $this->otp->newQuery()
            ->where('email', $email)
            ->where('usage', $usage->value)
            ->delete();
    }
}
