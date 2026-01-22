<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Child;
use App\Models\PlaySession;
use App\Support\ActionLogger;
use App\Http\Controllers\LegalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuardianController extends Controller
{
    /**
     * Display a listing of guardians
     */
    public function index()
    {
        $user = Auth::user();
        $location = $user->location;
        
        if (!$location) {
            return redirect($this->getHomeRoute())->with('error', 'Utilizatorul nu este asociat cu nicio locație');
        }

        // Stats for cards - only counts
        $totalGuardians = Guardian::where('location_id', $location->id)->count();
        $guardiansWithChildren = Guardian::where('location_id', $location->id)
            ->has('children')
            ->count();
        $guardiansWithoutChildren = $totalGuardians - $guardiansWithChildren;

        return view('guardians.index', [
            'totalGuardians' => $totalGuardians,
            'guardiansWithChildren' => $guardiansWithChildren,
            'guardiansWithoutChildren' => $guardiansWithoutChildren,
        ]);
    }

    /**
     * Show the form for creating a new guardian
     */
    public function create()
    {
        // STAFF nu are acces la crearea de părinți
        if (Auth::user()->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        $user = Auth::user();
        $location = $user->location;
        
        if (!$location) {
            return redirect($this->getHomeRoute())->with('error', 'Utilizatorul nu este asociat cu nicio locație');
        }

        return view('guardians.create');
    }

    /**
     * Store a newly created guardian
     */
    public function store(Request $request)
    {
        // STAFF nu are acces la crearea de părinți
        if (Auth::user()->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        $user = Auth::user();
        $location = $user->location;
        
        if (!$location) {
            return redirect($this->getHomeRoute())->with('error', 'Utilizatorul nu este asociat cu nicio locație');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'terms_accepted' => 'required|accepted',
            'gdpr_accepted' => 'required|accepted',
        ], [
            'terms_accepted.required' => 'Trebuie să acceptați termenii și condițiile',
            'terms_accepted.accepted' => 'Trebuie să acceptați termenii și condițiile',
            'gdpr_accepted.required' => 'Trebuie să acceptați politica de protecție a datelor (GDPR)',
            'gdpr_accepted.accepted' => 'Trebuie să acceptați politica de protecție a datelor (GDPR)',
        ]);

        $guardian = Guardian::create([
            'location_id' => $location->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'notes' => $request->notes,
            'terms_accepted_at' => now(),
            'gdpr_accepted_at' => now(),
            'terms_version' => LegalController::TERMS_VERSION,
            'gdpr_version' => LegalController::GDPR_VERSION,
        ]);

        ActionLogger::logCrud('created', 'Guardian', $guardian->id, [
            'name' => $guardian->name,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'guardian' => $guardian,
                'message' => 'Părintele a fost adăugat cu succes',
            ]);
        }

        return redirect()->route('guardians.index')->with('success', 'Părintele a fost adăugat cu succes');
    }

    /**
     * Display the specified guardian
     */
    public function show(Guardian $guardian)
    {
        $user = Auth::user();
        $location = $user->location;
        
        if (!$location || $guardian->location_id !== $location->id) {
            // Pentru STAFF, redirecționăm la scan sau la copil dacă există parametrul from_child
            if ($user->isStaff()) {
                $fromChildId = request()->query('from_child');
                if ($fromChildId) {
                    return redirect()->route('children.show', $fromChildId)->with('error', 'Părintele nu a fost găsit');
                }
                return redirect()->route('scan')->with('error', 'Părintele nu a fost găsit');
            }
            return redirect()->route('guardians.index')->with('error', 'Părintele nu a fost găsit');
        }

        // Load children
        $guardian->load(['children' => function($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        // Load play sessions for all children of this guardian
        $childIds = $guardian->children->pluck('id');
        $playSessions = PlaySession::whereIn('child_id', $childIds)
            ->with(['child', 'intervals'])
            ->orderBy('started_at', 'desc')
            ->get()
            ->map(function ($session) {
                $isActive = is_null($session->ended_at);
                
                if ($isActive) {
                    // Active session: use closed intervals only
                    $effectiveSeconds = $session->getClosedIntervalsDurationSeconds();
                    $isPaused = $session->isPaused();
                    $currentIntervalStartedAt = null;
                    
                    if (!$isPaused) {
                        $openInterval = $session->intervals()->whereNull('ended_at')->latest('started_at')->first();
                        $currentIntervalStartedAt = $openInterval && $openInterval->started_at 
                            ? $openInterval->started_at->toISOString() 
                            : null;
                    }
                    
                    // Calculate estimated price for active session
                    $price = $session->calculatePrice();
                    
                    return [
                        'id' => $session->id,
                        'child_id' => $session->child_id,
                        'child_name' => $session->child ? $session->child->name : '-',
                        'started_at' => $session->started_at,
                        'ended_at' => null,
                        'bracelet_code' => $session->bracelet_code ?? null,
                        'effective_seconds' => $effectiveSeconds,
                        'is_paused' => $isPaused,
                        'current_interval_started_at' => $currentIntervalStartedAt,
                        'is_active' => true,
                        'status' => 'active',
                        'price' => $price,
                        'formatted_price' => $session->getFormattedPrice(),
                    ];
                } else {
                    // Closed session: use all intervals
                    $effectiveSeconds = $session->getEffectiveDurationSeconds();
                    
                    // Use calculated price if available, otherwise calculate
                    $price = $session->calculated_price ?? $session->calculatePrice();
                    
                    return [
                        'id' => $session->id,
                        'child_id' => $session->child_id,
                        'child_name' => $session->child ? $session->child->name : '-',
                        'started_at' => $session->started_at,
                        'ended_at' => $session->ended_at,
                        'bracelet_code' => $session->bracelet_code ?? null,
                        'effective_seconds' => $effectiveSeconds,
                        'is_paused' => false,
                        'current_interval_started_at' => null,
                        'is_active' => false,
                        'status' => 'completed',
                        'price' => $price,
                        'formatted_price' => $session->getFormattedPrice(),
                    ];
                }
            });

        // Calculate total price
        $totalPrice = $playSessions->sum('price');

        return view('guardians.show', compact('guardian', 'playSessions', 'totalPrice'));
    }

    /**
     * Show the form for editing the specified guardian
     */
    public function edit(Guardian $guardian)
    {
        // STAFF nu are acces la editarea de părinți
        if (Auth::user()->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        $user = Auth::user();
        $location = $user->location;
        
        if (!$location || $guardian->location_id !== $location->id) {
            return redirect()->route('guardians.index')->with('error', 'Părintele nu a fost găsit');
        }

        return view('guardians.edit', compact('guardian'));
    }

    /**
     * Update the specified guardian
     */
    public function update(Request $request, Guardian $guardian)
    {
        // STAFF nu are acces la actualizarea de părinți
        if (Auth::user()->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        $user = Auth::user();
        $location = $user->location;
        
        if (!$location || $guardian->location_id !== $location->id) {
            return redirect()->route('guardians.index')->with('error', 'Părintele nu a fost găsit');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $dataBefore = [
            'name' => $guardian->name,
            'phone' => $guardian->phone,
            'notes' => $guardian->notes,
        ];

        $guardian->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'notes' => $request->notes,
        ]);

        $dataAfter = [
            'name' => $guardian->name,
            'phone' => $guardian->phone,
            'notes' => $guardian->notes,
        ];

        ActionLogger::logAudit('updated', 'Guardian', $guardian->id, $dataBefore, $dataAfter);

        return redirect()->route('guardians.index')->with('success', 'Părintele a fost actualizat cu succes');
    }

    /**
     * Remove the specified guardian
     */
    public function destroy(Guardian $guardian)
    {
        // STAFF nu are acces la ștergerea de părinți
        if (Auth::user()->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        $user = Auth::user();
        $location = $user->location;
        
        if (!$location || $guardian->location_id !== $location->id) {
            return redirect()->route('guardians.index')->with('error', 'Părintele nu a fost găsit');
        }

        // Check if guardian has children
        if ($guardian->children()->count() > 0) {
            return redirect()->route('guardians.index')->with('error', 'Nu se poate șterge părintele - are copii înregistrați');
        }

        $guardianData = [
            'name' => $guardian->name,
        ];

        $guardian->delete();

        ActionLogger::logCrud('deleted', 'Guardian', $guardian->id, $guardianData);

        return redirect()->route('guardians.index')->with('success', 'Părintele a fost șters cu succes');
    }

    /**
     * Server-side search for guardians (AJAX)
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $location = $user->location;
        if (!$location) {
            return response()->json(['success' => false, 'message' => 'Locație lipsă'], 400);
        }

        $request->validate([
            'q' => 'nullable|string|max:255',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $q = (string) $request->input('q', '');
        $limit = (int) ($request->input('limit', 20));

        $results = Guardian::where('location_id', $location->id)
            ->when($q !== '', function ($query) use ($q) {
                $like = "%" . str_replace(['%','_'], ['\\%','\\_'], $q) . "%";
                $query->where(function ($inner) use ($like) {
                    $inner->where('name', 'LIKE', $like)
                          ->orWhere('phone', 'LIKE', $like);
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get(['id','name','phone']);

        return response()->json([
            'success' => true,
            'guardians' => $results,
        ]);
    }

    /**
     * Server-side data for guardians table (pagination/search/sort)
     */
    public function data(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->location) {
            return response()->json([
                'success' => false,
                'message' => 'Neautentificat sau fără locație'
            ], 401);
        }

        $locationId = $user->location->id;

        // Inputs
        $page = max(1, (int) $request->input('page', 1));
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }
        $search = trim((string) $request->input('search', ''));
        $sortBy = (string) $request->input('sort_by', 'name');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Base query
        $query = Guardian::where('location_id', $locationId)
            ->withCount('children')
            ->when($search !== '', function ($q) use ($search) {
                $like = "%" . str_replace(['%','_'], ['\\%','\\_'], $search) . "%";
                $q->where(function ($inner) use ($like) {
                    $inner->where('name', 'LIKE', $like)
                          ->orWhere('phone', 'LIKE', $like)
                          ->orWhere('notes', 'LIKE', $like);
                });
            });

        // Sort
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $sortDir);
                break;
            case 'phone':
                $query->orderBy('phone', $sortDir);
                break;
            case 'children_count':
                $query->orderBy('children_count', $sortDir);
                break;
            case 'created_at':
                $query->orderBy('created_at', $sortDir);
                break;
            default:
                $query->orderBy('name', $sortDir);
                break;
        }

        $total = $query->count();
        $rows = $query->skip(($page - 1) * $perPage)
                      ->take($perPage)
                      ->get();

        $dataRows = $rows->map(function ($g) {
            return [
                'id' => $g->id,
                'name' => $g->name,
                'phone' => $g->phone,
                'notes' => $g->notes,
                'children_count' => $g->children_count,
                'created_at' => $g->created_at->format('d.m.Y'),
            ];
        });

        $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 1;

        return response()->json([
            'success' => true,
            'data' => $dataRows,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
        ]);
    }
}
