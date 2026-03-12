<?php

namespace App\Http\Controllers;

use App\Models\BirthdayPackage;
use App\Models\Location;
use Illuminate\Http\Request;

class BirthdayPackageController extends Controller
{
    public function index(Location $location)
    {
        $this->authorize('view', $location);
        $packages = $location->birthdayPackages()
            ->with('birthdayPackageWeekdays')
            ->orderBy('name')
            ->get();
        return view('locations.birthday-packages.index', compact('location', 'packages'));
    }

    public function create(Location $location)
    {
        $this->authorize('update', $location);
        return view('locations.birthday-packages.create', compact('location'));
    }

    public function store(Request $request, Location $location)
    {
        $this->authorize('update', $location);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:15',
            'available_weekdays' => 'required|array|min:1',
            'available_weekdays.*' => 'integer|between:0,6|distinct',
            'includes_food' => 'boolean',
            'includes_decorations' => 'boolean',
            'is_active' => 'boolean',
        ]);
        $validated['location_id'] = $location->id;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['includes_food'] = $request->boolean('includes_food', false);
        $validated['includes_decorations'] = $request->boolean('includes_decorations', false);
        $birthdayPackage = BirthdayPackage::create($validated);
        $this->syncBirthdayPackageWeekdays($birthdayPackage, $validated['available_weekdays']);
        return redirect()
            ->route('locations.birthday-packages.index', $location)
            ->with('success', 'Pachetul a fost adăugat.');
    }

    public function edit(Location $location, BirthdayPackage $birthdayPackage)
    {
        $this->authorize('update', $location);
        $birthdayPackage->load('birthdayPackageWeekdays');
        return view('locations.birthday-packages.edit', compact('location', 'birthdayPackage'));
    }

    public function update(Request $request, Location $location, BirthdayPackage $birthdayPackage)
    {
        $this->authorize('update', $birthdayPackage->location);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:15',
            'available_weekdays' => 'required|array|min:1',
            'available_weekdays.*' => 'integer|between:0,6|distinct',
            'includes_food' => 'boolean',
            'includes_decorations' => 'boolean',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['includes_food'] = $request->boolean('includes_food', false);
        $validated['includes_decorations'] = $request->boolean('includes_decorations', false);
        $birthdayPackage->update($validated);
        $this->syncBirthdayPackageWeekdays($birthdayPackage, $validated['available_weekdays']);
        return redirect()
            ->route('locations.birthday-packages.index', $location)
            ->with('success', 'Pachetul a fost actualizat.');
    }

    public function destroy(Location $location, BirthdayPackage $birthdayPackage)
    {
        $this->authorize('update', $location);
        if ($birthdayPackage->birthdayReservations()->exists()) {
            return redirect()
                ->route('locations.birthday-packages.index', $location)
                ->with('error', 'Nu se poate șterge pachetul deoarece are rezervări asociate.');
        }
        $birthdayPackage->delete();
        return redirect()
            ->route('locations.birthday-packages.index', $location)
            ->with('success', 'Pachetul a fost șters.');
    }

    private function syncBirthdayPackageWeekdays(BirthdayPackage $birthdayPackage, array $availableWeekdays): void
    {
        $birthdayPackage->birthdayPackageWeekdays()->delete();

        $birthdayPackage->birthdayPackageWeekdays()->createMany(
            collect($availableWeekdays)
                ->map(fn (int|string $dayOfWeek) => ['day_of_week' => (int) $dayOfWeek])
                ->sortBy('day_of_week')
                ->values()
                ->all()
        );
    }
}
