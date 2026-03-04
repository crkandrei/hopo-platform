<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    public function create()
    {
        $this->authorize('create', Company::class);
        return view('onboarding.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Company::class);

        $validated = $request->validate([
            // Step 1 — Company
            'company_name'  => 'required|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:50',
            // Step 2 — Location
            'location_name'     => 'required|string|max:255',
            'location_address'  => 'nullable|string|max:255',
            'location_phone'    => 'nullable|string|max:50',
            'location_email'    => 'nullable|email|max:255',
            'price_per_hour'    => 'required|numeric|min:0',
            'bracelet_required' => 'boolean',
            'fiscal_enabled'    => 'boolean',
            // Step 3 — Admin user
            'admin_name'     => 'required|string|max:255',
            'admin_username' => 'required|string|max:255|regex:/^[a-zA-Z0-9_]+$/|unique:users,username',
            'admin_email'    => 'required|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        DB::transaction(function () use ($validated) {
            // 1. Company
            $slug = $this->uniqueSlug($validated['company_name']);
            $company = Company::create([
                'name'      => $validated['company_name'],
                'email'     => $validated['company_email'] ?? null,
                'phone'     => $validated['company_phone'] ?? null,
                'slug'      => $slug,
                'is_active' => true,
            ]);

            // 2. Location
            $locSlug = $this->uniqueSlugForCompany($company->id, $validated['location_name']);
            Location::create([
                'company_id'        => $company->id,
                'name'              => $validated['location_name'],
                'slug'              => $locSlug,
                'address'           => $validated['location_address'] ?? null,
                'phone'             => $validated['location_phone'] ?? null,
                'email'             => $validated['location_email'] ?? null,
                'price_per_hour'    => $validated['price_per_hour'],
                'bracelet_required' => $validated['bracelet_required'] ?? false,
                'fiscal_enabled'    => $validated['fiscal_enabled'] ?? false,
                'is_active'         => true,
            ]);

            // 3. COMPANY_ADMIN user
            $role = Role::where('name', 'COMPANY_ADMIN')->firstOrFail();
            User::create([
                'name'       => $validated['admin_name'],
                'username'   => $validated['admin_username'],
                'email'      => $validated['admin_email'],
                'password'   => Hash::make($validated['admin_password']),
                'company_id' => $company->id,
                'role_id'    => $role->id,
                'status'     => 'active',
            ]);
        });

        return redirect()->route('companies.index')
            ->with('success', 'Clientul a fost creat cu succes!');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;
        while (Company::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }
        return $slug;
    }

    private function uniqueSlugForCompany(int $companyId, string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;
        while (Location::where('company_id', $companyId)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }
        return $slug;
    }
}
