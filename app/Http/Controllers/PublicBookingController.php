<?php

namespace App\Http\Controllers;

use App\Models\BirthdayHall;
use App\Models\BirthdayPackage;
use App\Models\BirthdayReservation;
use App\Models\BirthdayTimeSlot;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PublicBookingController extends Controller
{
    public function showForm(Location $location)
    {
        $location->load(['birthdayHalls' => fn ($q) => $q->where('is_active', true), 'birthdayPackages' => fn ($q) => $q->where('is_active', true)]);
        $halls = $location->birthdayHalls;
        $packages = $location->birthdayPackages;

        if ($halls->isEmpty() || $packages->isEmpty()) {
            abort(404, 'Rezervările pentru zile de naștere nu sunt configurate pentru această locație.');
        }

        return view('booking.show', [
            'location' => $location,
            'halls' => $halls,
            'packages' => $packages,
        ]);
    }

    public function getAvailableSlots(Request $request, Location $location)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'birthday_hall_id' => [
                'required',
                Rule::exists('birthday_halls', 'id')->where('location_id', $location->id)->where('is_active', true),
            ],
        ]);
        $hallId = (int) $request->birthday_hall_id;
        $hall = BirthdayHall::where('location_id', $location->id)
            ->where('is_active', true)
            ->find($hallId);

        if (!$hall || $hall->booking_mode === BirthdayHall::BOOKING_MODE_FLEXIBLE) {
            return response()->json(['slots' => []]);
        }

        $date = Carbon::parse($request->date);

        $allSlots = \App\Models\BirthdayTimeSlot::where('birthday_hall_id', $hallId)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        $result = $allSlots->map(function ($slot) use ($date) {
            if (!$slot->isAvailableOn($date)) {
                return null;
            }
            $count = BirthdayReservation::where('time_slot_id', $slot->id)
                ->whereDate('reservation_date', $date)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count();
            $available = $count < $slot->max_reservations;

            return [
                'id'         => $slot->id,
                'start_time' => Carbon::parse($slot->start_time)->format('H:i'),
                'end_time'   => Carbon::parse($slot->end_time)->format('H:i'),
                'label'      => Carbon::parse($slot->start_time)->format('H:i') . ' – ' . Carbon::parse($slot->end_time)->format('H:i'),
                'available'  => $available,
                'reason'     => $available ? null : 'Ocupat',
            ];
        })->filter()->values();

        return response()->json(['slots' => $result]);
    }

    /**
     * Availability by day: operating range + occupied intervals.
     * Flow: select hall, date, package → show this → user picks start time → reserve from start for package duration.
     */
    public function getAvailability(Request $request, Location $location)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'birthday_hall_id' => [
                'required',
                Rule::exists('birthday_halls', 'id')->where('location_id', $location->id)->where('is_active', true),
            ],
            'birthday_package_id' => [
                'required',
                Rule::exists('birthday_packages', 'id')->where('location_id', $location->id)->where('is_active', true),
            ],
        ]);

        $date = Carbon::parse($request->date);
        $hallId = (int) $request->birthday_hall_id;
        $packageId = (int) $request->birthday_package_id;
        $package = BirthdayPackage::where('location_id', $location->id)->where('is_active', true)->findOrFail($packageId);
        $durationMinutes = (int) $package->duration_minutes;

        $slotsForDay = BirthdayTimeSlot::where('birthday_hall_id', $hallId)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get()
            ->filter(fn ($slot) => $slot->isAvailableOn($date));

        $dayStart = '09:00';
        $dayEnd = '21:00';
        if ($slotsForDay->isNotEmpty()) {
            $dayStart = Carbon::parse($slotsForDay->min('start_time'))->format('H:i');
            $dayEnd = Carbon::parse($slotsForDay->max('end_time'))->format('H:i');
        }

        $reservations = BirthdayReservation::where('birthday_hall_id', $hallId)
            ->whereDate('reservation_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['timeSlot', 'birthdayPackage'])
            ->get();

        $occupied = [];
        foreach ($reservations as $r) {
            if ($r->time_slot_id && $r->timeSlot) {
                $start = Carbon::parse($r->timeSlot->start_time)->format('H:i');
                $end = Carbon::parse($r->timeSlot->end_time)->format('H:i');
            } else {
                $start = Carbon::parse($r->reservation_time)->format('H:i');
                $end = Carbon::parse($r->reservation_time)->addMinutes($r->birthdayPackage->duration_minutes ?? 120)->format('H:i');
            }
            $occupied[] = ['start' => $start, 'end' => $end];
        }
        $occupied = $this->mergeIntervals($occupied);

        return response()->json([
            'day_start' => $dayStart,
            'day_end' => $dayEnd,
            'duration_minutes' => $durationMinutes,
            'occupied' => $occupied,
        ]);
    }

    /** Merge overlapping [start,end] intervals (strings H:i). */
    private function mergeIntervals(array $intervals): array
    {
        if (empty($intervals)) {
            return [];
        }
        $toMinutes = fn ($t) => \Carbon\Carbon::parse($t)->format('H') * 60 + (int) \Carbon\Carbon::parse($t)->format('i');
        $toTime = fn ($m) => sprintf('%02d:%02d', (int) floor($m / 60), $m % 60);
        $pairs = array_map(fn ($i) => [$toMinutes($i['start']), $toMinutes($i['end'])], $intervals);
        usort($pairs, fn ($a, $b) => $a[0] <=> $b[0]);
        $merged = [];
        foreach ($pairs as [$s, $e]) {
            if (! empty($merged) && $s <= $merged[count($merged) - 1][1]) {
                $merged[count($merged) - 1][1] = max($merged[count($merged) - 1][1], $e);
            } else {
                $merged[] = [$s, $e];
            }
        }
        return array_map(fn ($m) => ['start' => $toTime($m[0]), 'end' => $toTime($m[1])], $merged);
    }

    public function submitForm(Request $request, Location $location)
    {
        $hallId = $request->input('birthday_hall_id');
        $hall = BirthdayHall::where('id', $hallId)
            ->where('location_id', $location->id)
            ->where('is_active', true)
            ->firstOrFail();

        $rules = [
            'child_name' => 'required|string|max:255',
            'child_age' => 'nullable|integer|min:0|max:18',
            'guardian_name' => 'required|string|max:255',
            'guardian_phone' => 'required|string|max:50',
            'guardian_email' => 'nullable|email|max:255',
            'birthday_hall_id' => [
                'required',
                Rule::exists('birthday_halls', 'id')->where('location_id', $location->id)->where('is_active', true),
            ],
            'birthday_package_id' => 'required|exists:birthday_packages,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required|date_format:H:i',
            'number_of_children' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
            'gdpr_accept' => 'required|accepted',
        ];

        $validated = $request->validate($rules);

        $package = BirthdayPackage::where('location_id', $location->id)->findOrFail($validated['birthday_package_id']);
        if (! $package->is_active) {
            return back()->withInput()->withErrors(['birthday_package_id' => 'Pachet invalid.']);
        }

        $numChildren = (int) $validated['number_of_children'];
        if ($numChildren > $hall->capacity) {
            throw ValidationException::withMessages([
                'number_of_children' => ['Numărul de copii nu poate depăși capacitatea sălii (' . $hall->capacity . ' copii).'],
            ]);
        }
        if ($numChildren > $package->max_children) {
            throw ValidationException::withMessages([
                'number_of_children' => ['Numărul de copii nu poate depăși limita pachetului selectat (' . $package->max_children . ' copii).'],
            ]);
        }

        $date = Carbon::parse($validated['reservation_date']);
        $startM = Carbon::parse($validated['reservation_time'])->format('H') * 60 + (int) Carbon::parse($validated['reservation_time'])->format('i');
        $endM = $startM + (int) $package->duration_minutes;
        $reservations = BirthdayReservation::where('birthday_hall_id', $hall->id)
            ->whereDate('reservation_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['timeSlot', 'birthdayPackage'])
            ->get();
        $occupied = [];
        foreach ($reservations as $r) {
            if ($r->time_slot_id && $r->timeSlot) {
                $s = Carbon::parse($r->timeSlot->start_time)->format('H') * 60 + (int) Carbon::parse($r->timeSlot->start_time)->format('i');
                $e = Carbon::parse($r->timeSlot->end_time)->format('H') * 60 + (int) Carbon::parse($r->timeSlot->end_time)->format('i');
            } else {
                $s = Carbon::parse($r->reservation_time)->format('H') * 60 + (int) Carbon::parse($r->reservation_time)->format('i');
                $e = $s + (int) $r->birthdayPackage->duration_minutes;
            }
            $occupied[] = [$s, $e];
        }
        foreach ($occupied as [$s, $e]) {
            if ($startM < $e && $endM > $s) {
                throw ValidationException::withMessages([
                    'reservation_time' => ['Intervalul ales se suprapune cu o rezervare existentă. Alegeți altă oră.'],
                ]);
            }
        }

        $totalPrice = $package->price;

        return DB::transaction(function () use ($location, $validated, $hall, $package, $totalPrice) {
            $reservation = BirthdayReservation::create([
                'location_id' => $location->id,
                'birthday_hall_id' => $validated['birthday_hall_id'],
                'birthday_package_id' => $validated['birthday_package_id'],
                'time_slot_id' => null,
                'reservation_date' => $validated['reservation_date'],
                'reservation_time' => $validated['reservation_time'],
                'child_name' => $validated['child_name'],
                'child_age' => $validated['child_age'] ?? null,
                'guardian_name' => $validated['guardian_name'],
                'guardian_phone' => $validated['guardian_phone'],
                'guardian_email' => $validated['guardian_email'] ?? null,
                'number_of_children' => $validated['number_of_children'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'total_price' => $totalPrice,
            ]);

            return redirect()->route('booking.confirmation', [
                'location' => $location,
                'token' => $reservation->token,
            ]);
        });
    }

    public function confirmation(Location $location, Request $request)
    {
        $token = $request->query('token');
        if (!$token) {
            return redirect()->route('booking.show', $location)->with('error', 'Link invalid.');
        }
        $reservation = BirthdayReservation::where('location_id', $location->id)
            ->where('token', $token)
            ->firstOrFail();
        $reservation->load(['birthdayHall', 'birthdayPackage', 'timeSlot']);
        return view('booking.confirmation', [
            'location' => $location,
            'reservation' => $reservation,
        ]);
    }
}
