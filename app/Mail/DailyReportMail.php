<?php

namespace App\Mail;

use App\Models\Company;
use App\Services\Reports\DailyReportService;
use Carbon\Carbon;
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
        public int $companyId,
        public string $date,
    ) {}

    public function envelope(): Envelope
    {
        $company = Company::find($this->companyId);
        $dateFormatted = Carbon::parse($this->date)->format('d.m.Y');

        return new Envelope(
            subject: "Raport zilnic {$company->name} — {$dateFormatted}",
            from: new Address(config('mail.from.address'), config('mail.from.name')),
        );
    }

    public function content(): Content
    {
        $company = Company::with('locations')->find($this->companyId);
        $date = Carbon::parse($this->date);

        $reportData = app(DailyReportService::class)->generateForCompany($company, $date);

        return new Content(
            view: 'emails.daily-report',
            with: ['reportData' => $reportData],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
