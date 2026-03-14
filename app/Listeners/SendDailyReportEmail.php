<?php

namespace App\Listeners;

use App\Events\DailyReportGenerated;
use App\Mail\DailyReportMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDailyReportEmail
{
    public function handle(DailyReportGenerated $event): void
    {
        $reportData = $event->reportData;
        $company = $reportData->company;

        $email = $company->getDailyReportEmail();

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Invalid or missing email for daily report', ['company_id' => $company->id]);
            return;
        }

        Mail::to($email)->queue(new DailyReportMail(
            companyId: $company->id,
            date: $reportData->date->toDateString(),
        ));

        Log::info('Daily report email queued', [
            'company_id' => $company->id,
            'email' => $email,
        ]);
    }
}
