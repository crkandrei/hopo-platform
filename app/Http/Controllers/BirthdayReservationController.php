<?php

namespace App\Http\Controllers;

use App\Models\BirthdayReservation;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BirthdayReservationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isCompanyAdmin()) {
            abort(403, 'Acces interzis');
        }

        $query = BirthdayReservation::with(['location', 'birthdayHall', 'birthdayPackage', 'timeSlot']);

        if ($user->isCompanyAdmin() && $user->company_id) {
            $query->whereHas('location', fn ($q) => $q->where('company_id', $user->company_id));
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        if ($request->filled('birthday_hall_id')) {
            $query->where('birthday_hall_id', $request->birthday_hall_id);
        }
        if ($request->filled('reservation_date')) {
            $query->whereDate('reservation_date', $request->reservation_date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reservations = $query->orderBy('reservation_date', 'desc')->orderBy('created_at', 'desc')->paginate(20);

        $locations = $user->isSuperAdmin()
            ? Location::orderBy('name')->get()
            : Location::where('company_id', $user->company_id)->orderBy('name')->get();

        return view('birthday-reservations.index', compact('reservations', 'locations'));
    }

    public function show(BirthdayReservation $birthdayReservation)
    {
        $this->authorize('view', $birthdayReservation->location);
        $birthdayReservation->load(['location', 'birthdayHall', 'birthdayPackage', 'timeSlot']);
        return view('birthday-reservations.show', compact('birthdayReservation'));
    }

    public function update(Request $request, BirthdayReservation $birthdayReservation)
    {
        $this->authorize('update', $birthdayReservation->location);
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
            'notes' => 'nullable|string',
        ]);
        $birthdayReservation->update($validated);
        return redirect()
            ->route('birthday-reservations.show', $birthdayReservation)
            ->with('success', 'Rezervarea a fost actualizată.');
    }

    public function destroy(BirthdayReservation $birthdayReservation)
    {
        $this->authorize('update', $birthdayReservation->location);
        $birthdayReservation->delete();
        return redirect()
            ->route('birthday-reservations.index')
            ->with('success', 'Rezervarea a fost ștearsă.');
    }
}
