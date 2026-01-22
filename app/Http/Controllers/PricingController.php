<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\WeeklyRate;
use App\Models\SpecialPeriodRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PricingController extends Controller
{
    /**
     * Check if user has access to pricing management
     * SUPER_ADMIN can manage all tenants, COMPANY_ADMIN can manage only their own tenant
     */
    private function checkPricingAccess()
    {
        $user = Auth::user();
        if (!$user || (!$user->isSuperAdmin() && !$user->isCompanyAdmin())) {
            abort(403, 'Acces permis doar pentru super admin sau company admin');
        }
    }

    /**
     * Get the location ID for the current user
     * SUPER_ADMIN can select any location, COMPANY_ADMIN uses locations from their company, STAFF uses their location
     */
    private function getLocationIdForUser($requestLocationId = null)
    {
        $user = Auth::user();
        
        if ($user->isSuperAdmin()) {
            // Super admin can select any location
            return $requestLocationId;
        } elseif ($user->isCompanyAdmin() && $user->company_id) {
            // Company admin can use locations from their company
            if ($requestLocationId) {
                // Verify location belongs to company
                $location = Location::where('id', $requestLocationId)
                    ->where('company_id', $user->company_id)
                    ->first();
                return $location ? $location->id : null;
            }
            // Return first location from company if no specific location requested
            $firstLocation = Location::where('company_id', $user->company_id)->first();
            return $firstLocation ? $firstLocation->id : null;
        } elseif ($user->isStaff()) {
            // Staff can only use their own location
            return $user->location_id;
        }
        
        return null;
    }

    /**
     * Ensure user can only access their own location's pricing
     */
    private function ensureLocationAccess($locationId)
    {
        $user = Auth::user();
        
        if ($user->isSuperAdmin()) {
            // Super admin can access any location
            return;
        }
        
        if ($user->isCompanyAdmin()) {
            $location = Location::find($locationId);
            if (!$location || $location->company_id != $user->company_id) {
                abort(403, 'Nu aveți acces la această locație');
            }
            return;
        }
        
        if ($user->isStaff() && $user->location_id != $locationId) {
            abort(403, 'Nu aveți acces la această locație');
        }
    }

    /**
     * Display the pricing management page
     */
    public function index(Request $request)
    {
        $this->checkPricingAccess();

        $user = Auth::user();
        $locationId = $this->getLocationIdForUser($request->get('location_id'));

        // For SUPER_ADMIN: show location selector
        // For COMPANY_ADMIN: show locations from their company
        // For STAFF: automatically use their location
        $locations = null;
        $selectedLocation = null;

        if ($user->isSuperAdmin()) {
            $locations = Location::with('company')->orderBy('name')->get();
        } elseif ($user->isCompanyAdmin() && $user->company_id) {
            $locations = Location::where('company_id', $user->company_id)->orderBy('name')->get();
            if ($locationId) {
                $selectedLocation = Location::with(['weeklyRates', 'specialPeriodRates'])->findOrFail($locationId);
            } elseif ($locations->count() > 0) {
                $selectedLocation = Location::with(['weeklyRates', 'specialPeriodRates'])->find($locations->first()->id);
            }
        } elseif ($user->isStaff() && $user->location_id) {
            // Staff: automatically use their location
            $selectedLocation = Location::with(['weeklyRates', 'specialPeriodRates'])->findOrFail($user->location_id);
        }

        return view('pricing.index', [
            'locations' => $locations,
            'selectedLocation' => $selectedLocation,
            'isSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    /**
     * Show weekly rates form
     */
    public function showWeeklyRates(Request $request)
    {
        $this->checkPricingAccess();

        $locationId = $this->getLocationIdForUser($request->get('location_id'));
        if (!$locationId) {
            return redirect()->route('pricing.index')
                ->with('error', 'Selectați o locație');
        }

        $location = Location::with('weeklyRates')->findOrFail($locationId);
        $this->ensureLocationAccess($location->id);
        
        // Get existing rates indexed by day_of_week
        $weeklyRates = [];
        foreach ($location->weeklyRates as $rate) {
            $weeklyRates[$rate->day_of_week] = $rate->hourly_rate;
        }

        return view('pricing.weekly-rates', [
            'location' => $location,
            'weeklyRates' => $weeklyRates,
        ]);
    }

    /**
     * Update weekly rates
     */
    public function updateWeeklyRates(Request $request)
    {
        $this->checkPricingAccess();

        $locationId = $this->getLocationIdForUser($request->location_id);
        if (!$locationId) {
            return redirect()->route('pricing.index')
                ->with('error', 'Locație invalidă');
        }

        $request->validate([
            'rates' => 'required|array',
            'rates.*' => 'required|numeric|min:0',
        ]);

        $location = Location::findOrFail($locationId);
        $this->ensureLocationAccess($location->id);

        DB::transaction(function () use ($location, $request) {
            // Update or create rates for each day
            for ($day = 0; $day <= 6; $day++) {
                $rate = $request->rates[$day] ?? null;
                if ($rate !== null) {
                    WeeklyRate::updateOrCreate(
                        [
                            'location_id' => $location->id,
                            'day_of_week' => $day,
                        ],
                        [
                            'hourly_rate' => $rate,
                        ]
                    );
                } else {
                    // Remove rate if not provided
                    WeeklyRate::where('location_id', $location->id)
                        ->where('day_of_week', $day)
                        ->delete();
                }
            }
        });

        $redirectParams = [];
        if (Auth::user()->isSuperAdmin()) {
            $redirectParams['location_id'] = $location->id;
        }

        return redirect()->route('pricing.index', $redirectParams)
            ->with('success', 'Tarifele săptămânale au fost actualizate cu succes');
    }


    /**
     * List special periods
     */
    public function indexSpecialPeriods(Request $request)
    {
        $this->checkPricingAccess();

        $locationId = $this->getLocationIdForUser($request->get('location_id'));
        if (!$locationId) {
            return redirect()->route('pricing.index')
                ->with('error', 'Selectați o locație');
        }

        $location = Location::findOrFail($locationId);
        $this->ensureLocationAccess($location->id);
        
        $specialPeriods = SpecialPeriodRate::where('location_id', $locationId)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('pricing.special-periods', [
            'location' => $location,
            'specialPeriods' => $specialPeriods,
        ]);
    }

    /**
     * Store a new special period
     */
    public function storeSpecialPeriod(Request $request)
    {
        $this->checkPricingAccess();

        $locationId = $this->getLocationIdForUser($request->location_id);
        if (!$locationId) {
            return redirect()->route('pricing.index')
                ->with('error', 'Locație invalidă');
        }

        $this->ensureLocationAccess($locationId);

        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'hourly_rate' => 'required|numeric|min:0',
        ]);

        // Check for overlapping periods
        $overlap = $this->checkSpecialPeriodOverlap(
            $locationId,
            $request->start_date,
            $request->end_date
        );

        if ($overlap) {
            return back()->withInput()
                ->with('error', 'Există deja o perioadă specială care se suprapune cu perioada specificată');
        }

        try {
            SpecialPeriodRate::create([
                'location_id' => $locationId,
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'hourly_rate' => $request->hourly_rate,
            ]);

            $redirectParams = [];
            if (Auth::user()->isSuperAdmin()) {
                $redirectParams['location_id'] = $locationId;
            }

            return redirect()->route('pricing.special-periods', $redirectParams)
                ->with('success', 'Perioada specială a fost creată cu succes');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Eroare la crearea perioadei speciale: ' . $e->getMessage());
        }
    }

    /**
     * Update a special period
     */
    public function updateSpecialPeriod(Request $request, $id)
    {
        $this->checkPricingAccess();

        $specialPeriod = SpecialPeriodRate::findOrFail($id);
        $this->ensureLocationAccess($specialPeriod->location_id);

        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'hourly_rate' => 'required|numeric|min:0',
        ]);

        // Check for overlapping periods (excluding current one)
        $overlap = $this->checkSpecialPeriodOverlap(
            $specialPeriod->location_id,
            $request->start_date,
            $request->end_date,
            $id
        );

        if ($overlap) {
            return back()->withInput()
                ->with('error', 'Există deja o perioadă specială care se suprapune cu perioada specificată');
        }

        try {
            $specialPeriod->update([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'hourly_rate' => $request->hourly_rate,
            ]);

            $redirectParams = [];
            if (Auth::user()->isSuperAdmin()) {
                $redirectParams['location_id'] = $specialPeriod->location_id;
            }

            return redirect()->route('pricing.special-periods', $redirectParams)
                ->with('success', 'Perioada specială a fost actualizată cu succes');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Eroare la actualizarea perioadei speciale: ' . $e->getMessage());
        }
    }

    /**
     * Delete a special period
     */
    public function destroySpecialPeriod($id)
    {
        $this->checkPricingAccess();

        try {
            $specialPeriod = SpecialPeriodRate::findOrFail($id);
            $this->ensureLocationAccess($specialPeriod->location_id);
            
            $locationId = $specialPeriod->location_id;
            $specialPeriod->delete();

            $redirectParams = [];
            if (Auth::user()->isSuperAdmin()) {
                $redirectParams['location_id'] = $locationId;
            }

            return redirect()->route('pricing.special-periods', $redirectParams)
                ->with('success', 'Perioada specială a fost ștearsă cu succes');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Eroare la ștergerea perioadei speciale: ' . $e->getMessage());
        }
    }

    /**
     * Check if a date range overlaps with existing special periods
     * 
     * @param int $locationId
     * @param string $startDate
     * @param string $endDate
     * @param int|null $excludeId Exclude this ID from check (for updates)
     * @return bool True if overlap exists
     */
    private function checkSpecialPeriodOverlap($locationId, $startDate, $endDate, $excludeId = null)
    {
        $query = SpecialPeriodRate::where('location_id', $locationId)
            ->where(function ($q) use ($startDate, $endDate) {
                // Check if new period overlaps with existing periods
                // Overlap exists if:
                // - existing start_date is between new start_date and end_date, OR
                // - existing end_date is between new start_date and end_date, OR
                // - new period is completely within an existing period
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}

