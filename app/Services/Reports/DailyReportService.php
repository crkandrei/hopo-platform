<?php

namespace App\Services\Reports;

use App\Models\Company;
use App\Models\Location;
use App\Repositories\Contracts\DailyReportRepositoryInterface;
use App\Services\PricingService;
use App\Services\Reports\Data\DailyReportData;
use App\Services\Reports\Data\LocationReportData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DailyReportService
{
    public function __construct(
        private DailyReportRepositoryInterface $repository,
        private PricingService $pricingService,
    ) {}

    public function generateForCompany(Company $company, Carbon $date): DailyReportData
    {
        $cacheKey = "daily_report_{$company->id}_{$date->format('Y-m-d')}";

        return Cache::remember($cacheKey, 3600, function () use ($company, $date) {
            $locationReports = collect();
            $grandTotalMoney = 0.0;

            foreach ($company->locations()->where('is_active', true)->get() as $location) {
                $locationReport = $this->calculateLocationReport($location, $date);
                $locationReports->push($locationReport);
                $grandTotalMoney += $locationReport->totalMoney;
            }

            $hasActivity = $locationReports->contains(fn(LocationReportData $r) => $r->totalSessions > 0);

            Log::info('Daily report generated', [
                'company_id' => $company->id,
                'date' => $date->format('Y-m-d'),
                'locations' => $locationReports->count(),
                'grand_total' => $grandTotalMoney,
            ]);

            return new DailyReportData(
                company: $company,
                date: $date,
                locationReports: $locationReports,
                grandTotalMoney: round($grandTotalMoney, 2),
                hasActivity: $hasActivity,
            );
        });
    }

    public function calculateLocationReport(Location $location, Carbon $date): LocationReportData
    {
        $sessions = $this->repository->getSessionsForLocationAndDate($location, $date);
        $receipts = $this->repository->getStandaloneReceiptsForLocationAndDate($location, $date);

        $breakdown = $this->calculatePaymentBreakdown($sessions, $receipts);

        $totalBilledHours = 0.0;
        foreach ($sessions as $session) {
            if ($session->ended_at) {
                $durationInHours = $this->pricingService->getDurationInHours($session);
                $roundedHours = $this->pricingService->roundToHalfHour($durationInHours);
                $totalBilledHours += $roundedHours;
            }
        }

        $totalMoney = $breakdown['cashTotal'] + $breakdown['cardTotal'] + $breakdown['voucherTotal'];

        return new LocationReportData(
            location: $location,
            totalSessions: $sessions->count(),
            cashTotal: round($breakdown['cashTotal'], 2),
            cardTotal: round($breakdown['cardTotal'], 2),
            voucherTotal: round($breakdown['voucherTotal'], 2),
            totalMoney: round($totalMoney, 2),
            totalBilledHours: $totalBilledHours,
        );
    }

    public function hasActivityForLocation(Location $location, Carbon $date): bool
    {
        return $this->repository->getSessionsForLocationAndDate($location, $date)->count() > 0
            || $this->repository->getStandaloneReceiptsForLocationAndDate($location, $date)->count() > 0;
    }

    private function calculatePaymentBreakdown(Collection $sessions, Collection $receipts): array
    {
        $cashTotal = 0.0;
        $cardTotal = 0.0;
        $voucherTotal = 0.0;

        $endedSessions = $sessions->whereNotNull('ended_at')->whereNotNull('calculated_price');

        foreach ($endedSessions as $session) {
            if ($session->isPaid() && !$session->is_free) {
                $timePrice = $session->calculated_price ?? $session->calculatePrice();
                $productsPrice = $session->getProductsTotalPrice();
                $totalPrice = $timePrice + $productsPrice;

                $voucherPrice = $session->getVoucherPrice();

                if ($voucherPrice > 0) {
                    $voucherTotal += $voucherPrice;
                }

                $amountCollected = $totalPrice - $voucherPrice;

                if ($session->payment_method === 'CASH') {
                    $cashTotal += $amountCollected;
                } elseif ($session->payment_method === 'CARD') {
                    $cardTotal += $amountCollected;
                } else {
                    if ($amountCollected > 0) {
                        $cashTotal += $amountCollected;
                    }
                }
            }
        }

        foreach ($receipts as $receipt) {
            $voucherDiscount = $receipt->getVoucherDiscount();
            $amountCollected = max(0, (float) $receipt->total_amount - $voucherDiscount);

            if ($voucherDiscount > 0) {
                $voucherTotal += $voucherDiscount;
            }

            if ($receipt->payment_method === 'CASH') {
                $cashTotal += $amountCollected;
            } elseif ($receipt->payment_method === 'CARD') {
                $cardTotal += $amountCollected;
            } else {
                if ($amountCollected > 0) {
                    $cashTotal += $amountCollected;
                }
            }
        }

        return [
            'cashTotal' => $cashTotal,
            'cardTotal' => $cardTotal,
            'voucherTotal' => $voucherTotal,
        ];
    }
}
