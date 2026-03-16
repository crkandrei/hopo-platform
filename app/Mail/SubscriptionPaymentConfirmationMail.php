<?php

namespace App\Mail;

use App\Models\LocationSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionPaymentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly LocationSubscription $subscription,
    ) {}

    public function envelope(): Envelope
    {
        $location = $this->subscription->location->name;

        return new Envelope(
            subject: "Abonament activat — {$location}",
            from: new Address(config('mail.from.address'), config('mail.from.name')),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-payment-confirmation',
            with: [
                'subscription' => $this->subscription,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
