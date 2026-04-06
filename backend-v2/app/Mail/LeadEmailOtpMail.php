<?php

namespace App\Mail;

use App\Models\RegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeadEmailOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly RegistrationRequest $lead,
        public readonly string $code,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify your Mobius inquiry',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.lead-email-otp',
        );
    }
}
