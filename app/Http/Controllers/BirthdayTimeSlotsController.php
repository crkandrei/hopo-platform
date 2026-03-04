<?php

namespace App\Http\Controllers;

use App\Models\BirthdayHall;
use App\Models\BirthdayTimeSlot;
use Illuminate\Http\Request;

class BirthdayTimeSlotsController extends Controller
{
    public function index(BirthdayHall $birthdayHall)
    {
        $this->authorize('view', $birthdayHall->location);
        $slots = $birthdayHall->timeSlots()->orderBy('day_of_week')->orderBy('start_time')->get();
        $location = $birthdayHall->location;
        return view('birthday-halls.time-slots.index', compact('birthdayHall', 'slots', 'location'));
    }

    public function store(Request $request, BirthdayHall $birthdayHall)
    {
        $this->authorize('update', $birthdayHall->location);
        $validated = $request->validate([
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_reservations' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);
        $validated['birthday_hall_id'] = $birthdayHall->id;
        $validated['is_active'] = $request->boolean('is_active', true);
        BirthdayTimeSlot::create($validated);
        return redirect()
            ->route('birthday-halls.time-slots.index', $birthdayHall)
            ->with('success', 'Slot-ul a fost adăugat.');
    }

    public function edit(BirthdayHall $birthdayHall, BirthdayTimeSlot $timeSlot)
    {
        $this->authorize('view', $birthdayHall->location);
        $slots = $birthdayHall->timeSlots()->orderBy('day_of_week')->orderBy('start_time')->get();
        $location = $birthdayHall->location;
        return view('birthday-halls.time-slots.edit', compact('birthdayHall', 'timeSlot', 'location'));
    }

    public function update(Request $request, BirthdayTimeSlot $timeSlot)
    {
        $this->authorize('update', $timeSlot->birthdayHall->location);
        $validated = $request->validate([
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_reservations' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $timeSlot->update($validated);
        return redirect()
            ->route('birthday-halls.time-slots.index', $timeSlot->birthdayHall)
            ->with('success', 'Slot-ul a fost actualizat.');
    }

    public function destroy(BirthdayTimeSlot $timeSlot)
    {
        $this->authorize('update', $timeSlot->birthdayHall->location);
        $hasFuture = $timeSlot->birthdayReservations()
            ->where('reservation_date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
        if ($hasFuture) {
            return redirect()
                ->route('birthday-halls.time-slots.index', $timeSlot->birthdayHall)
                ->with('error', 'Nu se poate șterge slot-ul deoarece are rezervări viitoare.');
        }
        $timeSlot->delete();
        return redirect()
            ->route('birthday-halls.time-slots.index', $timeSlot->birthdayHall)
            ->with('success', 'Slot-ul a fost șters.');
    }
}
