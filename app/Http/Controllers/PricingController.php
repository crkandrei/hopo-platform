<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\PricingTier;
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
            if ($locationId) {
                $selectedLocation = Location::with(['weeklyRates', 'specialPeriodRates', 'pricingTiers'])->find($locationId);
            }
        } elseif ($user->isCompanyAdmin() && $user->company_id) {
            $locations = Location::where('company_id', $user->company_id)->orderBy('name')->get();
            if ($locationId) {
                $selectedLocation = Location::with(['weeklyRates', 'specialPeriodRates', 'pricingTiers'])->findOrFail($locationId);
            } elseif ($locations->count() > 0) {
                $selectedLocation = Location::with(['weeklyRates', 'specialPeriodRates', 'pricingTiers'])->find($locations->first()->id);
            }
        } elseif ($user->isStaff() && $user->location_id) {
            $selectedLocation = Location::with(['weeklyRates', 'specialPeriodRates', 'pricingTiers'])->findOrFail($user->location_id);
        }

        $weeklyRatesByDay = [];
        $tieredGrid = [];
        $durations = [1, 2, 3, 4];
        if ($selectedLocation) {
            foreach ($selectedLocation->weeklyRates as $rate) {
                $weeklyRatesByDay[$rate->day_of_week] = $rate->hourly_rate;
            }
            foreach (range(0, 6) as $day) {
                $tieredGrid[$day] = [];
                foreach ($durations as $dur) {
                    $tier = $selectedLocation->pricingTiers->first(function ($t) use ($day, $dur) {
                        return $t->day_of_week === $day && (float) $t->duration_hours === (float) $dur;
                    });
                    $tieredGrid[$day][$dur] = $tier ? (float) $tier->price : null;
                }
            }
        }

        return view('pricing.index', [
            'locations' => $locations,
            'selectedLocation' => $selectedLocation,
            'weeklyRatesByDay' => $weeklyRatesByDay,
            'tieredGrid' => $tieredGrid ?? [],
            'durations' => $durations,
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
     * Update pricing mode (flat_hourly / tiered)
     */
    public function updatePricingMode(Request $request)
    {
        $this->checkPricingAccess();

        $locationId = $this->getLocationIdForUser($request->location_id);
        if (!$locationId) {
            return redirect()->route('pricing.index')
                ->with('error', 'Locație invalidă');
        }

        $request->validate([
            'pricing_mode' => 'required|in:flat_hourly,tiered',
        ]);

        $location = Location::findOrFail($locationId);
        $this->ensureLocationAccess($location->id);

        $location->update(['pricing_mode' => $request->pricing_mode]);

        $redirectParams = [];
        if (Auth::user()->isSuperAdmin()) {
            $redirectParams['location_id'] = $location->id;
        }

        return redirect()->route('pricing.index', $redirectParams)
            ->with('success', 'Modul de tarifare a fost actualizat');
    }

    /**
     * Update tiered rates (grid: day_of_week x duration_hours => price)
     */
    public function updateTieredRates(Request $request)
    {
        $this->checkPricingAccess();

        $locationId = $this->getLocationIdForUser($request->location_id);
        if (!$locationId) {
            return redirect()->route('pricing.index')
                ->with('error', 'Locație invalidă');
        }

        $location = Location::findOrFail($locationId);
        $this->ensureLocationAccess($location->id);

        $request->validate([
            'overflow_price_per_hour' => 'nullable|numeric|min:0',
            'tiers' => 'array',
            'tiers.*' => 'array',
            'tiers.*.*' => 'nullable|numeric|min:0',
        ]);

        $overflow = $request->overflow_price_per_hour !== null && $request->overflow_price_per_hour !== ''
            ? (float) $request->overflow_price_per_hour
            : null;
        $location->update(['overflow_price_per_hour' => $overflow]);

        $durations = $request->input('durations', [1, 2, 3, 4]);
        if (!is_array($durations)) {
            $durations = [1, 2, 3, 4];
        }

        DB::transaction(function () use ($location, $request, $durations) {
            $tiers = $request->input('tiers', []);
            foreach (range(0, 6) as $day) {
                $dayTiers = $tiers[$day] ?? [];
                foreach ($durations as $dur) {
                    $dur = (float) $dur;
                    $price = isset($dayTiers[$dur]) && $dayTiers[$dur] !== '' && $dayTiers[$dur] !== null
                        ? (float) $dayTiers[$dur]
                        : null;
                    if ($price !== null && $price >= 0) {
                        PricingTier::updateOrCreate(
                            [
                                'location_id' => $location->id,
                                'day_of_week' => $day,
                                'duration_hours' => $dur,
                            ],
                            ['price' => $price]
                        );
                    } else {
                        PricingTier::where('location_id', $location->id)
                            ->where('day_of_week', $day)
                            ->where('duration_hours', $dur)
                            ->delete();
                    }
                }
            }
        });

        $redirectParams = [];
        if (Auth::user()->isSuperAdmin()) {
            $redirectParams['location_id'] = $location->id;
        }

        return redirect()->route('pricing.index', $redirectParams)
            ->with('success', 'Tarifele pe durate au fost actualizate');
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
            'pricing_mode' => 'required|in:flat_hourly,tiered',
            'hourly_rate' => 'required_unless:pricing_mode,tiered|nullable|numeric|min:0',
            'price_1h' => 'nullable|numeric|min:0',
            'price_2h' => 'nullable|numeric|min:0',
            'price_3h' => 'nullable|numeric|min:0',
            'price_4h' => 'nullable|numeric|min:0',
            'overflow_price_per_hour' => 'nullable|numeric|min:0',
        ]);
        if ($request->pricing_mode === 'tiered') {
            $hasTier = $request->filled('price_1h') || $request->filled('price_2h') || $request->filled('price_3h') || $request->filled('price_4h');
            if (!$hasTier) {
                return back()->withInput()->with('error', 'Pentru tarifare pe durate setați cel puțin un preț (1h, 2h, 3h sau 4h).');
            }
        }

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

        $hourlyRate = $request->pricing_mode === 'tiered' ? 0 : ($request->hourly_rate ?? 0);
        $data = [
            'location_id' => $locationId,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'hourly_rate' => $hourlyRate,
            'pricing_mode' => $request->pricing_mode,
            'price_1h' => $request->price_1h !== '' && $request->price_1h !== null ? $request->price_1h : null,
            'price_2h' => $request->price_2h !== '' && $request->price_2h !== null ? $request->price_2h : null,
            'price_3h' => $request->price_3h !== '' && $request->price_3h !== null ? $request->price_3h : null,
            'price_4h' => $request->price_4h !== '' && $request->price_4h !== null ? $request->price_4h : null,
            'overflow_price_per_hour' => $request->overflow_price_per_hour !== '' && $request->overflow_price_per_hour !== null ? $request->overflow_price_per_hour : null,
        ];

        try {
            SpecialPeriodRate::create($data);

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
            'pricing_mode' => 'required|in:flat_hourly,tiered',
            'hourly_rate' => 'required_unless:pricing_mode,tiered|nullable|numeric|min:0',
            'price_1h' => 'nullable|numeric|min:0',
            'price_2h' => 'nullable|numeric|min:0',
            'price_3h' => 'nullable|numeric|min:0',
            'price_4h' => 'nullable|numeric|min:0',
            'overflow_price_per_hour' => 'nullable|numeric|min:0',
        ]);
        if ($request->pricing_mode === 'tiered') {
            $hasTier = $request->filled('price_1h') || $request->filled('price_2h') || $request->filled('price_3h') || $request->filled('price_4h');
            if (!$hasTier) {
                return back()->withInput()->with('error', 'Pentru tarifare pe durate setați cel puțin un preț (1h, 2h, 3h sau 4h).');
            }
        }

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

        $hourlyRate = $request->pricing_mode === 'tiered' ? 0 : ($request->hourly_rate ?? 0);
        $updateData = [
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'hourly_rate' => $hourlyRate,
            'pricing_mode' => $request->pricing_mode,
            'price_1h' => $request->price_1h !== '' && $request->price_1h !== null ? $request->price_1h : null,
            'price_2h' => $request->price_2h !== '' && $request->price_2h !== null ? $request->price_2h : null,
            'price_3h' => $request->price_3h !== '' && $request->price_3h !== null ? $request->price_3h : null,
            'price_4h' => $request->price_4h !== '' && $request->price_4h !== null ? $request->price_4h : null,
            'overflow_price_per_hour' => $request->overflow_price_per_hour !== '' && $request->overflow_price_per_hour !== null ? $request->overflow_price_per_hour : null,
        ];

        try {
            $specialPeriod->update($updateData);

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

