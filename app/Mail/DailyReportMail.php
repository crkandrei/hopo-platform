<?php

namespace App\Mail;

use App\Services\Reports\Data\DailyReportData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly DailyReportData $reportData,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Raport zilnic {$this->reportData->company->name} — {$this->reportData->date->format('d.m.Y')}",
            from: new Address(config('mail.from.address'), config('mail.from.name')),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-report',
            with: ['reportData' => $this->reportData],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
