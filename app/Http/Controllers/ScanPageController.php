<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\PlaySession;
use App\Models\PlaySessionProduct;
use App\Models\Product;
use App\Services\ScanService;
use App\Support\ApiResponder;
use Illuminate\Http\Request;
use App\Http\Requests\Scan\LookupBraceletRequest;
use App\Http\Requests\Scan\AssignBraceletRequest;
use App\Http\Requests\Scan\CreateChildRequest;
use App\Http\Requests\Scan\StartSessionRequest;
use App\Http\Requests\AddProductsToSessionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScanPageController extends Controller
{
    protected ScanService $scanService;

    public function __construct(ScanService $scanService)
    {
        $this->scanService = $scanService;
    }

    /**
     * Show scan page
     */
    public function index()
    {
        $user = Auth::user();
        $location = $user->location;
        
        // Get available children for the location
        $children = [];
        
        if ($location) {
            $children = Child::where('location_id', $location->id)
                ->with('guardian')
                ->get();
        }

        return view('scan.index', compact('children'));
    }

    /**
     * Generate new RFID code
     */
    public function generateCode(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponder::error('Neautentificat', 401);
        }
        $location = $user->location;
        
        if (!$location) {
            return ApiResponder::error('Nu există niciun locație în sistem', 400);
        }

        try {
            $code = $this->scanService->generateRandomCode($location);
            $scanEvent = $this->scanService->createScanEvent($location, $code);

            return response()->json([
                'success' => true,
                'code' => $code,
                'expires_at' => $scanEvent->expires_at->toISOString(),
                'message' => 'Cod RFID generat cu succes',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Eroare la generarea codului: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lookup bracelet by code
     */
    public function lookupBracelet(LookupBraceletRequest $request)
    {
        // Normalize code - only trim, preserve case for barcode
        $code = trim($request->code);

        // Folosește tenant-ul utilizatorului autentificat
        $user = Auth::user();
        if (!$user) {
            return ApiResponder::error('Neautentificat', 401);
        }
        $location = $user->location;
        if (!$location) {
            return ApiResponder::error('Utilizatorul nu este asociat cu nicio locație', 400);
        }

        return response()->json($this->scanService->lookupBracelet($code, $location));
    }

    /**
     * Assign bracelet code to child and start session
     */
    public function assignBracelet(AssignBraceletRequest $request)
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponder::error('Neautentificat', 401);
        }
        $location = $user->location;
        
        if (!$location) {
            return ApiResponder::error('Nu există nicio locație în sistem', 400);
        }

        $braceletCode = trim($request->bracelet_code);
        $child = Child::where('id', $request->child_id)
            ->where('location_id', $location->id)
            ->first();

        if (!$child) {
            return ApiResponder::error('Copil nu a fost găsit', 404);
        }

        // Verifică dacă copilul are deja o sesiune activă
        $existingSession = PlaySession::where('child_id', $child->id)
            ->whereNull('ended_at')
            ->first();

        if ($existingSession) {
            $childName = $child->name;
            return ApiResponder::error(
                "Copilul {$childName} are deja o sesiune activă care a început la " . 
                $existingSession->started_at->format('d.m.Y H:i') . 
                ". Te rog oprește sesiunea existentă înainte de a începe una nouă.",
                400
            );
        }

        try {
            $session = $this->scanService->startPlaySession($location, $child, $braceletCode);

            return ApiResponder::success([
                'message' => 'Sesiune pornită cu succes',
                'session' => [
                    'id' => $session->id,
                    'started_at' => $session->started_at->toISOString(),
                    'bracelet_code' => $braceletCode,
                ],
            ]);
        } catch (\Throwable $e) {
            return ApiResponder::error('Nu s-a putut porni sesiunea: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new child and start session with bracelet code
     */
    public function createChild(CreateChildRequest $request)
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponder::error('Neautentificat', 401);
        }
        $location = $user->location;
        
        if (!$location) {
            return ApiResponder::error('Nu există nicio locație în sistem', 400);
        }

        $braceletCode = trim($request->bracelet_code);

        try {
            $data = DB::transaction(function () use ($request, $location, $braceletCode) {
                // Identifică sau creează părintele/tutorul în funcție de datele primite
                if ($request->filled('guardian_id')) {
                    $guardian = Guardian::where('id', $request->guardian_id)
                        ->where('location_id', $location->id)
                        ->first();
                    if (!$guardian) {
                        throw new \Exception('Părintele selectat nu a fost găsit în această locație');
                    }
                    
                    // Verifică dacă părintele existent a acceptat termenii
                    if ($guardian->needsToAcceptLegalDocuments()) {
                        throw new \Exception('Părintele trebuie să accepte termenii și condițiile și politica GDPR înainte de a crea un copil. Te rog acceptă termenii mai întâi.');
                    }
                } else {
                    // Verifică dacă există deja un părinte cu același telefon
                    $existingGuardian = Guardian::where('location_id', $location->id)
                        ->where('phone', $request->guardian_phone)
                        ->first();
                    
                    if ($existingGuardian) {
                        throw new \Exception(
                            "Există deja un părinte cu numărul de telefon {$request->guardian_phone}: {$existingGuardian->name}. " .
                            "Te rog selectează părinte existent în loc de creare nouă."
                        );
                    }
                    
                    // Validate terms acceptance for new guardian
                    if (!$request->boolean('terms_accepted') || !$request->boolean('gdpr_accepted')) {
                        throw new \Exception('Trebuie să acceptați termenii și condițiile și politica GDPR');
                    }
                    
                    $guardian = Guardian::create([
                        'name' => $request->guardian_name,
                        'phone' => $request->guardian_phone,
                        'location_id' => $location->id,
                        'terms_accepted_at' => now(),
                        'gdpr_accepted_at' => now(),
                        'terms_version' => \App\Http\Controllers\LegalController::TERMS_VERSION,
                        'gdpr_version' => \App\Http\Controllers\LegalController::GDPR_VERSION,
                    ]);
                }

                // Generează cod intern
                $namePart = substr(trim($request->first_name), 0, 2);
                $nextPart = strlen(trim($request->first_name)) > 2 ? substr(trim($request->first_name), 2, 2) : substr(trim($request->first_name), 0, 2);
                $internalCode = strtoupper($namePart . $nextPart . rand(100, 999));

                // Creează copilul
                $child = Child::create([
                    'name' => $request->first_name,
                    'birth_date' => null,
                    'allergies' => $request->allergies,
                    'internal_code' => $internalCode,
                    'guardian_id' => $guardian->id,
                    'location_id' => $location->id,
                ]);

                // Pornește sesiunea cu codul de bare
                $session = $this->scanService->startPlaySession($location, $child, $braceletCode);

                return [
                    'child' => $child,
                    'guardian' => $guardian,
                    'bracelet_code' => $braceletCode,
                    'session' => $session,
                ];
            });

            return ApiResponder::success([
                'message' => 'Copil creat și sesiune pornită',
                'data' => [
                    'child' => [
                        'id' => $data['child']->id,
                        'name' => $data['child']->name,
                        'internal_code' => $data['child']->internal_code,
                    ],
                    'guardian' => $data['guardian'],
                    'bracelet_code' => $data['bracelet_code'],
                    'session' => [
                        'id' => $data['session']->id,
                        'started_at' => $data['session']->started_at->toISOString(),
                    ],
                ],
            ]);

        } catch (\Throwable $e) {
            return ApiResponder::error('Nu s-a putut crea copilul și porni sesiunea: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Check if guardian has accepted terms and conditions
     */
    public function checkGuardianTerms(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->location) {
            return ApiResponder::error('Neautentificat sau fără locație', 401);
        }

        $request->validate([
            'guardian_id' => 'required|integer|exists:guardians,id',
        ]);

        $location = $user->location;
        $guardian = Guardian::where('id', $request->guardian_id)
            ->where('location_id', $location->id)
            ->first();

        if (!$guardian) {
            return ApiResponder::error('Părintele nu a fost găsit', 404);
        }

        $needsTerms = $guardian->needsToAcceptTerms();
        $needsGdpr = $guardian->needsToAcceptGdpr();
        $accepted = !$guardian->needsToAcceptLegalDocuments();

        return ApiResponder::success([
            'accepted' => $accepted,
            'needs_terms' => $needsTerms,
            'needs_gdpr' => $needsGdpr,
        ]);
    }

    /**
     * Save guardian terms acceptance
     */
    public function acceptGuardianTerms(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->location) {
            return ApiResponder::error('Neautentificat sau fără locație', 401);
        }

        $request->validate([
            'guardian_id' => 'required|integer|exists:guardians,id',
        ]);

        $location = $user->location;
        $guardian = Guardian::where('id', $request->guardian_id)
            ->where('location_id', $location->id)
            ->first();

        if (!$guardian) {
            return ApiResponder::error('Părintele nu a fost găsit', 404);
        }

        // Update acceptance
        $guardian->update([
            'terms_accepted_at' => now(),
            'gdpr_accepted_at' => now(),
            'terms_version' => \App\Http\Controllers\LegalController::TERMS_VERSION,
            'gdpr_version' => \App\Http\Controllers\LegalController::GDPR_VERSION,
        ]);

        return ApiResponder::success([
            'message' => 'Termenii și condițiile au fost acceptate cu succes',
        ]);
    }

    /**
     * Start a play session for a child
     */
    public function startSession(StartSessionRequest $request)
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponder::error('Neautentificat', 401);
        }
        $location = $user->location;
        
        if (!$location) {
            return ApiResponder::error('Nu există nicio locație în sistem', 400);
        }

        try {
            $child = Child::where('id', $request->child_id)
                ->where('location_id', $location->id)
                ->first();

            if (!$child) {
                return ApiResponder::error('Copil nu a fost găsit', 404);
            }

            $braceletCode = trim($request->bracelet_code);
            $session = $this->scanService->startPlaySession($location, $child, $braceletCode);

            return ApiResponder::success([
                'message' => 'Sesiunea a început cu succes',
                'session' => [
                    'id' => $session->id,
                    'child_name' => $child->name,
                    'parent_name' => $child->guardian->name,
                    'started_at' => $session->started_at->toISOString(),
                    'bracelet_code' => $braceletCode,
                ],
            ]);

        } catch (\Exception $e) {
            return ApiResponder::error('Eroare la începerea sesiunii: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Stop a play session
     */
    public function stopSession(Request $request, $sessionId)
    {
        try {
            // Get session first to verify bracelet code
            $session = PlaySession::with('child')->findOrFail($sessionId);
            
            // Verify bracelet code if provided
            $braceletCode = $request->input('bracelet_code');
            if ($braceletCode && $session->bracelet_code) {
                if ($session->bracelet_code !== trim($braceletCode)) {
                    $child = $session->child;
                    $childName = $child ? $child->name : 'necunoscut';
                    return ApiResponder::error(
                        "Codul scanat ({$braceletCode}) nu corespunde cu sesiunea care se încearcă să fie oprită. " .
                        "Sesiunea aparține copilului {$childName} cu codul {$session->bracelet_code}. " .
                        "Te rog scanează codul corect.",
                        400
                    );
                }
            }
            
            $session = $this->scanService->stopAndUnassign($sessionId);

            return ApiResponder::success([
                'message' => 'Sesiunea a fost oprită cu succes',
                'session' => [
                    'id' => $session->id,
                    'duration_minutes' => $session->getCurrentDurationMinutes(),
                    'duration_formatted' => $session->getFormattedDuration(),
                ],
            ]);

        } catch (\Exception $e) {
            return ApiResponder::error('Eroare la oprirea sesiunii: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Pause a play session
     */
    public function pauseSession(Request $request, $sessionId)
    {
        try {
            $session = $this->scanService->pausePlaySession((int) $sessionId);

            return ApiResponder::success([
                'message' => 'Sesiunea a fost pusă pe pauză',
                'session' => [
                    'id' => $session->id,
                    'is_paused' => $session->isPaused(),
                    'effective_seconds' => $session->getEffectiveDurationSeconds(),
                ],
            ]);
        } catch (\Exception $e) {
            return ApiResponder::error('Eroare la pauzarea sesiunii: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Resume a paused play session
     */
    public function resumeSession(Request $request, $sessionId)
    {
        try {
            $session = $this->scanService->resumePlaySession((int) $sessionId);

            return ApiResponder::success([
                'message' => 'Sesiunea a fost reluată',
                'session' => [
                    'id' => $session->id,
                    'is_paused' => $session->isPaused(),
                    'effective_seconds' => $session->getEffectiveDurationSeconds(),
                    'current_interval_started_at' => optional($session->intervals()->whereNull('ended_at')->latest('started_at')->first())->started_at?->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return ApiResponder::error('Eroare la reluarea sesiunii: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get active sessions
     */
    public function getActiveSessions()
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponder::error('Neautentificat', 401);
        }
        $location = $user->location;
        
        if (!$location) {
            return ApiResponder::error('Nu există nicio locație în sistem', 400);
        }

        $sessions = $this->scanService->getActiveSessions($location);

        return ApiResponder::success(['sessions' => $sessions]);
    }

    /**
     * Get session statistics
     */
    public function getSessionStats()
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponder::error('Neautentificat', 401);
        }
        $location = $user->location;
        
        if (!$location) {
            return ApiResponder::error('Nu există nicio locație în sistem', 400);
        }

        $stats = $this->scanService->getSessionStats($location);

        return ApiResponder::success(['stats' => $stats]);
    }

    /**
     * Get last 3 completed sessions for current tenant
     */
    public function recentCompletedSessions()
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponder::error('Neautentificat', 401);
        }
        $location = $user->location;
        
        if (!$location) {
            return ApiResponder::error('Nu există nicio locație în sistem', 400);
        }

        $list = $this->scanService->getRecentCompletedSessions($location, 3);
        return ApiResponder::success(['recent' => $list]);
    }

    /**
     * Search for children with active sessions
     */
    public function searchChildrenWithActiveSessions(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponder::error('Neautentificat', 401);
        }
        $location = $user->location;
        
        if (!$location) {
            return ApiResponder::error('Nu există nicio locație în sistem', 400);
        }

        $request->validate([
            'q' => 'nullable|string|max:255',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $q = (string) $request->input('q', '');
        $limit = (int) ($request->input('limit', 15));

        // Get children with active sessions only
        $children = Child::where('location_id', $location->id)
            ->whereHas('activeSessions')
            ->when($q !== '', function ($query) use ($q) {
                $like = "%" . str_replace(['%','_'], ['\\%','\\_'], $q) . "%";
                $query->where(function ($inner) use ($like) {
                    $inner->where('name', 'LIKE', $like)
                          ->orWhere('internal_code', 'LIKE', $like)
                          ->orWhereHas('guardian', function ($g) use ($like) {
                              $g->where('name', 'LIKE', $like)
                                ->orWhere('phone', 'LIKE', $like);
                          });
                });
            })
            ->with(['guardian:id,name,phone', 'activeSessions' => function($q) {
                $q->whereNull('ended_at')->with('intervals');
            }])
            ->orderBy('name')
            ->limit($limit)
            ->get(['id','name','internal_code','guardian_id']);

        $results = $children->map(function ($child) {
            $activeSession = $child->activeSessions->first();
            $currentInterval = $activeSession ? $activeSession->intervals->whereStrict('ended_at', null)->sortByDesc('started_at')->first() : null;
            
            return [
                'id' => $child->id,
                'name' => $child->name,
                'internal_code' => $child->internal_code,
                'guardian_name' => optional($child->guardian)->name,
                'guardian_phone' => optional($child->guardian)->phone,
                'bracelet_code' => $activeSession ? $activeSession->bracelet_code : null,
                'session_id' => $activeSession ? $activeSession->id : null,
                'session_started_at' => $activeSession && $activeSession->started_at ? $activeSession->started_at->toISOString() : null,
                'session_is_paused' => $activeSession ? $activeSession->isPaused() : false,
                'session_effective_seconds' => $activeSession ? $activeSession->getEffectiveDurationSeconds() : 0,
                'session_duration_formatted' => $activeSession ? $activeSession->getFormattedDuration() : '00:00',
                'session_current_interval_started_at' => $currentInterval && $currentInterval->started_at ? $currentInterval->started_at->toISOString() : null,
            ];
        });

        return ApiResponder::success([
            'success' => true,
            'children' => $results,
        ]);
    }

    /**
     * Get active session for a specific child
     */
    public function lookupChildSession(Request $request, $childId)
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponder::error('Neautentificat', 401);
        }
        $location = $user->location;
        
        if (!$location) {
            return ApiResponder::error('Nu există nicio locație în sistem', 400);
        }

        $child = Child::where('id', $childId)
            ->where('location_id', $location->id)
            ->with(['guardian'])
            ->first();

        if (!$child) {
            return ApiResponder::error('Copilul nu a fost găsit', 404);
        }

        // Get active session for this child
        $activeSession = PlaySession::where('child_id', $child->id)
            ->whereNull('ended_at')
            ->with('intervals')
            ->first();

        if (!$activeSession) {
            return ApiResponder::error('Copilul nu are o sesiune activă', 404);
        }

        $currentInterval = $activeSession->intervals->whereStrict('ended_at', null)->sortByDesc('started_at')->first();
        
        return ApiResponder::success([
            'success' => true,
            'child' => [
                'id' => $child->id,
                'name' => $child->name,
                'guardian_name' => optional($child->guardian)->name,
            ],
            'bracelet_code' => $activeSession->bracelet_code,
            'active_session' => [
                'id' => $activeSession->id,
                'started_at' => optional($activeSession->started_at)->toISOString(),
                'is_paused' => $activeSession->isPaused(),
                'effective_seconds' => $activeSession->getEffectiveDurationSeconds(),
                'current_interval_started_at' => $currentInterval && $currentInterval->started_at ? $currentInterval->started_at->toISOString() : null,
            ],
        ]);
    }

    /**
     * Add products to a session
     */
    public function addProductsToSession(AddProductsToSessionRequest $request)
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponder::error('Neautentificat', 401);
        }
        $location = $user->location;
        
        if (!$location) {
            return ApiResponder::error('Nu există nicio locație în sistem', 400);
        }

        try {
            $session = PlaySession::where('id', $request->session_id)
                ->where('location_id', $location->id)
                ->first();

            if (!$session) {
                return ApiResponder::error('Sesiunea nu a fost găsită', 404);
            }

            // Verifică că sesiunea nu este plătită (se pot adăuga produse la sesiuni închise dar neplătite)
            if ($session->isPaid()) {
                return ApiResponder::error('Nu se pot adăuga produse la o sesiune deja plătită', 400);
            }

            // Verifică că produsele aparțin tenant-ului și sunt active
            $productIds = collect($request->products)->pluck('product_id')->unique();
            $products = Product::whereIn('id', $productIds)
                ->where('location_id', $location->id)
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            if ($products->count() !== $productIds->count()) {
                return ApiResponder::error('Unul sau mai multe produse nu au fost găsite sau nu sunt active', 400);
            }

            // Adaugă produsele la sesiune
            $addedProducts = [];
            foreach ($request->products as $productData) {
                $product = $products->get($productData['product_id']);
                if (!$product) {
                    continue;
                }

                $sessionProduct = PlaySessionProduct::create([
                    'play_session_id' => $session->id,
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'unit_price' => $product->price, // Salvează prețul la momentul adăugării
                ]);

                $addedProducts[] = [
                    'id' => $sessionProduct->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $sessionProduct->quantity,
                    'unit_price' => $sessionProduct->unit_price,
                    'total_price' => $sessionProduct->total_price,
                ];
            }

            return ApiResponder::success([
                'message' => 'Produse adăugate cu succes',
                'products' => $addedProducts,
            ]);

        } catch (\Throwable $e) {
            return ApiResponder::error('Nu s-au putut adăuga produsele: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get available products for the tenant
     */
    public function getAvailableProducts()
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponder::error('Neautentificat', 401);
        }
        $location = $user->location;
        
        if (!$location) {
            return ApiResponder::error('Nu există nicio locație în sistem', 400);
        }

        try {
            $products = Product::where('location_id', $location->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'price']);

            return ApiResponder::success([
                'products' => $products,
            ]);

        } catch (\Throwable $e) {
            return ApiResponder::error('Nu s-au putut încărca produsele: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get products for a session
     */
    public function getSessionProducts($sessionId)
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponder::error('Neautentificat', 401);
        }
        $location = $user->location;
        
        if (!$location) {
            return ApiResponder::error('Nu există nicio locație în sistem', 400);
        }

        try {
            $session = PlaySession::where('id', $sessionId)
                ->where('location_id', $location->id)
                ->first();

            if (!$session) {
                return ApiResponder::error('Sesiunea nu a fost găsită', 404);
            }

            $products = PlaySessionProduct::where('play_session_id', $session->id)
                ->with('product')
                ->get()
                ->map(function ($sp) {
                    return [
                        'id' => $sp->id,
                        'product_id' => $sp->product_id,
                        'product_name' => $sp->product->name ?? 'Produs',
                        'quantity' => $sp->quantity,
                        'unit_price' => $sp->unit_price,
                        'total_price' => $sp->total_price,
                    ];
                });

            return ApiResponder::success([
                'products' => $products,
            ]);

        } catch (\Throwable $e) {
            return ApiResponder::error('Nu s-au putut încărca produsele: ' . $e->getMessage(), 500);
        }
    }
}
