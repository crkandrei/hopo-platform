<?php

namespace App\Mail;

use App\Models\BirthdayReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewBirthdayReservationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly BirthdayReservation $reservation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎂 Rezervare nouă – ' . $this->reservation->child_name . ' · ' .
                     $this->reservation->reservation_date->format('d.m.Y'),
            from: env('MAIL_FROM_ADDRESS', 'noreply@hopo.ro'),
        );
    }

    public function content(): Content
    {
        $reservation = $this->reservation;
        $reservation->loadMissing(['location', 'birthdayHall', 'birthdayPackage']);

        return new Content(
            view: 'emails.birthday-reservation',
            with: [
                'reservation'  => $reservation,
                'confirmUrl'   => route('birthday-reservations.action', [
                    'token'  => $reservation->token,
                    'action' => 'confirm',
                ]),
                'rejectUrl'    => route('birthday-reservations.action', [
                    'token'  => $reservation->token,
                    'action' => 'reject',
                ]),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
