<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** A plain-text email (tracking numbers, status updates, reminders). No HTML, nothing to hide behind. */
class PlainMessage extends Mailable
{
    public function __construct(public string $mailSubject, public string $body) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->mailSubject, replyTo: [site('contact.email')]);
    }

    public function content(): Content
    {
        return new Content(text: 'emails.plain');
    }
}
