<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies (SUPER_ADMIN only)
     */
    public function index()
    {
        $this->authorize('viewAny', Company::class);
        
        $companies = Company::with('locations')->orderBy('name')->get();
        
        return view('companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new company
     */
    public function create()
    {
        $this->authorize('create', Company::class);
        
        return view('companies.create');
    }

    /**
     * Store a newly created company
     */
    public function store(Request $request)
    {
        $this->authorize('create', Company::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);
        
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $validated['is_active'] ?? true;
        
        // Ensure unique slug
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (Company::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        $company = Company::create($validated);
        
        return redirect()->route('companies.index')
            ->with('success', 'Compania a fost creată cu succes');
    }

    /**
     * Display the specified company
     */
    public function show(Company $company)
    {
        $this->authorize('view', $company);
        
        $company->load('locations', 'users');
        
        return view('companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified company
     */
    public function edit(Company $company)
    {
        $this->authorize('update', $company);
        
        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified company
     */
    public function update(Request $request, Company $company)
    {
        $this->authorize('update', $company);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);
        
        // Update slug if name changed
        if ($company->name !== $validated['name']) {
            $newSlug = Str::slug($validated['name']);
            $baseSlug = $newSlug;
            $counter = 1;
            while (Company::where('slug', $newSlug)->where('id', '!=', $company->id)->exists()) {
                $newSlug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $newSlug;
        }
        
        $company->update($validated);
        
        return redirect()->route('companies.index')
            ->with('success', 'Compania a fost actualizată cu succes');
    }

    /**
     * Remove the specified company
     */
    public function destroy(Company $company)
    {
        $this->authorize('delete', $company);
        
        // Check if company has locations
        if ($company->locations()->count() > 0) {
            return redirect()->route('companies.index')
                ->with('error', 'Nu se poate șterge compania deoarece are locații asociate');
        }
        
        $company->delete();
        
        return redirect()->route('companies.index')
            ->with('success', 'Compania a fost ștearsă cu succes');
    }
}
