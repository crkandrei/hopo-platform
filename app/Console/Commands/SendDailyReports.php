<?php

namespace App\Console\Commands;

use App\Events\DailyReportGenerated;
use App\Models\Company;
use App\Services\Reports\DailyReportService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendDailyReports extends Command
{
    protected $signature = 'reports:send-daily {--date= : Data pentru care să se genereze raportul (default: ieri)}';

    protected $description = 'Trimite rapoarte zilnice pe email companiilor cu funcția activată';

    public function __construct(
        private DailyReportService $reportService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::yesterday();

        $this->info("Generez rapoarte pentru {$date->format('d.m.Y')}...");

        $companies = Company::where('daily_report_enabled', true)
            ->where('is_active', true)
            ->with('locations')
            ->get();

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($companies as $company) {
            try {
                $reportData = $this->reportService->generateForCompany($company, $date);

                if (!$reportData->hasActivity) {
                    $this->line("- {$company->name}: Fara activitate");
                    $skipped++;
                    continue;
                }

                event(new DailyReportGenerated($reportData));
                $company->markDailyReportSent();

                $this->line("+ {$company->name}: Raport trimis");
                $sent++;
            } catch (Exception $e) {
                $this->error("x {$company->name}: {$e->getMessage()}");
                Log::error('Daily report generation failed', [
                    'company_id' => $company->id,
                    'date' => $date->format('Y-m-d'),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $failed++;
            }
        }

        $this->info("Rezumat: {$sent} trimise, {$skipped} omise, {$failed} esuate");

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
