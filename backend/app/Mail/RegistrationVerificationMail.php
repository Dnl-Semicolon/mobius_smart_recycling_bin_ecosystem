<?php

namespace App\Mail;

use App\Models\RegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public RegistrationRequest $request) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Verify your email — Mobius Registration');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.registration-verification');
    }
}
