<?php

namespace App\Mail;

use App\Support\EmailOtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $purpose,
        public int $ttlMinutes,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->purpose === EmailOtp::PURPOSE_RESET
                ? 'Password reset code - Nepal Notary Public Council'
                : 'Verify your email - Nepal Notary Public Council',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.otp', text: 'emails.otp-text');
    }
}
