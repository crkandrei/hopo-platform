<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $locations = Location::with('company')->orderBy('name')->get();
        } elseif ($user->isCompanyAdmin() && $user->company_id) {
            $locations = Location::where('company_id', $user->company_id)
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
            'bridge_config' => 'nullable|array',
        ]);
        
        // Set company_id for COMPANY_ADMIN
        if ($user->isCompanyAdmin() && $user->company_id) {
            $validated['company_id'] = $user->company_id;
        }
        
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $validated['is_active'] ?? true;
        
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
        
        return view('locations.edit', compact('location', 'companies'));
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
            'bridge_config' => 'nullable|array',
        ]);
        
        // COMPANY_ADMIN cannot change company_id
        if ($user->isCompanyAdmin() && $user->company_id) {
            $validated['company_id'] = $location->company_id;
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
        
        // Check if location has associated data
        $hasSessions = $location->playSessions()->exists();
        $hasChildren = $location->children()->exists();
        $hasUsers = $location->users()->exists();
        
        if ($hasSessions || $hasChildren || $hasUsers) {
            return redirect()->route('locations.index')
                ->with('error', 'Nu se poate șterge locația deoarece are date asociate');
        }
        
        $location->delete();
        
        return redirect()->route('locations.index')
            ->with('success', 'Locația a fost ștearsă cu succes');
    }
}
