<?php

namespace App\Mail;

use App\Models\LocationSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly LocationSubscription $subscription,
        public readonly int $daysRemaining,
        public readonly string $recipientType,
    ) {}

    public function envelope(): Envelope
    {
        $days = $this->daysRemaining;
        $location = $this->subscription->location->name;

        return new Envelope(
            subject: "Abonamentul pentru {$location} expira in {$days} " . ($days === 1 ? 'zi' : 'zile'),
            from: new Address(config('mail.from.address'), config('mail.from.name')),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-expiry',
            text: 'emails.subscription-expiry-text',
            with: [
                'subscription'  => $this->subscription,
                'daysRemaining' => $this->daysRemaining,
                'recipientType' => $this->recipientType,
            ],
        );
    }

    public function headers(): \Illuminate\Mail\Mailables\Headers
    {
        return new \Illuminate\Mail\Mailables\Headers(
            text: [
                'X-Priority' => '1',
                'X-Mailer' => 'Hopo Platform',
                'Precedence' => 'bulk',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
