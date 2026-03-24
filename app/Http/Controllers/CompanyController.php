<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SubscriptionPlan;
use App\Support\ActionLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
            'daily_report_enabled' => 'boolean',
        ]);

        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isCompanyAdmin()) {
            unset($validated['daily_report_enabled']);
        }

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

        $allPlans = SubscriptionPlan::active()->orderBy('sort_order')->get();
        $selectedPlanIds = $company->subscriptionPlans()->pluck('subscription_plan_id')->toArray();

        return view('companies.edit', compact('company', 'allPlans', 'selectedPlanIds'));
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
            'daily_report_enabled' => 'boolean',
            'subscription_plan_ids' => 'nullable|array',
            'subscription_plan_ids.*' => 'integer|exists:subscription_plans,id',
            'logo' => 'nullable|file|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isCompanyAdmin()) {
            unset($validated['daily_report_enabled']);
        }

        if (isset($validated['daily_report_enabled']) && $validated['daily_report_enabled'] !== $company->daily_report_enabled) {
            ActionLogger::log('daily_report_enabled_changed', 'Company', $company->id, [
                'from' => $company->daily_report_enabled,
                'to' => $validated['daily_report_enabled'],
            ]);
        }

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

        // Extract logo from validated before update
        $logoFile = $validated['logo'] ?? null;
        unset($validated['logo']);

        $company->update($validated);

        if ($logoFile) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $ext  = match ($logoFile->getMimeType()) {
                'image/png'  => 'png',
                'image/webp' => 'webp',
                default      => 'jpg',
            };
            $path = $logoFile->storeAs(
                "companies/{$company->id}",
                "logo.{$ext}",
                'public'
            );
            $company->logo_path = $path;
            $company->save();
        }

        // Sync planuri abonament (doar super admin)
        if (Auth::user()->isSuperAdmin()) {
            $company->subscriptionPlans()->sync($validated['subscription_plan_ids'] ?? []);
        }

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
