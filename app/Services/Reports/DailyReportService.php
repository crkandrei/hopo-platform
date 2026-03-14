<?php

namespace App\Services\Reports;

use App\Models\Company;
use App\Models\Location;
use App\Repositories\Contracts\DailyReportRepositoryInterface;
use App\Services\PricingService;
use App\Services\Reports\Data\DailyReportData;
use App\Services\Reports\Data\LocationReportData;
use App\Services\Reports\Data\SaleItemData;
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

            foreach ($company->locations()->where('is_active', true)->get() as $location) {
                $locationReport = $this->calculateLocationReport($location, $date);
                $locationReports->push($locationReport);
            }

            $hasActivity = $locationReports->contains(
                fn(LocationReportData $r) => $r->totalSessions > 0 || $r->productsTotal > 0 || $r->packagesTotal > 0
            );

            $today = Carbon::today();
            $todayReservations = $this->repository->getReservationsForCompany($company, $today);
            $hasReservations = $todayReservations->count() > 0;

            $grandCash = round($locationReports->sum('grandCash'), 2);
            $grandCard = round($locationReports->sum('grandCard'), 2);
            $grandVoucher = round($locationReports->sum('grandVoucher'), 2);
            $grandTotal = round($locationReports->sum('grandTotal'), 2);

            Log::info('Daily report generated', [
                'company_id' => $company->id,
                'date' => $date->format('Y-m-d'),
                'locations' => $locationReports->count(),
                'grand_total' => $grandTotal,
                'reservations_today' => $todayReservations->count(),
            ]);

            return new DailyReportData(
                company: $company,
                date: $date,
                locationReports: $locationReports,
                grandCash: $grandCash,
                grandCard: $grandCard,
                grandVoucher: $grandVoucher,
                grandTotal: $grandTotal,
                hasActivity: $hasActivity,
                todayReservations: $todayReservations,
                hasReservations: $hasReservations,
            );
        });
    }

    public function calculateLocationReport(Location $location, Carbon $date): LocationReportData
    {
        $sessions = $this->repository->getSessionsForLocationAndDate($location, $date);
        $receipts = $this->repository->getStandaloneReceiptsForLocationAndDate($location, $date);

        $sessionBreakdown = $this->calculateSessionBreakdown($sessions);
        $standaloneBreakdown = $this->calculateStandaloneBreakdown($receipts);

        $totalBilledHours = 0.0;
        foreach ($sessions as $session) {
            if ($session->ended_at) {
                $durationInHours = $this->pricingService->getDurationInHours($session);
                $roundedHours = $this->pricingService->roundToHalfHour($durationInHours);
                $totalBilledHours += $roundedHours;
            }
        }

        $sessionCash = round($sessionBreakdown['cashTotal'], 2);
        $sessionCard = round($sessionBreakdown['cardTotal'], 2);
        $sessionVoucher = round($sessionBreakdown['voucherTotal'], 2);
        $sessionTotal = round($sessionCash + $sessionCard + $sessionVoucher, 2);

        $grandCash = round($sessionCash + $standaloneBreakdown['productsCash'] + $standaloneBreakdown['packagesCash'], 2);
        $grandCard = round($sessionCard + $standaloneBreakdown['productsCard'] + $standaloneBreakdown['packagesCard'], 2);
        $grandVoucher = round($sessionVoucher + $standaloneBreakdown['productsVoucher'] + $standaloneBreakdown['packagesVoucher'], 2);
        $grandTotal = round($grandCash + $grandCard + $grandVoucher, 2);

        return new LocationReportData(
            location: $location,
            totalSessions: $sessions->count(),
            cashTotal: $sessionCash,
            cardTotal: $sessionCard,
            voucherTotal: $sessionVoucher,
            totalMoney: $sessionTotal,
            totalBilledHours: $totalBilledHours,
            productSales: $standaloneBreakdown['productSales'],
            productsCash: round($standaloneBreakdown['productsCash'], 2),
            productsCard: round($standaloneBreakdown['productsCard'], 2),
            productsVoucher: round($standaloneBreakdown['productsVoucher'], 2),
            productsTotal: round($standaloneBreakdown['productsTotal'], 2),
            packageSales: $standaloneBreakdown['packageSales'],
            packagesCash: round($standaloneBreakdown['packagesCash'], 2),
            packagesCard: round($standaloneBreakdown['packagesCard'], 2),
            packagesVoucher: round($standaloneBreakdown['packagesVoucher'], 2),
            packagesTotal: round($standaloneBreakdown['packagesTotal'], 2),
            grandCash: $grandCash,
            grandCard: $grandCard,
            grandVoucher: $grandVoucher,
            grandTotal: $grandTotal,
        );
    }

    public function hasActivityForLocation(Location $location, Carbon $date): bool
    {
        return $this->repository->getSessionsForLocationAndDate($location, $date)->count() > 0
            || $this->repository->getStandaloneReceiptsForLocationAndDate($location, $date)->count() > 0;
    }

    private function calculateSessionBreakdown(Collection $sessions): array
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

        return [
            'cashTotal' => $cashTotal,
            'cardTotal' => $cardTotal,
            'voucherTotal' => $voucherTotal,
        ];
    }

    private function calculateStandaloneBreakdown(Collection $receipts): array
    {
        $productGroups = [];
        $packageGroups = [];

        foreach ($receipts as $receipt) {
            $voucherDiscount = $receipt->getVoucherDiscount();
            $receiptItemsTotal = $receipt->items->sum(fn($item) => (float) $item->unit_price * $item->quantity);

            foreach ($receipt->items as $item) {
                $itemRawTotal = (float) $item->unit_price * $item->quantity;

                $itemVoucher = $receiptItemsTotal > 0
                    ? ($itemRawTotal / $receiptItemsTotal) * $voucherDiscount
                    : 0.0;

                $itemCollected = max(0.0, $itemRawTotal - $itemVoucher);

                $itemCash = $receipt->payment_method === 'CASH' ? $itemCollected : 0.0;
                $itemCard = $receipt->payment_method === 'CARD' ? $itemCollected : 0.0;

                $isPackage = str_contains(strtolower($item->source_type ?? ''), 'package');
                $groups = &($isPackage ? $packageGroups : $productGroups);

                $name = $item->name;
                if (!isset($groups[$name])) {
                    $groups[$name] = ['qty' => 0, 'cash' => 0.0, 'card' => 0.0, 'voucher' => 0.0];
                }
                $groups[$name]['qty'] += $item->quantity;
                $groups[$name]['cash'] += $itemCash;
                $groups[$name]['card'] += $itemCard;
                $groups[$name]['voucher'] += $itemVoucher;
            }
        }

        $productSales = collect();
        foreach ($productGroups as $name => $data) {
            $cash = round($data['cash'], 2);
            $card = round($data['card'], 2);
            $voucher = round($data['voucher'], 2);
            $productSales->push(new SaleItemData(
                name: $name,
                quantity: $data['qty'],
                cashTotal: $cash,
                cardTotal: $card,
                voucherTotal: $voucher,
                total: round($cash + $card + $voucher, 2),
            ));
        }

        $packageSales = collect();
        foreach ($packageGroups as $name => $data) {
            $cash = round($data['cash'], 2);
            $card = round($data['card'], 2);
            $voucher = round($data['voucher'], 2);
            $packageSales->push(new SaleItemData(
                name: $name,
                quantity: $data['qty'],
                cashTotal: $cash,
                cardTotal: $card,
                voucherTotal: $voucher,
                total: round($cash + $card + $voucher, 2),
            ));
        }

        return [
            'productSales' => $productSales,
            'productsCash' => $productSales->sum('cashTotal'),
            'productsCard' => $productSales->sum('cardTotal'),
            'productsVoucher' => $productSales->sum('voucherTotal'),
            'productsTotal' => $productSales->sum('total'),
            'packageSales' => $packageSales,
            'packagesCash' => $packageSales->sum('cashTotal'),
            'packagesCard' => $packageSales->sum('cardTotal'),
            'packagesVoucher' => $packageSales->sum('voucherTotal'),
            'packagesTotal' => $packageSales->sum('total'),
        ];
    }
}
