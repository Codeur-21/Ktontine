<?php

namespace App\Mail;

use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpired extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $businessName,
        public CarbonImmutable $expiredAt,
        public string $paymentUrl,
        public bool $isTrial = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->isTrial
            ? 'Votre essai gratuit de 30 jours est terminé — Ktontine'
            : 'Votre abonnement a expiré — Ktontine');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.subscription-expired', text: 'emails.subscription-expired-text');
    }
}
