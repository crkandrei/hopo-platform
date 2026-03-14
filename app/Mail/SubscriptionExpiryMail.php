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
        return new Envelope(
            subject: "⚠ Abonament HOPO — expiră în {$this->daysRemaining} zile: {$this->subscription->location->name}",
            from: new Address(config('mail.from.address'), config('mail.from.name')),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-expiry',
            with: [
                'subscription'  => $this->subscription,
                'daysRemaining' => $this->daysRemaining,
                'recipientType' => $this->recipientType,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
