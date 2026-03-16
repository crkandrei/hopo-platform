<?php

namespace App\Mail;

use App\Models\Location;
use App\Models\SubscriptionPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Location $location,
        public readonly ?SubscriptionPlan $plan,
        public readonly string $stripeEventId,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[HOPO] Plată eșuată — {$this->location->name}",
            from: new Address(config('mail.from.address'), config('mail.from.name')),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-failed',
            with: [
                'location'       => $this->location,
                'plan'           => $this->plan,
                'stripeEventId'  => $this->stripeEventId,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
