<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Product;
use App\Models\StandaloneReceipt;
use App\Models\StandaloneReceiptItem;
use App\Models\FiscalReceiptLog;
use App\Models\Location;
use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StandaloneReceiptController extends Controller
{
    /**
     * Show form to create a new standalone receipt (select packages + products).
     */
    public function create()
    {
        $user = Auth::user();
        if (!$user || !$user->location) {
            return redirect()->route('dashboard')->with('error', 'Utilizatorul nu este asociat cu nicio locație');
        }
        $location = $user->location;
        $packages = $location->packages()->where('is_active', true)->orderBy('name')->get();
        $products = $location->products()->where('is_active', true)->orderBy('name')->get();
        return view('standalone-receipts.create', compact('location', 'packages', 'products'));
    }

    /**
     * Store a new standalone receipt with items, then redirect to payment step.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->location) {
            return response()->json(['success' => false, 'message' => 'Utilizatorul nu este asociat cu nicio locație'], 400);
        }
        $location = $user->location;

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.source_type' => 'required|in:package,product',
            'items.*.source_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:65535',
        ]);

        $totalAmount = 0;
        $receiptItems = [];

        foreach ($validated['items'] as $item) {
            if ($item['source_type'] === 'package') {
                $source = Package::where('location_id', $location->id)->where('id', $item['source_id'])->where('is_active', true)->first();
            } else {
                $source = Product::where('location_id', $location->id)->where('id', $item['source_id'])->where('is_active', true)->first();
            }
            if (!$source) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item invalid: ' . $item['source_type'] . ' #' . $item['source_id'],
                ], 422);
            }
            $qty = (int) $item['quantity'];
            $unitPrice = (float) $source->price;
            $totalAmount += $unitPrice * $qty;
            $receiptItems[] = [
                'source_type' => $item['source_type'],
                'source_id' => $source->id,
                'name' => $source->name,
                'unit_price' => $unitPrice,
                'quantity' => $qty,
            ];
        }

        if ($totalAmount <= 0) {
            return response()->json(['success' => false, 'message' => 'Totalul trebuie să fie mai mare decât 0.'], 422);
        }

        $receipt = StandaloneReceipt::create([
            'location_id' => $location->id,
            'created_by' => $user->id,
            'total_amount' => round($totalAmount, 2),
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($receiptItems as $row) {
            $receipt->items()->create($row);
        }

        return response()->json([
            'success' => true,
            'receipt_id' => $receipt->id,
            'total_amount' => (float) $receipt->total_amount,
            'payment_url' => route('standalone-receipts.pay', $receipt),
        ]);
    }

    /**
     * Show payment page for a standalone receipt (Cash/Card, then print fiscal or mark paid).
     */
    public function pay(StandaloneReceipt $standaloneReceipt)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        if (!$user->isSuperAdmin() && $user->location && $standaloneReceipt->location_id !== $user->location->id) {
            abort(403, 'Nu aveți acces la acest bon.');
        }
        if ($standaloneReceipt->isPaid()) {
            return redirect()->route('sessions.index')->with('info', 'Bonul a fost deja plătit.');
        }
        $standaloneReceipt->load('items');
        return view('standalone-receipts.pay', compact('standaloneReceipt'));
    }

    /**
     * Prepare fiscal receipt data for a standalone receipt (items for bridge).
     */
    public function prepareFiscalPrint(StandaloneReceipt $standaloneReceipt, Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Neautentificat'], 401);
        }
        if (!$user->isSuperAdmin() && $user->location && $standaloneReceipt->location_id !== $user->location->id) {
            return response()->json(['success' => false, 'message' => 'Acces interzis'], 403);
        }
        if ($standaloneReceipt->isPaid()) {
            return response()->json(['success' => false, 'message' => 'Bonul a fost deja plătit.'], 400);
        }

        $request->validate([
            'paymentType' => 'nullable|in:CASH,CARD',
            'voucher_code' => 'nullable|string|max:32',
        ]);

        $totalPrice = (float) $standaloneReceipt->total_amount;
        $voucherCode = null;
        $voucherType = null;
        $voucherId = null;
        $discountAmount = 0.0;
        $finalPrice = $totalPrice;

        if ($request->filled('voucher_code')) {
            $voucherService = app(VoucherService::class);
            $result = $voucherService->validateVoucher($request->voucher_code, $standaloneReceipt->location, 'amount');
            if (!$result['valid']) {
                return response()->json(['success' => false, 'message' => $result['message']], 400);
            }
            $voucher = $result['voucher'];
            if ($voucher->type !== 'amount') {
                return response()->json(['success' => false, 'message' => 'Pe bonuri specifice se acceptă doar vouchere de tip sumă (RON).'], 400);
            }
            $discountAmount = min((float) $voucher->remaining_value, $totalPrice);
            $finalPrice = max(0, $totalPrice - $discountAmount);
            $voucherCode = $voucher->code;
            $voucherType = $voucher->type;
            $voucherId = $voucher->id;
        }

        $items = $standaloneReceipt->items->map(function (StandaloneReceiptItem $item) {
            return [
                'name' => $item->name,
                'quantity' => $item->quantity,
                'price' => (float) $item->unit_price,
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'paymentType' => $request->paymentType,
                'voucher_code' => $voucherCode,
                'voucher_type' => $voucherType,
                'voucher_id' => $voucherId,
                'voucher_discount_amount' => (float) $discountAmount,
            ],
            'receipt' => [
                'totalPrice' => (float) $totalPrice,
                'finalPrice' => $finalPrice,
                'voucher_code' => $voucherCode,
                'voucher_type' => $voucherType,
                'voucher_id' => $voucherId,
                'discount_amount' => (float) $discountAmount,
                'noReceiptNeeded' => $finalPrice <= 0,
            ],
        ]);
    }

    /**
     * Save fiscal receipt log for standalone receipt and mark as paid.
     */
    public function saveFiscalReceiptLog(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Neautentificat'], 401);
        }

        $request->validate([
            'standalone_receipt_id' => 'required|exists:standalone_receipts,id',
            'filename' => 'nullable|string|max:255',
            'status' => 'required|in:success,error',
            'error_message' => 'nullable|string',
            'payment_method' => 'nullable|string|in:CASH,CARD',
            'voucher_id' => 'nullable|exists:vouchers,id',
            'voucher_amount_used' => 'nullable|numeric|min:0',
        ]);

        $receipt = StandaloneReceipt::findOrFail($request->standalone_receipt_id);
        if (!$user->isSuperAdmin() && $user->location && $receipt->location_id !== $user->location->id) {
            return response()->json(['success' => false, 'message' => 'Acces interzis'], 403);
        }

        try {
            DB::transaction(function () use ($request, $receipt) {
                FiscalReceiptLog::create([
                    'type' => 'standalone',
                    'standalone_receipt_id' => $receipt->id,
                    'location_id' => $receipt->location_id,
                    'filename' => $request->filename,
                    'status' => $request->status,
                    'error_message' => $request->error_message,
                ]);

                if ($request->status === 'success' && !$receipt->isPaid()) {
                    $updateData = [
                        'paid_at' => now(),
                        'payment_status' => 'paid',
                        'payment_method' => $request->payment_method ?? null,
                    ];
                    $voucherId = $request->input('voucher_id');
                    $voucherAmountUsed = $request->input('voucher_amount_used') !== null ? (float) $request->input('voucher_amount_used') : null;
                    if ($voucherId && $voucherAmountUsed > 0) {
                        $voucher = Voucher::withoutGlobalScope('location')->where('id', $voucherId)->where('location_id', $receipt->location_id)->first();
                        if ($voucher && $voucher->type === 'amount') {
                            $voucher->use($voucherAmountUsed, $receipt);
                            $updateData['voucher_id'] = $voucher->id;
                        }
                    }
                    $receipt->update($updateData);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Log salvat cu succes',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Mark standalone receipt as paid without fiscal (for locations with fiscal_enabled = false).
     */
    public function markPaidNoFiscal(StandaloneReceipt $standaloneReceipt, Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Neautentificat'], 401);
        }
        if (!$user->isSuperAdmin() && $user->location && $standaloneReceipt->location_id !== $user->location->id) {
            return response()->json(['success' => false, 'message' => 'Acces interzis'], 403);
        }
        if ($standaloneReceipt->isPaid()) {
            return response()->json(['success' => false, 'message' => 'Bonul a fost deja plătit.'], 400);
        }

        $request->validate([
            'payment_method' => 'nullable|in:CASH,CARD',
            'voucher_code' => 'nullable|string|max:32',
            'voucher_id' => 'nullable|exists:vouchers,id',
            'voucher_amount_used' => 'nullable|numeric|min:0',
        ]);

        try {
            $standaloneReceipt->loadMissing('location');
            $voucher = app(VoucherService::class)->resolveVoucherFromRequest($standaloneReceipt->location, $request, 'amount');

            $paymentMethod = $request->input('payment_method');
            $voucherAmountUsed = $request->input('voucher_amount_used') !== null
                ? (float) $request->input('voucher_amount_used')
                : null;

            if (!$paymentMethod) {
                $coveredAmount = $voucherAmountUsed;
                if ($coveredAmount === null && $voucher) {
                    $coveredAmount = min((float) $voucher->remaining_value, (float) $standaloneReceipt->total_amount);
                }

                if ($coveredAmount === null || $coveredAmount < (float) $standaloneReceipt->total_amount) {
                    throw new \InvalidArgumentException('Selectați o metodă de plată.');
                }
            }

            DB::transaction(function () use ($standaloneReceipt, $request, $voucher) {
                $updateData = [
                    'paid_at' => now(),
                    'payment_status' => 'paid',
                    'payment_method' => $request->payment_method ?: null,
                ];

                if ($voucher) {
                    $amountToUse = $request->input('voucher_amount_used') !== null
                        ? (float) $request->input('voucher_amount_used')
                        : min((float) $voucher->remaining_value, (float) $standaloneReceipt->total_amount);

                    if ($amountToUse > (float) $standaloneReceipt->total_amount) {
                        throw new \InvalidArgumentException('Valoarea voucherului nu poate depăși totalul bonului.');
                    }

                    if ($amountToUse <= 0) {
                        throw new \InvalidArgumentException('Voucherul nu are sold.');
                    }

                    $voucher->use($amountToUse, $standaloneReceipt);
                    $updateData['voucher_id'] = $voucher->id;
                    $updateData['payment_status'] = 'paid_voucher';
                }

                $standaloneReceipt->update($updateData);
            });
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true]);
    }
}
