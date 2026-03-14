<?php

namespace App\Http\Controllers;

use App\Models\FiscalReceiptLog;
use App\Models\PlaySession;
use App\Models\PlaySessionProduct;
use App\Models\StandaloneReceipt;
use App\Models\User;
use App\Models\Location;
use App\Services\PricingService;
use App\Services\Reports\DailyReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class EndOfDayController extends Controller
{
    public function __construct(
        private PricingService $pricingService,
        private DailyReportService $reportService,
    ) {}

    /**
     * Show the end of day statistics page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Trebuie să fiți autentificat');
        }

        $location = $this->getLocation($user);

        // Get selected date or default to today
        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));
        try {
            $date = Carbon::parse($selectedDate);
        } catch (\Exception $e) {
            $date = Carbon::today();
        }

        $locationReport = $this->reportService->calculateLocationReport($location, $date);

        // Also fetch standalone receipts for the view (standalone total is displayed separately)
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        $standaloneReceipts = StandaloneReceipt::where('location_id', $location->id)
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$startOfDay, $endOfDay])
            ->with(['items', 'voucherUsages'])
            ->orderBy('paid_at')
            ->get();
        $standaloneTotal = round($standaloneReceipts->sum('total_amount'), 2);

        return view('end-of-day.index', [
            'totalSessions' => $locationReport->totalSessions,
            'totalMoney' => $locationReport->totalMoney,
            'totalBilledHours' => $locationReport->totalBilledHours,
            'cashTotal' => $locationReport->cashTotal,
            'cardTotal' => $locationReport->cardTotal,
            'voucherTotal' => $locationReport->voucherTotal,
            'standaloneReceipts' => $standaloneReceipts,
            'standaloneTotal' => $standaloneTotal,
            'locationId' => $location->id,
            'selectedDate' => $date->format('Y-m-d'),
            'selectedDateFormatted' => $date->format('d.m.Y'),
        ]);
    }

    private function getLocation(User $user): Location
    {
        if (!$user->isSuperAdmin() && $user->location) {
            return $user->location;
        }
        abort(403, 'Acces interzis');
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

        // Add standalone receipts (Bon Specific) paid on this date
        $standaloneQuery = StandaloneReceipt::whereNotNull('paid_at')
            ->whereBetween('paid_at', [$startOfDay, $endOfDay]);
        if ($locationId) {
            $standaloneQuery->where('location_id', $locationId);
        }
        $standaloneReceipts = $standaloneQuery->with(['items', 'voucherUsages'])->orderBy('paid_at')->get();
        $standaloneTotal = $standaloneReceipts->sum('total_amount');
        foreach ($standaloneReceipts as $receipt) {
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

        // Get all products sold today (only from paid sessions, exclude is_free)
        $paidSessionIds = $sessionsToday->filter(function($session) {
            return $session->ended_at && $session->isPaid() && !$session->is_free;
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
        // Add product-type items from standalone receipts to productsGrouped (by name for aggregation)
        foreach ($standaloneReceipts as $receipt) {
            foreach ($receipt->items as $item) {
                if ($item->source_type === 'product') {
                    $key = 'product_' . $item->source_id;
                    if (!isset($productsGrouped[$key])) {
                        $productsGrouped[$key] = ['name' => $item->name, 'total' => 0, 'quantity' => 0];
                    }
                    $productsGrouped[$key]['total'] += (float) $item->unit_price * $item->quantity;
                    $productsGrouped[$key]['quantity'] += $item->quantity;
                    $totalProductsValue += (float) $item->unit_price * $item->quantity;
                }
            }
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
            'standaloneReceipts' => $standaloneReceipts,
            'standaloneTotal' => round($standaloneTotal, 2),
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

