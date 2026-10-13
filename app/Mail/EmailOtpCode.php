<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailOtpCode extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public $ttlMinutes;

    public function __construct(string $code, int $ttlMinutes)
    {
        $this->code = $code;
        $this->ttlMinutes = $ttlMinutes;
    }

    public function build()
    {
        $fromAddress = config('mail.from.address') ?: config('app.url') . '@noreply';
        $fromName = config('mail.from.name') ?: config('app.name');

        return $this
            ->from($fromAddress, $fromName)
            ->subject('[Raksantara] Kode verifikasi masuk')
            ->view('emails.mfa-email-otp');
    }
}
