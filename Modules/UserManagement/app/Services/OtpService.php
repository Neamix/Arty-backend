<?php

namespace Modules\UserManagement\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
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
        $this->otpRepository->deleteUnverifiedFor($email, $usage);

        $code = rand(111111, 999999);
        $expiresInMinutes = Otp::$expireTime;

        $otp = $this->otpRepository->create([
            'email' => $email,
            'otp' => $code,
            'usage' => $usage->value,
            'expires_at' => Carbon::now()->addMinutes($expiresInMinutes),
        ]);

        Mail::to($email)->send(new OtpMail($code, $usage, $expiresInMinutes));

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

        $windowMinutes = Otp::$expireTime;
        if ($otp->verified_at->lt(Carbon::now()->subMinutes($windowMinutes))) {
            throw new OtpException('Verification expired. Start the flow again.');
        }

        return $otp;
    }

    public function consume(string $email, OtpUsage $usage): void
    {
        $this->otpRepository->deleteFor($email, $usage);
    }
}
