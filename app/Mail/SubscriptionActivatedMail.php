<?php

namespace App\Mail;

use App\Models\LocationSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionActivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly LocationSubscription $subscription,
        public readonly string $source,
    ) {}

    public function envelope(): Envelope
    {
        $location = $this->subscription->location->name;
        $sourceLabel = $this->source === 'stripe' ? 'Stripe' : 'manual';

        return new Envelope(
            subject: "[HOPO] Abonament activat — {$location} (via {$sourceLabel})",
            from: new Address(config('mail.from.address'), config('mail.from.name')),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-activated',
            with: [
                'subscription' => $this->subscription,
                'source'       => $this->source,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
