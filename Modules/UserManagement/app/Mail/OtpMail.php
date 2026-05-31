<?php

namespace Modules\UserManagement\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\UserManagement\Enums\OtpUsage;

class OtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public OtpUsage $usage,
        public int $expiresInMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: match ($this->usage) {
                OtpUsage::PasswordReset => 'Your password reset code',
                OtpUsage::EmailVerification => 'Verify your email',
                OtpUsage::LoginVerification => 'Your login verification code',
            },
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'usermanagement::emails.otp',
            with: [
                'otp' => $this->otp,
                'usage' => $this->usage,
                'expiresInMinutes' => $this->expiresInMinutes,
            ],
        );
    }
}
