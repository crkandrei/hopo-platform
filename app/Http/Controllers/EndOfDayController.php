<?php

namespace App\Http\Controllers;

use App\Models\PlaySession;
use App\Models\FiscalReceiptLog;
use App\Models\PlaySessionProduct;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class EndOfDayController extends Controller
{
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Show the end of day statistics page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Trebuie să fiți autentificat');
        }

        // Get location - super admin can see all, others see their location
        $locationId = null;
        if (!$user->isSuperAdmin() && $user->location) {
            $locationId = $user->location->id;
        }

        // Get selected date or default to today
        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));
        try {
            $date = Carbon::parse($selectedDate);
        } catch (\Exception $e) {
            $date = Carbon::today();
        }

        // Get date range for selected date
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        // Get all sessions started today
        $sessionsQuery = PlaySession::where('started_at', '>=', $startOfDay)
            ->where('started_at', '<=', $endOfDay);

        if ($locationId) {
            $sessionsQuery->where('location_id', $locationId);
        }

        $sessionsToday = $sessionsQuery->get();

        // Calculate statistics
        $totalSessions = $sessionsToday->count();
        
        // Calculate payment breakdown: cash, card, voucher
        $cashTotal = 0;
        $cardTotal = 0;
        $voucherTotal = 0;
        
        $endedSessions = $sessionsToday->whereNotNull('ended_at')->whereNotNull('calculated_price');
        
        foreach ($endedSessions as $session) {
            if ($session->isPaid()) {
                // Get total price (time + products)
                $timePrice = $session->calculated_price ?? $session->calculatePrice();
                $productsPrice = $session->getProductsTotalPrice();
                $totalPrice = $timePrice + $productsPrice;
                
                $voucherPrice = $session->getVoucherPrice();
                
                // Add voucher value
                if ($voucherPrice > 0) {
                    $voucherTotal += $voucherPrice;
                }
                
                // Amount collected = total price - voucher (voucher applies only to time)
                $amountCollected = $totalPrice - $voucherPrice;
                
                // Add cash/card amount based on payment method
                if ($session->payment_method === 'CASH') {
                    $cashTotal += $amountCollected;
                } elseif ($session->payment_method === 'CARD') {
                    $cardTotal += $amountCollected;
                } else {
                    // If no payment method specified but session is paid, assume it's cash
                    // This handles legacy data or sessions paid without fiscal receipt
                    if ($amountCollected > 0) {
                        $cashTotal += $amountCollected;
                    }
                }
            }
        }
        
        // Total money = cash + card + voucher (total value, not just collected)
        $totalMoney = $cashTotal + $cardTotal + $voucherTotal;

        // Calculate total billed hours
        $totalBilledHours = 0;
        foreach ($sessionsToday as $session) {
            if ($session->ended_at) {
                $durationInHours = $this->pricingService->getDurationInHours($session);
                $roundedHours = $this->pricingService->roundToHalfHour($durationInHours);
                $totalBilledHours += $roundedHours;
            }
        }

        return view('end-of-day.index', [
            'totalSessions' => $totalSessions,
            'totalMoney' => $totalMoney,
            'totalBilledHours' => $totalBilledHours,
            'cashTotal' => round($cashTotal, 2),
            'cardTotal' => round($cardTotal, 2),
            'voucherTotal' => round($voucherTotal, 2),
            'locationId' => $locationId,
            'selectedDate' => $date->format('Y-m-d'),
            'selectedDateFormatted' => $date->format('d.m.Y'),
        ]);
    }

    /**
     * Show non-fiscal report print page
     */
    public function printNonFiscalReport(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Trebuie să fiți autentificat');
        }

        // Get location - super admin can see all, others see their location
        $locationId = null;
        if (!$user->isSuperAdmin() && $user->location) {
            $locationId = $user->location->id;
        }

        // Get selected date or default to today
        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));
        try {
            $date = Carbon::parse($selectedDate);
        } catch (\Exception $e) {
            $date = Carbon::today();
        }

        // Get date range for selected date
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        // Get all sessions started today
        $sessionsQuery = PlaySession::where('started_at', '>=', $startOfDay)
            ->where('started_at', '<=', $endOfDay);

        if ($locationId) {
            $sessionsQuery->where('location_id', $locationId);
        }

        $sessionsToday = $sessionsQuery->with('products.product')->get();

        // Calculate statistics
        $totalSessions = $sessionsToday->count();
        
        // Calculate total billed hours
        $totalBilledHours = 0;
        $regularBilledHours = 0;
        $regularSessionsTotal = 0;
        $totalVoucherHours = 0;
        
        foreach ($sessionsToday as $session) {
            if ($session->ended_at) {
                $durationInHours = $this->pricingService->getDurationInHours($session);
                $roundedHours = $this->pricingService->roundToHalfHour($durationInHours);
                
                // Add voucher hours if session was paid with voucher
                if ($session->voucher_hours && $session->voucher_hours > 0) {
                    $totalVoucherHours += $session->voucher_hours;
                }
                
                $regularBilledHours += $roundedHours;
                if ($session->calculated_price) {
                    $regularSessionsTotal += $session->calculated_price;
                }
                $totalBilledHours += $roundedHours;
            }
        }
        
        // Calculate payment breakdown: cash, card, voucher
        // Also calculate totalSessionsValue (time only) for paid sessions
        $cashTotal = 0;
        $cardTotal = 0;
        $voucherTotal = 0;
        $totalSessionsValue = 0; // Only time price for paid sessions (without products)
        
        foreach ($sessionsToday as $session) {
            if ($session->ended_at && $session->isPaid()) {
                $amountCollected = $session->getAmountCollected(); // This includes time + products
                $voucherPrice = $session->getVoucherPrice();
                
                // Calculate time price only (without products) for paid sessions
                $timePrice = $session->calculated_price ?? $session->calculatePrice();
                $finalTimePrice = max(0, $timePrice - $voucherPrice);
                // Total sessions value = time paid + voucher value (time only, no products)
                $totalSessionsValue += $finalTimePrice + $voucherPrice;
                
                // Add voucher value
                if ($voucherPrice > 0) {
                    $voucherTotal += $voucherPrice;
                }
                
                // Add cash/card amount based on payment method
                if ($session->payment_method === 'CASH') {
                    $cashTotal += $amountCollected;
                } elseif ($session->payment_method === 'CARD') {
                    $cardTotal += $amountCollected;
                } else {
                    // If no payment method specified but session is paid, assume it's cash/card
                    // This handles legacy data or sessions paid without fiscal receipt
                    if ($amountCollected > 0) {
                        $cashTotal += $amountCollected;
                    }
                }
            }
        }

        // Get all products sold today (only from paid sessions)
        $paidSessionIds = $sessionsToday->filter(function($session) {
            return $session->ended_at && $session->isPaid();
        })->pluck('id');
        
        $productsSold = PlaySessionProduct::whereIn('play_session_id', $paidSessionIds)
            ->with('product')
            ->get();

        // Group products by product_id and calculate totals
        $productsGrouped = [];
        $totalProductsValue = 0;
        foreach ($productsSold as $psp) {
            $productId = $psp->product_id;
            $productName = $psp->product ? $psp->product->name : 'Produs #' . $productId;
            $totalPrice = $psp->total_price;

            if (!isset($productsGrouped[$productId])) {
                $productsGrouped[$productId] = [
                    'name' => $productName,
                    'total' => 0,
                    'quantity' => 0,
                ];
            }
            $productsGrouped[$productId]['total'] += $totalPrice;
            $productsGrouped[$productId]['quantity'] += $psp->quantity;
            $totalProductsValue += $totalPrice;
        }

        // Format hours for display
        $formatHours = function($hours) {
            $hoursInt = floor($hours);
            $minutesInt = round(($hours - $hoursInt) * 60);
            if ($minutesInt >= 60) {
                $hoursInt += 1;
                $minutesInt = 0;
            }
            $formatted = $hoursInt . 'h';
            if ($minutesInt > 0) {
                $formatted .= ' ' . $minutesInt . 'm';
            }
            return $formatted;
        };

        return view('end-of-day.print-non-fiscal', [
            'totalSessions' => $totalSessions,
            'regularSessions' => $totalSessions,
            'regularSessionsTotal' => $regularSessionsTotal,
            'totalSessionsValue' => $totalSessionsValue,
            'totalBilledHours' => $formatHours($totalBilledHours),
            'totalVoucherHours' => $formatHours($totalVoucherHours),
            'productsGrouped' => $productsGrouped,
            'totalProductsValue' => $totalProductsValue,
            'cashTotal' => round($cashTotal, 2),
            'cardTotal' => round($cardTotal, 2),
            'voucherTotal' => round($voucherTotal, 2),
            'date' => $date->format('d.m.Y'),
        ]);
    }

    /**
     * Save Z Report log (called from frontend after bridge response)
     */
    public function saveZReportLog(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Neautentificat'
            ], 401);
        }
        
        $request->validate([
            'filename' => 'nullable|string|max:255',
            'status' => 'required|in:success,error',
            'error_message' => 'nullable|string',
        ]);
        
        try {
            // Get location_id from user if available (works for both super admin and regular users)
            $locationId = $user->location_id ?? null;
            
            $log = FiscalReceiptLog::create([
                'type' => 'z_report',
                'play_session_id' => null,
                'location_id' => $locationId,
                'filename' => $request->filename,
                'status' => $request->status,
                'error_message' => $request->error_message,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Log salvat cu succes',
                'log_id' => $log->id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to save Z report log', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Eroare la salvarea logului: ' . $e->getMessage(),
            ], 500);
        }
    }
}

