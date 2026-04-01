<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocationController extends Controller
{
    /**
     * Display a listing of locations
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isSuperAdmin()) {
            $locations = Location::with('company', 'bridge')->orderBy('name')->get();
        } elseif ($user->isCompanyAdmin() && $user->company_id) {
            $locations = Location::with('bridge')->where('company_id', $user->company_id)
                ->orderBy('name')
                ->get();
        } else {
            abort(403, 'Acces interzis');
        }
        
        return view('locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new location
     */
    public function create()
    {
        $user = Auth::user();
        
        $this->authorize('create', Location::class);
        
        $companies = null;
        if ($user->isSuperAdmin()) {
            $companies = Company::orderBy('name')->get();
        }
        
        return view('locations.create', compact('companies'));
    }

    /**
     * Store a newly created location
     */
    public function store(Request $request)
    {
        $this->authorize('create', Location::class);
        
        $user = Auth::user();
        
        $validated = $request->validate([
            'company_id' => $user->isSuperAdmin() ? 'required|exists:companies,id' : 'nullable',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'price_per_hour' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'bracelet_required' => 'boolean',
            'fiscal_enabled' => 'boolean',
            'birthday_concurrent_reservations' => 'boolean',
        ]);

        // Set company_id for COMPANY_ADMIN
        if ($user->isCompanyAdmin() && $user->company_id) {
            $validated['company_id'] = $user->company_id;
        }

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['bracelet_required'] = $request->boolean('bracelet_required', true);
        $validated['fiscal_enabled'] = $request->boolean('fiscal_enabled', true);
        $validated['birthday_concurrent_reservations'] = $request->boolean('birthday_concurrent_reservations', false);
        
        // Ensure unique slug within company
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (Location::where('company_id', $validated['company_id'])
            ->where('slug', $validated['slug'])
            ->exists()) {
            $validated['slug'] = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        $location = Location::create($validated);
        
        return redirect()->route('locations.index')
            ->with('success', 'Locația a fost creată cu succes');
    }

    /**
     * Display the specified location
     */
    public function show(Location $location)
    {
        $this->authorize('view', $location);

        // When SuperAdmin enters a location page, store it in session as active context
        if (Auth::user()->isSuperAdmin()) {
            session(['superadmin_selected_location_id' => $location->id]);
        }

        $location->load('company', 'users');

        return view('locations.show', compact('location'));
    }

    /**
     * Show the form for editing the specified location
     */
    public function edit(Location $location)
    {
        $this->authorize('update', $location);

        $user = Auth::user();
        $companies = null;
        if ($user->isSuperAdmin()) {
            $companies = Company::orderBy('name')->get();
        }

        $bridge = $location->bridge;
        $recentLogs = $bridge
            ? \App\Models\BridgeLog::where('location_id', $location->id)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
            : collect();

        return view('locations.edit', compact('location', 'companies', 'bridge', 'recentLogs'));
    }

    /**
     * Update the specified location
     */
    public function update(Request $request, Location $location)
    {
        $this->authorize('update', $location);
        
        $user = Auth::user();
        
        $validated = $request->validate([
            'company_id' => $user->isSuperAdmin() ? 'required|exists:companies,id' : 'nullable',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'price_per_hour' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'bracelet_required' => 'boolean',
            'fiscal_enabled' => 'boolean',
            'birthday_concurrent_reservations' => 'boolean',
            'pre_checkin_enabled' => 'boolean',
            'rules_url' => 'nullable|url|max:500',
            'rules_document' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'remove_rules_document' => 'nullable|boolean',
        ]);

        // COMPANY_ADMIN cannot change company_id
        if ($user->isCompanyAdmin() && $user->company_id) {
            $validated['company_id'] = $location->company_id;
        }

        // Explicitly handle checkboxes (unchecked = absent from request = false)
        $validated['is_active'] = $request->boolean('is_active');
        $validated['bracelet_required'] = $request->boolean('bracelet_required');
        $validated['fiscal_enabled'] = $request->boolean('fiscal_enabled');
        $validated['birthday_concurrent_reservations'] = $request->boolean('birthday_concurrent_reservations');
        $validated['pre_checkin_enabled'] = $request->boolean('pre_checkin_enabled');

        // Handle rules document upload
        $rulesFile = $validated['rules_document'] ?? null;
        unset($validated['rules_document'], $validated['remove_rules_document']);

        if ($rulesFile) {
            if ($location->rules_document_path) {
                Storage::disk('public')->delete($location->rules_document_path);
            }
            $ext = $rulesFile->getClientOriginalExtension() ?: 'pdf';
            $validated['rules_document_path'] = $rulesFile->storeAs(
                "locations/{$location->id}",
                "regulament.{$ext}",
                'public'
            );
        } elseif ($request->boolean('remove_rules_document') && $location->rules_document_path) {
            Storage::disk('public')->delete($location->rules_document_path);
            $validated['rules_document_path'] = null;
        }

        // Update slug if name changed
        if ($location->name !== $validated['name']) {
            $newSlug = Str::slug($validated['name']);
            $baseSlug = $newSlug;
            $counter = 1;
            while (Location::where('company_id', $validated['company_id'])
                ->where('slug', $newSlug)
                ->where('id', '!=', $location->id)
                ->exists()) {
                $newSlug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $newSlug;
        }

        $location->update($validated);

        return redirect()->route('locations.index')
            ->with('success', 'Locația a fost actualizată cu succes');
    }

    /**
     * Remove the specified location
     */
    public function destroy(Location $location)
    {
        $this->authorize('delete', $location);

        $checks = [
            'users' => 'Utilizatori',
            'guardians' => 'Tutori',
            'children' => 'Copii',
            'products' => 'Produse',
            'playSessions' => 'Sesiuni de joc',
            'weeklyRates' => 'Tarife săptămânale',
            'specialPeriodRates' => 'Tarife perioade speciale',
            'pricingTiers' => 'Tarife pe durate',
            'birthdayHalls' => 'Săli zile de naștere',
            'birthdayPackages' => 'Pachete zile de naștere',
            'birthdayReservations' => 'Rezervări zile de naștere',
        ];

        $entitiesWithData = [];
        foreach ($checks as $relation => $label) {
            if ($location->{$relation}()->exists()) {
                $entitiesWithData[] = $label;
            }
        }

        if (!empty($entitiesWithData)) {
            $entitiesList = implode(', ', $entitiesWithData);
            return redirect()->route('locations.index')
                ->with('error', 'Nu se poate șterge locația deoarece are date asociate: ' . $entitiesList . '. Eliminați sau mutați aceste date înainte de ștergere.');
        }

        $location->delete();

        return redirect()->route('locations.index')
            ->with('success', 'Locația a fost ștearsă cu succes');
    }
}
