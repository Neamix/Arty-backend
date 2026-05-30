<?php

namespace Modules\UserManagement\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Modules\UserManagement\Enums\OtpUsage;
use Modules\UserManagement\Exceptions\OtpException;
use Modules\UserManagement\Mail\OtpMail;
use Modules\UserManagement\Models\Otp;
use Modules\UserManagement\Repositories\OtpRepository;

class OtpService
{
    public function __construct(private OtpRepository $otpRepository) {}

    /**
     * Generate, persist, and email an OTP for the given email + usage.
     */
    public function sendOtp(string $email, OtpUsage $usage): Otp
    {
        $this->ensureNotRateLimited($email, $usage);

        $this->otpRepository->deleteUnverifiedFor($email, $usage);

        $code = $this->generateCode();
        $expiresInMinutes = Otp::$expireTime;

        $otp = $this->otpRepository->create([
            'email' => $email,
            'otp' => $code,
            'usage' => $usage->value,
            'expires_at' => Carbon::now()->addMinutes($expiresInMinutes),
        ]);

        Mail::to($email)->send(new OtpMail($code, $usage, $expiresInMinutes));

        RateLimiter::hit($this->rateLimiterKey($email, $usage), (int) config('usermanagement.otp.rate_limit.decay_minutes', 15) * 60);

        return $otp;
    }

    /**
     * Verify an OTP code. Marks it verified on success.
     *
     * @throws OtpException
     */
    public function verify(string $email, string $code, OtpUsage $usage): Otp
    {
        $otp = $this->otpRepository->latestUnverifiedFor($email, $usage);

        if (! $otp) {
            throw new OtpException('No active verification code for this email.');
        }

        if ($otp->isExpired()) {
            throw new OtpException('Verification code has expired.');
        }

        $maxAttempts = Otp::$maxAttempts;
        if ($otp->attempts >= $maxAttempts) {
            throw new OtpException('Too many attempts. Request a new code.');
        }

        if (! hash_equals($otp->otp, $code)) {
            $this->otpRepository->incrementAttempts($otp);
            throw new OtpException('Invalid verification code.');
        }

        $this->otpRepository->markVerified($otp);

        return $otp->refresh();
    }

    /**
     * Confirm a previously-verified OTP exists within the reset window.
     *
     * @throws OtpException
     */
    public function ensureVerifiedWithinWindow(string $email, OtpUsage $usage): Otp
    {
        $otp = $this->otpRepository->findVerifiedFor($email, $usage);

        if (! $otp) {
            throw new OtpException('Verification required before this action.');
        }

        $windowMinutes = (int) config('usermanagement.otp.reset_window_minutes', 15);
        if ($otp->verified_at->lt(Carbon::now()->subMinutes($windowMinutes))) {
            throw new OtpException('Verification expired. Start the flow again.');
        }

        return $otp;
    }

    public function consume(string $email, OtpUsage $usage): void
    {
        $this->otpRepository->deleteFor($email, $usage);
    }

    private function generateCode(): string
    {
        $length = (int) config('usermanagement.otp.length', 6);
        $max = (int) str_repeat('9', $length);
        $min = (int) ('1'.str_repeat('0', $length - 1));

        return (string) random_int($min, $max);
    }

    private function ensureNotRateLimited(string $email, OtpUsage $usage): void
    {
        $key = $this->rateLimiterKey($email, $usage);
        $max = (int) config('usermanagement.otp.rate_limit.max_requests', 5);

        if (RateLimiter::tooManyAttempts($key, $max)) {
            $seconds = RateLimiter::availableIn($key);
            throw new OtpException("Too many requests. Try again in {$seconds} seconds.", 429);
        }
    }

    private function rateLimiterKey(string $email, OtpUsage $usage): string
    {
        return 'otp:'.$usage->value.':'.sha1(strtolower($email));
    }
}
