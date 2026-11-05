<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

/**
 * A message typed into the contact form, on its way to the shop's support inbox. Replying to it
 * answers the customer, not our own sending address.
 */
class ContactMessageMail extends Mailable
{
    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public string $body,
    ) {}

    public function build(): static
    {
        return $this->subject('New message from ' . $this->senderName . ' — Ravellen contact form')
            ->replyTo($this->senderEmail, $this->senderName)
            ->view('emails.contact-message');
    }
}
