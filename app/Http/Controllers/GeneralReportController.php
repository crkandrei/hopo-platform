<?php

namespace App\Http\Controllers;

use App\Models\PlaySession;
use App\Models\PlaySessionProduct;
use App\Support\ApiResponder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GeneralReportController extends Controller
{
    /**
     * Display the general report page
     * Access: SUPER_ADMIN, COMPANY_ADMIN
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        
        // STAFF nu are acces la rapoarte
        if ($user->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        return view('reports.general');
    }

    /**
     * Get aggregated report data for a date range
     * Access: SUPER_ADMIN, COMPANY_ADMIN
     */
    public function data(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->location) {
            return ApiResponder::error('Neautentificat', 401);
        }
        
        if ($user->isStaff()) {
            return ApiResponder::error('Acces interzis', 403);
        }
        
        $locationId = $user->location->id;
        
        // Parse date range
        $startDate = $request->input('start');
        $endDate = $request->input('end');
        
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();
        
        if ($end->lessThan($start)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }
        
        // Get all completed sessions in range
        $sessions = PlaySession::where('location_id', $locationId)
            ->whereNotNull('ended_at')
            ->where('started_at', '>=', $start)
            ->where('started_at', '<=', $end)
            ->with('products.product')
            ->get();
        
        // Calculate total hours played
        $totalMinutes = 0;
        foreach ($sessions as $session) {
            $totalMinutes += $session->getCurrentDurationMinutes();
        }
        $totalHours = round($totalMinutes / 60, 1);
        
        // Session breakdown
        $totalSessions = $sessions->count();
        $normalSessions = $totalSessions;
        
        // Sales breakdown - only paid sessions
        $paidSessions = $sessions->filter(fn($s) => $s->isPaid());
        
        $cashTotal = 0;
        $cardTotal = 0;
        $voucherTotal = 0;
        $totalProductsSold = 0;
        $productSales = []; // Track product sales for top products
        
        foreach ($paidSessions as $session) {
            // Get total price (time + products)
            $timePrice = $session->calculated_price ?? 0;
            $productsPrice = $session->getProductsTotalPrice();
            $voucherPrice = $session->getVoucherPrice();
            
            // Add voucher value
            if ($voucherPrice > 0) {
                $voucherTotal += $voucherPrice;
            }
            
            // Amount collected = total price - voucher
            $amountCollected = ($timePrice + $productsPrice) - $voucherPrice;
            
            // Add cash/card amount based on payment method
            if ($session->payment_method === 'CASH') {
                $cashTotal += $amountCollected;
            } elseif ($session->payment_method === 'CARD') {
                $cardTotal += $amountCollected;
            } else {
                if ($amountCollected > 0) {
                    $cashTotal += $amountCollected;
                }
            }
            
            // Count products
            foreach ($session->products as $sessionProduct) {
                $totalProductsSold += $sessionProduct->quantity;
                
                $productName = $sessionProduct->product->name ?? 'Produs necunoscut';
                if (!isset($productSales[$productName])) {
                    $productSales[$productName] = [
                        'name' => $productName,
                        'quantity' => 0,
                        'total' => 0,
                    ];
                }
                $productSales[$productName]['quantity'] += $sessionProduct->quantity;
                $productSales[$productName]['total'] += $sessionProduct->total_price;
            }
        }
        
        // Sort products by quantity and get top 10
        usort($productSales, fn($a, $b) => $b['quantity'] - $a['quantity']);
        $topProducts = array_slice($productSales, 0, 10);
        
        // Total sales (including voucher value)
        $totalSales = $cashTotal + $cardTotal + $voucherTotal;
        
        // Calculate average session duration
        $avgMinutes = $totalSessions > 0 ? round($totalMinutes / $totalSessions) : 0;
        $avgHours = floor($avgMinutes / 60);
        $avgMins = $avgMinutes % 60;
        $avgDuration = $avgHours > 0 ? "{$avgHours}h {$avgMins}m" : "{$avgMins}m";
        
        return ApiResponder::success([
            'period' => [
                'start' => $start->format('d.m.Y'),
                'end' => $end->format('d.m.Y'),
            ],
            'hours' => [
                'total' => $totalHours,
                'formatted' => $this->formatHours($totalHours),
                'avg_per_session' => $avgDuration,
            ],
            'sessions' => [
                'total' => $totalSessions,
                'normal' => $normalSessions,
            ],
            'products' => [
                'total_sold' => $totalProductsSold,
                'total_revenue' => round(array_sum(array_column($productSales, 'total')), 2),
                'top_products' => $topProducts,
            ],
            'sales' => [
                'total' => round($totalSales, 2),
                'cash' => round($cashTotal, 2),
                'card' => round($cardTotal, 2),
                'voucher' => round($voucherTotal, 2),
            ],
        ]);
    }
    
    /**
     * Format hours into human-readable string
     */
    private function formatHours(float $hours): string
    {
        $wholeHours = floor($hours);
        $minutes = round(($hours - $wholeHours) * 60);
        
        if ($wholeHours === 0 && $minutes === 0) {
            return '0 ore';
        }
        
        if ($wholeHours === 0) {
            return "{$minutes} minute";
        }
        
        if ($minutes === 0) {
            return "{$wholeHours} ore";
        }
        
        return "{$wholeHours} ore {$minutes} min";
    }
}

