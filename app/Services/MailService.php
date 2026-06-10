<?php

namespace App\Services;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class MailService
{
    public function send(string $to, Mailable $mailable): void
    {
        Mail::to($to)->queue($mailable);
    }
}
