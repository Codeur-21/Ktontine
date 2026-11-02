<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantCommunication extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $businessName,
        public string $mailSubject,
        public string $messageBody,
        public string $platformName = 'Ktontine',
        public string $buttonLabel = '',
        public string $buttonUrl = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: strtr($this->mailSubject, ['{nom}' => $this->name, '{espace}' => $this->businessName]));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.tenant-communication', text: 'emails.tenant-communication-text', with: [
            'personalMessage' => strtr($this->messageBody, ['{nom}' => $this->name, '{espace}' => $this->businessName]),
        ]);
    }
}
