<?php

namespace App\Http\Controllers;

use App\Models\BirthdayHall;
use App\Models\Location;
use Illuminate\Http\Request;

class BirthdayHallController extends Controller
{
    public function index(Location $location)
    {
        $this->authorize('view', $location);
        $halls = $location->birthdayHalls()->withCount(['timeSlots', 'birthdayReservations'])->orderBy('name')->get();
        return view('locations.birthday-halls.index', compact('location', 'halls'));
    }

    public function create(Location $location)
    {
        $this->authorize('update', $location);
        return view('locations.birthday-halls.create', compact('location'));
    }

    public function store(Request $request, Location $location)
    {
        $this->authorize('update', $location);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:birthday_halls,name,NULL,id,location_id,' . $location->id,
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'booking_mode' => 'required|in:slots,flexible',
        ]);
        $validated['location_id'] = $location->id;
        $validated['is_active'] = $request->boolean('is_active', true);
        BirthdayHall::create($validated);
        return redirect()
            ->route('locations.birthday-halls.index', $location)
            ->with('success', 'Sala a fost adăugată.');
    }

    public function edit(Location $location, BirthdayHall $birthdayHall)
    {
        $this->authorize('update', $location);
        return view('locations.birthday-halls.edit', compact('location', 'birthdayHall'));
    }

    public function update(Request $request, Location $location, BirthdayHall $birthdayHall)
    {
        $this->authorize('update', $birthdayHall->location);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:birthday_halls,name,' . $birthdayHall->id . ',id,location_id,' . $birthdayHall->location_id,
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'booking_mode' => 'required|in:slots,flexible',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $birthdayHall->update($validated);
        return redirect()
            ->route('locations.birthday-halls.index', $birthdayHall->location)
            ->with('success', 'Sala a fost actualizată.');
    }

    public function destroy(Location $location, BirthdayHall $birthdayHall)
    {
        $this->authorize('update', $location);
        if ($birthdayHall->timeSlots()->exists() || $birthdayHall->birthdayReservations()->exists()) {
            return redirect()
                ->route('locations.birthday-halls.index', $birthdayHall->location)
                ->with('error', 'Nu se poate șterge sala deoarece are slot-uri sau rezervări asociate.');
        }
        $birthdayHall->delete();
        return redirect()
            ->route('locations.birthday-halls.index', $birthdayHall->location)
            ->with('success', 'Sala a fost ștearsă.');
    }
}
