<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\PlaySessionRepositoryInterface;
use App\Support\ApiResponder;
use App\Models\PlaySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{
    public function __construct(private PlaySessionRepositoryInterface $sessions)
    {
    }
    /** Show sessions page */
    public function index()
    {
        return view('sessions.index');
    }

    /** Server-side data for sessions table */
    public function data(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->location) {
            return ApiResponder::error('Neautentificat sau fără locație', 401);
        }

        $locationId = $user->location->id;

        // Inputs
        $page = max(1, (int) $request->input('page', 1));
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }
        $search = trim((string) $request->input('search', ''));
        $sortBy = (string) $request->input('sort_by', 'started_at');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        
        // Date filter - default to today if not provided
        $dateInput = $request->input('date');
        $date = $dateInput ? \Carbon\Carbon::parse($dateInput) : \Carbon\Carbon::today();

        // Allowed sorting columns map to SQL columns
        $result = $this->sessions->paginateSessions(
            $locationId,
            $page,
            $perPage,
            $search === '' ? null : $search,
            $sortBy,
            $sortDir,
            $date
        );

        return ApiResponder::success([
            'data' => $result['rows'],
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $result['total'],
                'total_pages' => (int) ceil($result['total'] / max(1, $perPage)),
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
                'search' => $search,
                'date' => $date->format('Y-m-d'),
            ],
        ]);
    }

    /** Show session details */
    public function show($id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // SUPER_ADMIN poate vedea sesiuni din toate locațiile
        $query = PlaySession::where('id', $id);
        
        if (!$user->isSuperAdmin() && $user->location) {
            $query->where('location_id', $user->location->id);
        }

        $session = $query->with(['child.guardian', 'intervals' => function($query) {
                $query->orderBy('started_at', 'asc');
            }, 'products.product', 'location'])
            ->first();

        if (!$session) {
            abort(404, 'Sesiunea nu a fost găsită');
        }

        return view('sessions.show', compact('session'));
    }

    /** Generate receipt for session */
    public function receipt($id)
    {
        $user = Auth::user();
        if (!$user || !$user->location) {
            abort(401, 'Neautentificat');
        }

        $session = PlaySession::where('id', $id)
            ->where('location_id', $user->location->id)
            ->with(['child.guardian', 'location', 'intervals' => function($query) {
                $query->orderBy('started_at', 'asc');
            }])
            ->first();

        if (!$session) {
            abort(404, 'Sesiunea nu a fost găsită');
        }

        if (!$session->ended_at) {
            abort(400, 'Bonul poate fi generat doar pentru sesiuni finalizate');
        }

        $hasProducts = $session->getProductsTotalPrice() > 0;

        // Ensure price is calculated
        if (!$session->calculated_price) {
            $session->saveCalculatedPrice();
            $session->refresh();
        }

        // Load products relationship if not already loaded
        if (!$session->relationLoaded('products')) {
            $session->load('products.product');
        }

        return view('sessions.receipt', compact('session'));
    }

    /**
     * Prepare fiscal receipt data for printing session
     * Returns calculated data that will be sent to bridge from client-side
     */
    public function prepareFiscalPrint($id, Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Neautentificat'
            ], 401);
        }
        
        $request->validate([
            'paymentType' => 'required|in:CASH,CARD',
            'voucherHours' => 'nullable|numeric|min:0',
        ]);

        // For SUPER_ADMIN, can access any session (location comes from session)
        // For other roles, restrict to their location
        $sessionQuery = PlaySession::where('id', $id);
        
        if (!$user->isSuperAdmin() && $user->location) {
            $sessionQuery->where('location_id', $user->location->id);
        }
        
        $session = $sessionQuery->with(['products.product', 'location'])->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Sesiunea nu a fost găsită'
            ], 404);
        }

        if (!$session->ended_at) {
            return response()->json([
                'success' => false,
                'message' => 'Bonul poate fi generat doar pentru sesiuni finalizate'
            ], 400);
        }

        $hasProducts = $session->getProductsTotalPrice() > 0;

        if ($session->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Sesiunea a fost deja plătită'
            ], 400);
        }

        // Ensure price is calculated (recalculate if price is 0, as it might be incorrect)
        if (!$session->calculated_price || $session->calculated_price == 0) {
            $session->saveCalculatedPrice();
            $session->refresh();
        }

        // Get voucher hours from request
        $voucherHours = $request->input('voucherHours', 0);
        if ($voucherHours > 0) {
            $voucherHours = (float) $voucherHours;
        } else {
            $voucherHours = 0;
        }

        // Get pricing service
        $pricingService = app(\App\Services\PricingService::class);
        
        // Get effective duration in seconds and convert to hours and minutes
        $seconds = $session->getEffectiveDurationSeconds();
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        // Format duration for display (e.g., "1h 15m" or "45m")
        $duration = $this->formatDuration($hours, $minutes);

        // Calculate rounded duration (fiscalized duration)
        $durationInHours = $pricingService->getDurationInHours($session);
        $roundedHours = $pricingService->roundToHalfHour($durationInHours);
        
        // Validate voucher hours don't exceed session duration (only for paid time sessions)
        if (!$isFreeSession && $voucherHours > $roundedHours) {
            return response()->json([
                'success' => false,
                'message' => 'Orele de voucher nu pot depăși durata sesiunii (' . $this->formatDuration(floor($roundedHours), round(($roundedHours - floor($roundedHours)) * 60)) . ')'
            ], 400);
        }

        // Get price per hour (use the one saved at calculation time, or calculate current rate)
        $pricePerHour = $session->price_per_hour_at_calculation ?? $pricingService->getHourlyRate($session->location, $session->started_at);
        
        // Calculate voucher price
        $voucherPrice = $voucherHours * $pricePerHour;

        // Get prices separately
        $timePrice = $session->calculated_price ?? $session->calculatePrice();
        $productsPrice = $session->getProductsTotalPrice();
        $totalPrice = $timePrice + $productsPrice;

        // Voucher applies ONLY to time, not to products
        // Calculate final time price after voucher discount
        $finalTimePrice = max(0, $timePrice - $voucherPrice);
        
        // Final total price = final time price + products price (products are never discounted by voucher)
        $finalPrice = $finalTimePrice + $productsPrice;
        
        // If voucher covers all time AND there are no products, no receipt needed
        // If there are products, we still need a receipt even if time is fully covered
        $noReceiptNeeded = ($finalTimePrice <= 0 && $productsPrice <= 0);

        // Calculate billed hours (total hours minus voucher hours)
        $billedHours = max(0, $roundedHours - $voucherHours);
        
        // Format rounded duration
        $roundedHoursInt = floor($roundedHours);
        $roundedMinutes = round(($roundedHours - $roundedHoursInt) * 60);
        // Handle case where roundedMinutes might be 60 (from rounding)
        if ($roundedMinutes >= 60) {
            $roundedHoursInt += 1;
            $roundedMinutes = 0;
        }
        $durationFiscalized = $this->formatDuration($roundedHoursInt, $roundedMinutes);

        // Format billed hours for display
        $billedHoursInt = floor($billedHours);
        $billedMinutes = round(($billedHours - $billedHoursInt) * 60);
        if ($billedMinutes >= 60) {
            $billedHoursInt += 1;
            $billedMinutes = 0;
        }
        $durationBilled = $this->formatDuration($billedHoursInt, $billedMinutes);

        // Calculate billed time price (only hours that will be charged)
        $billedTimePrice = $billedHours * $pricePerHour;

        // Get products data - ensure we use the actual product name
        $products = $session->products->map(function($sp) {
            // Get product name from loaded relation (should be loaded via ->with(['products.product']))
            $productName = null;
            if ($sp->product && $sp->product->name) {
                $productName = trim($sp->product->name);
            }
            
            // If name not found in relation, try loading product directly
            if (empty($productName) && $sp->product_id) {
                $product = \App\Models\Product::find($sp->product_id);
                if ($product && $product->name) {
                    $productName = trim($product->name);
                }
            }
            
            // Ensure we always have a name - use product ID as fallback if name is missing
            if (empty($productName)) {
                $productName = 'Produs ID: ' . $sp->product_id;
            }
            
            return [
                'name' => $productName,
                'quantity' => $sp->quantity,
                'unit_price' => (float) $sp->unit_price,
                'total_price' => (float) $sp->total_price,
            ];
        })->values();

        // Get location name
        $locationName = $session->location->name ?? 'Loc de Joacă';

        // Product name
        $productName = 'Ora de joacă';

        // Build items array for bridge: time item + product items
        // Only include items if there's something to pay (finalPrice > 0)
        $items = [];
        
        if (!$noReceiptNeeded) {
            // Add time item ONLY if there's time to bill (billed hours > 0 and final time price > 0)
            // If voucher covers all time, don't include time item on receipt
            if ($billedHours > 0 && $finalTimePrice > 0) {
                $items[] = [
                    'name' => $productName . ' (' . $durationBilled . ')',
                    'quantity' => 1,
                    'price' => (float) $finalTimePrice,
                ];
            }
            
            // Add product items (products are never affected by voucher)
            foreach ($products as $product) {
                if ($product['total_price'] > 0) {
                    $items[] = [
                        'name' => $product['name'],
                        'quantity' => $product['quantity'],
                        'price' => (float) $product['unit_price'], // Use unit price, not total
                    ];
                }
            }
        }

        // Return data for client-side bridge call
        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'paymentType' => $request->paymentType,
                'voucherHours' => $voucherHours,
                // Keep legacy fields for backward compatibility (not used if items is present)
                'productName' => $productName,
                'duration' => $durationBilled,
                'price' => max(0, $finalPrice),
            ],
            'receipt' => [
                'locationName' => $locationName,
                'timePrice' => (float) $timePrice,
                'finalTimePrice' => (float) $finalTimePrice,
                'billedTimePrice' => (float) $finalTimePrice, // Use finalTimePrice for display
                'voucherHours' => $voucherHours,
                'voucherPrice' => (float) $voucherPrice,
                'durationReal' => $duration,
                'durationFiscalized' => $durationFiscalized,
                'durationBilled' => $durationBilled,
                'products' => $products,
                'productsPrice' => (float) $productsPrice,
                'totalPrice' => (float) $totalPrice,
                'finalPrice' => max(0, $finalPrice),
                'noReceiptNeeded' => $noReceiptNeeded,
            ],
            'session' => [
                'id' => $session->id,
            ],
        ]);
    }

    /**
     * Prepare fiscal receipt data for printing multiple sessions combined
     * Returns calculated data that will be sent to bridge from client-side
     */
    public function prepareCombinedFiscalPrint(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Neautentificat'
            ], 401);
        }
        
        $request->validate([
            'session_ids' => 'required|array|min:2',
            'session_ids.*' => 'required|integer|exists:play_sessions,id',
            'paymentType' => 'required|in:CASH,CARD',
            'voucherHours' => 'nullable|numeric|min:0',
        ]);

        $sessionIds = $request->input('session_ids');
        
        // Load all sessions
        $sessionQuery = PlaySession::whereIn('id', $sessionIds)
            ->with(['products.product', 'location', 'child']);
        
        if (!$user->isSuperAdmin() && $user->location) {
            $sessionQuery->where('location_id', $user->location->id);
        }
        
        $sessions = $sessionQuery->get();

        if ($sessions->count() !== count($sessionIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Unele sesiuni nu au fost găsite'
            ], 404);
        }

        if ($sessions->count() < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Trebuie să selectați minim 2 sesiuni pentru bon combinat'
            ], 400);
        }

        // Validate all sessions are from the same location
        $locationIds = $sessions->pluck('location_id')->unique();
        if ($locationIds->count() > 1) {
            return response()->json([
                'success' => false,
                'message' => 'Toate sesiunile trebuie să fie din aceeași locație'
            ], 400);
        }
        $location = $sessions->first()->location;

        // Validate all sessions are ended, unpaid, and not Birthday/Jungle
        foreach ($sessions as $session) {
            if (!$session->ended_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toate sesiunile trebuie să fie finalizate'
                ], 400);
            }


            if ($session->isPaid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toate sesiunile trebuie să fie neplătite'
                ], 400);
            }
        }

        // Ensure prices are calculated for all sessions
        foreach ($sessions as $session) {
            if (!$session->calculated_price || $session->calculated_price == 0) {
                $session->saveCalculatedPrice();
                $session->refresh();
            }
        }

        // Get voucher hours from request
        $voucherHours = $request->input('voucherHours', 0);
        if ($voucherHours > 0) {
            $voucherHours = (float) $voucherHours;
        } else {
            $voucherHours = 0;
        }

        // Get pricing service
        $pricingService = app(\App\Services\PricingService::class);

        // Calculate totals across all sessions
        $totalTimePrice = 0;
        $totalProductsPrice = 0;
        $totalRoundedHours = 0;
        $allProducts = collect();
        $timeItems = [];

        foreach ($sessions as $session) {
            // Calculate rounded duration for this session
            $durationInHours = $pricingService->getDurationInHours($session);
            $roundedHours = $pricingService->roundToHalfHour($durationInHours);
            $totalRoundedHours += $roundedHours;

            // Get price per hour for this session
            $pricePerHour = $session->price_per_hour_at_calculation ?? $pricingService->getHourlyRate($session->location, $session->started_at);
            
            // Get time price
            $timePrice = $session->calculated_price ?? $session->calculatePrice();
            $totalTimePrice += $timePrice;

            // Format duration for this session
            $roundedHoursInt = floor($roundedHours);
            $roundedMinutes = round(($roundedHours - $roundedHoursInt) * 60);
            if ($roundedMinutes >= 60) {
                $roundedHoursInt += 1;
                $roundedMinutes = 0;
            }
            $durationBilled = $this->formatDuration($roundedHoursInt, $roundedMinutes);

            // Get child name for preview
            $childName = $session->child ? $session->child->name : 'Copil necunoscut';
            
            // Store time item for this session (will be added to items if billed hours > 0)
            $timeItems[] = [
                'duration' => $durationBilled,
                'roundedHours' => $roundedHours,
                'price' => $timePrice,
                'pricePerHour' => $pricePerHour,
                'childName' => $childName, // For preview only, not sent to bridge
                'sessionId' => $session->id, // For reference
            ];

            // Collect products from this session
            $sessionProducts = $session->products->map(function($sp) {
                $productName = null;
                if ($sp->product && $sp->product->name) {
                    $productName = trim($sp->product->name);
                }
                
                if (empty($productName) && $sp->product_id) {
                    $product = \App\Models\Product::find($sp->product_id);
                    if ($product && $product->name) {
                        $productName = trim($product->name);
                    }
                }
                
                if (empty($productName)) {
                    $productName = 'Produs ID: ' . $sp->product_id;
                }
                
                return [
                    'name' => $productName,
                    'quantity' => $sp->quantity,
                    'unit_price' => (float) $sp->unit_price,
                    'total_price' => (float) $sp->total_price,
                ];
            });

            $allProducts = $allProducts->merge($sessionProducts);
            $totalProductsPrice += $session->getProductsTotalPrice();
        }

        // Validate voucher hours don't exceed total duration
        if ($voucherHours > $totalRoundedHours) {
            return response()->json([
                'success' => false,
                'message' => 'Orele de voucher nu pot depăși durata totală (' . $this->formatDuration(floor($totalRoundedHours), round(($totalRoundedHours - floor($totalRoundedHours)) * 60)) . ')'
            ], 400);
        }

        // Calculate voucher price by subtracting full hours from first child(ren) that have at least 1 hour
        // Find which child to apply voucher to (first one with at least voucherHours)
        $voucherPrice = 0;
        $remainingVoucherHours = $voucherHours;
        $adjustedTimeItems = [];
        
        foreach ($timeItems as $index => $timeItem) {
            if ($remainingVoucherHours <= 0) {
                // No more voucher to apply, add full item
                $adjustedTimeItems[] = $timeItem;
                continue;
            }
            
            // Check if this child has enough hours for remaining voucher
            if ($timeItem['roundedHours'] >= $remainingVoucherHours) {
                // Apply remaining voucher to this child
                $adjustedHours = $timeItem['roundedHours'] - $remainingVoucherHours;
                $voucherPrice += $remainingVoucherHours * $timeItem['pricePerHour'];
                
                if ($adjustedHours > 0) {
                    // Child still has some hours left
                    $adjustedHoursInt = floor($adjustedHours);
                    $adjustedMinutes = round(($adjustedHours - $adjustedHoursInt) * 60);
                    if ($adjustedMinutes >= 60) {
                        $adjustedHoursInt += 1;
                        $adjustedMinutes = 0;
                    }
                    $adjustedDuration = $this->formatDuration($adjustedHoursInt, $adjustedMinutes);
                    
                    $adjustedTimeItems[] = [
                        'duration' => $adjustedDuration,
                        'roundedHours' => $adjustedHours,
                        'price' => $adjustedHours * $timeItem['pricePerHour'],
                        'pricePerHour' => $timeItem['pricePerHour'],
                        'childName' => $timeItem['childName'],
                        'sessionId' => $timeItem['sessionId'],
                    ];
                }
                // Voucher fully applied
                $remainingVoucherHours = 0;
            } else {
                // Apply full hours from this child
                $voucherPrice += $timeItem['roundedHours'] * $timeItem['pricePerHour'];
                $remainingVoucherHours -= $timeItem['roundedHours'];
                // This child's time is fully covered by voucher, don't add to adjusted items
            }
        }

        // Calculate final time price after voucher discount
        $finalTimePrice = max(0, $totalTimePrice - $voucherPrice);
        
        // Final total price = final time price + products price
        $finalPrice = $finalTimePrice + $totalProductsPrice;

        // If voucher covers all time AND there are no products, no receipt needed
        $noReceiptNeeded = ($finalTimePrice <= 0 && $totalProductsPrice <= 0);

        // Calculate billed hours (total hours minus voucher hours)
        $billedHours = max(0, $totalRoundedHours - $voucherHours);

        // Build items array for bridge: separate items for each child with adjusted hours after voucher
        $items = [];
        
        if (!$noReceiptNeeded) {
            // Add separate time items for each child with adjusted hours (after voucher deduction)
            // adjustedTimeItems already has hours reduced by voucher
            foreach ($adjustedTimeItems as $timeItem) {
                if ($timeItem['roundedHours'] > 0 && $timeItem['price'] > 0) {
                    $items[] = [
                        'name' => 'Ora de joacă (' . $timeItem['duration'] . ')',
                        'quantity' => 1,
                        'price' => (float) $timeItem['price'],
                    ];
                }
            }
            
            // Add all product items
            foreach ($allProducts as $product) {
                if ($product['total_price'] > 0) {
                    $items[] = [
                        'name' => $product['name'],
                        'quantity' => $product['quantity'],
                        'price' => (float) $product['unit_price'],
                    ];
                }
            }
        }

        // Format total duration for display
        $totalRoundedHoursInt = floor($totalRoundedHours);
        $totalRoundedMinutes = round(($totalRoundedHours - $totalRoundedHoursInt) * 60);
        if ($totalRoundedMinutes >= 60) {
            $totalRoundedHoursInt += 1;
            $totalRoundedMinutes = 0;
        }
        $durationFiscalized = $this->formatDuration($totalRoundedHoursInt, $totalRoundedMinutes);

        // Format billed hours for display
        $billedHoursInt = floor($billedHours);
        $billedMinutes = round(($billedHours - $billedHoursInt) * 60);
        if ($billedMinutes >= 60) {
            $billedHoursInt += 1;
            $billedMinutes = 0;
        }
        $durationBilled = $this->formatDuration($billedHoursInt, $billedMinutes);

        // Get location name
        $locationName = $location->name ?? 'Loc de Joacă';

        // Return data for client-side bridge call
        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'paymentType' => $request->paymentType,
                'voucherHours' => $voucherHours,
                'productName' => 'Ora de joacă',
                'duration' => $durationBilled,
                'price' => max(0, $finalPrice),
            ],
            'receipt' => [
                'locationName' => $locationName,
                'timePrice' => (float) $totalTimePrice,
                'finalTimePrice' => (float) $finalTimePrice,
                'billedTimePrice' => (float) $finalTimePrice,
                'voucherHours' => $voucherHours,
                'voucherPrice' => (float) $voucherPrice,
                'durationFiscalized' => $durationFiscalized,
                'durationBilled' => $durationBilled,
                'timeItems' => $adjustedTimeItems, // Include adjusted time items (after voucher) with child names for preview
                'originalTimeItems' => $timeItems, // Original time items before voucher adjustment
                'products' => $allProducts->values()->toArray(),
                'productsPrice' => (float) $totalProductsPrice,
                'totalPrice' => (float) ($totalTimePrice + $totalProductsPrice),
                'finalPrice' => max(0, $finalPrice),
                'noReceiptNeeded' => $noReceiptNeeded,
            ],
            'sessions' => [
                'ids' => $sessionIds,
            ],
        ]);
    }

    /**
     * Save fiscal receipt log
     */
    public function saveFiscalReceiptLog(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Neautentificat'
            ], 401);
        }
        
        $request->validate([
            'play_session_id' => 'required|exists:play_sessions,id',
            'filename' => 'nullable|string|max:255',
            'status' => 'required|in:success,error',
            'error_message' => 'nullable|string',
            'voucher_hours' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|string|in:paid,paid_voucher',
            'payment_method' => 'nullable|string|in:CASH,CARD',
        ]);
        
        try {
            // Get tenant from play session
            $playSession = PlaySession::findOrFail($request->play_session_id);
            
            $voucherHours = $request->input('voucher_hours', null);
            if ($voucherHours !== null) {
                $voucherHours = (float) $voucherHours;
            }
            
            $log = \App\Models\FiscalReceiptLog::create([
                'type' => 'session',
                'play_session_id' => $request->play_session_id,
                'location_id' => $playSession->location_id,
                'filename' => $request->filename,
                'status' => $request->status,
                'error_message' => $request->error_message,
                'voucher_hours' => $voucherHours,
            ]);
            
            // Mark session as paid if receipt was successfully printed
            if ($request->status === 'success' && !$playSession->isPaid()) {
                $updateData = [
                    'paid_at' => now(),
                ];
                
                // Add voucher hours and payment status if provided
                if ($voucherHours !== null) {
                    $updateData['voucher_hours'] = $voucherHours;
                }
                
                $paymentStatus = $request->input('payment_status', 'paid');
                if ($paymentStatus === 'paid_voucher' || ($voucherHours !== null && $voucherHours > 0)) {
                    $updateData['payment_status'] = 'paid_voucher';
                } else {
                    $updateData['payment_status'] = 'paid';
                }
                
                // Add payment method if provided
                $paymentMethod = $request->input('payment_method');
                if ($paymentMethod && in_array($paymentMethod, ['CASH', 'CARD'])) {
                    $updateData['payment_method'] = $paymentMethod;
                }
                
                $playSession->update($updateData);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Log salvat cu succes',
                'log' => $log,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Eroare la salvarea logului: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save fiscal receipt log for combined receipt (multiple sessions)
     */
    public function saveCombinedFiscalReceiptLog(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Neautentificat'
            ], 401);
        }
        
        $request->validate([
            'play_session_ids' => 'required|array|min:2',
            'play_session_ids.*' => 'required|integer|exists:play_sessions,id',
            'filename' => 'nullable|string|max:255',
            'status' => 'required|in:success,error',
            'error_message' => 'nullable|string',
            'voucher_hours' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|string|in:paid,paid_voucher',
            'payment_method' => 'nullable|string|in:CASH,CARD',
        ]);
        
        try {
            $sessionIds = $request->input('play_session_ids');
            
            // Load all sessions to get location_id
            $sessions = PlaySession::whereIn('id', $sessionIds)->get();
            
            if ($sessions->count() !== count($sessionIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unele sesiuni nu au fost găsite'
                ], 404);
            }

            // Validate all sessions are from the same location
            $locationIds = $sessions->pluck('location_id')->unique();
            if ($locationIds->count() > 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toate sesiunile trebuie să fie din aceeași locație'
                ], 400);
            }
            
            $locationId = $sessions->first()->location_id;
            
            $voucherHours = $request->input('voucher_hours', null);
            if ($voucherHours !== null) {
                $voucherHours = (float) $voucherHours;
            }
            
            // Create log with play_session_ids array and play_session_id NULL
            $log = \App\Models\FiscalReceiptLog::create([
                'type' => 'session',
                'play_session_id' => null, // NULL for combined receipts
                'play_session_ids' => $sessionIds, // Array of session IDs
                'location_id' => $locationId,
                'filename' => $request->filename,
                'status' => $request->status,
                'error_message' => $request->error_message,
                'voucher_hours' => $voucherHours,
            ]);
            
            // Mark all sessions as paid if receipt was successfully printed
            if ($request->status === 'success') {
                $updateData = [
                    'paid_at' => now(),
                ];
                
                // Add voucher hours and payment status if provided
                if ($voucherHours !== null) {
                    $updateData['voucher_hours'] = $voucherHours;
                }
                
                $paymentStatus = $request->input('payment_status', 'paid');
                if ($paymentStatus === 'paid_voucher' || ($voucherHours !== null && $voucherHours > 0)) {
                    $updateData['payment_status'] = 'paid_voucher';
                } else {
                    $updateData['payment_status'] = 'paid';
                }
                
                // Add payment method if provided
                $paymentMethod = $request->input('payment_method');
                if ($paymentMethod && in_array($paymentMethod, ['CASH', 'CARD'])) {
                    $updateData['payment_method'] = $paymentMethod;
                }
                
                // Update all sessions
                PlaySession::whereIn('id', $sessionIds)
                    ->whereNull('paid_at') // Only update unpaid sessions
                    ->update($updateData);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Log salvat cu succes',
                'log' => $log,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Eroare la salvarea logului: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark session as paid with voucher (no receipt needed)
     */
    public function markPaidWithVoucher($id, Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Neautentificat'
            ], 401);
        }
        
        $request->validate([
            'voucher_hours' => 'required|numeric|min:0',
        ]);

        // For SUPER_ADMIN, can access any session (location comes from session)
        // For other roles, restrict to their location
        $sessionQuery = PlaySession::where('id', $id);
        
        if (!$user->isSuperAdmin() && $user->location) {
            $sessionQuery->where('location_id', $user->location->id);
        }
        
        $session = $sessionQuery->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Sesiunea nu a fost găsită'
            ], 404);
        }

        if ($session->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Sesiunea a fost deja plătită'
            ], 400);
        }

        $voucherHours = (float) $request->voucher_hours;

        // Validate voucher hours don't exceed session duration
        $pricingService = app(\App\Services\PricingService::class);
        $durationInHours = $pricingService->getDurationInHours($session);
        $roundedHours = $pricingService->roundToHalfHour($durationInHours);
        
        if ($voucherHours > $roundedHours) {
            return response()->json([
                'success' => false,
                'message' => 'Orele de voucher nu pot depăși durata sesiunii (' . $this->formatDuration(floor($roundedHours), round(($roundedHours - floor($roundedHours)) * 60)) . ')'
            ], 400);
        }

        // Mark session as paid with voucher
        $session->update([
            'paid_at' => now(),
            'voucher_hours' => $voucherHours,
            'payment_status' => 'paid_voucher',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesiunea a fost marcată ca plătită cu voucher',
            'session' => [
                'id' => $session->id,
                'voucher_hours' => $session->voucher_hours,
                'payment_status' => $session->payment_status,
            ],
        ]);
    }


    /**
     * Restart a stopped session (Super Admin only)
     * This reactivates a session that was stopped but not yet paid
     */
    public function restartSession($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Neautentificat'
            ], 401);
        }

        // Only super admin can restart sessions
        if (!$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Nu aveți permisiunea de a reporni sesiuni'
            ], 403);
        }

        // Ensure ID is an integer
        $sessionId = (int) $id;
        
        // Get session (super admin can access any session)
        try {
            $session = PlaySession::findOrFail($sessionId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sesiunea nu a fost găsită (ID: ' . $sessionId . ')'
            ], 404);
        }

        // Verify session is stopped
        if ($session->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Sesiunea este deja activă'
            ], 400);
        }

        // Verify session is not paid
        if ($session->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Nu se poate reporni o sesiune plătită'
            ], 400);
        }

        try {
            $session->restart();
            
            return response()->json([
                'success' => true,
                'message' => 'Sesiunea a fost repornită cu succes',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Eroare la repornirea sesiunii: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle payment status for a session (Super Admin only)
     */
    public function togglePaymentStatus($id, Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Neautentificat'
            ], 401);
        }

        // Only super admin can toggle payment status
        if (!$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Nu aveți permisiunea de a modifica statusul de plată'
            ], 403);
        }

        // Validate payment_method if provided
        $request->validate([
            'payment_method' => 'nullable|in:CASH,CARD',
        ]);

        // Ensure ID is an integer
        $sessionId = (int) $id;
        
        // Get session (super admin can access any session)
        // Use findOrFail to get better error message, but catch it to return JSON
        try {
            $session = PlaySession::findOrFail($sessionId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Log for debugging
            \Log::warning('Session not found in togglePaymentStatus', [
                'requested_id' => $id,
                'casted_id' => $sessionId,
                'user_id' => $user->id,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Sesiunea nu a fost găsită (ID: ' . $sessionId . ')'
            ], 404);
        }

        // Toggle payment status
        if ($session->isPaid()) {
            // Mark as unpaid
            $session->update([
                'paid_at' => null,
                'payment_status' => null,
                'payment_method' => null,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Sesiunea a fost marcată ca neplătită',
                'is_paid' => false,
            ]);
        } else {
            // Mark as paid
            $paymentMethod = $request->input('payment_method');
            
            $updateData = [
                'paid_at' => now(),
                'payment_status' => 'paid',
            ];
            
            // Add payment method if provided
            if ($paymentMethod && in_array($paymentMethod, ['CASH', 'CARD'])) {
                $updateData['payment_method'] = $paymentMethod;
            }
            
            $session->update($updateData);
            
            return response()->json([
                'success' => true,
                'message' => 'Sesiunea a fost marcată ca plătită' . ($paymentMethod ? ' (' . ($paymentMethod === 'CASH' ? 'Cash' : 'Card') . ')' : ''),
                'is_paid' => true,
                'payment_method' => $paymentMethod,
            ]);
        }
    }

    /**
     * Format duration as "Xh Ym" or "Xh" if no minutes, or "Ym" if no hours
     */
    private function formatDuration(int $hours, int $minutes): string
    {
        if ($hours === 0 && $minutes === 0) {
            return '0m';
        }
        
        if ($hours === 0) {
            return "{$minutes}m";
        }
        
        if ($minutes === 0) {
            return "{$hours}h";
        }
        
        return "{$hours}h {$minutes}m";
    }
}


