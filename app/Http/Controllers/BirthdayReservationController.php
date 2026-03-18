<?php

namespace App\Http\Controllers;

use App\Models\BirthdayReservation;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

    public function dashboard(Request $request)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isCompanyAdmin()) {
            abort(403, 'Acces interzis');
        }

        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();

        $query = BirthdayReservation::with(['location', 'birthdayHall', 'birthdayPackage', 'timeSlot'])
            ->whereDate('reservation_date', $date);

        if ($user->isCompanyAdmin() && $user->company_id) {
            $query->whereHas('location', fn ($q) => $q->where('company_id', $user->company_id));
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        $reservations = $query->orderBy('reservation_time')->get();

        $byHall = $reservations->groupBy(fn ($r) => $r->birthdayHall?->name ?? 'Fără sală');

        $locations = $user->isSuperAdmin()
            ? Location::orderBy('name')->get()
            : Location::where('company_id', $user->company_id)->orderBy('name')->get();

        // Timeline — calculat din rezervările zilei (pending + confirmed, cu oră)
        $defaultStart = 9 * 60;
        $defaultEnd   = 21 * 60;

        $timelineByHall = $reservations
            ->whereIn('status', ['pending', 'confirmed'])
            ->filter(fn ($r) => !empty($r->reservation_time))
            ->groupBy(fn ($r) => $r->birthdayHall?->name ?? 'Fără sală')
            ->map(function ($hallReservations) use ($defaultStart, $defaultEnd) {
                $intervals = $hallReservations->map(function ($r) {
                    $startM   = Carbon::parse($r->reservation_time)->hour * 60 + Carbon::parse($r->reservation_time)->minute;
                    $duration = $r->birthdayPackage?->duration_minutes ?? 120;
                    $endM     = $startM + $duration;
                    return [
                        'start_m'    => $startM,
                        'end_m'      => $endM,
                        'start_lbl'  => sprintf('%02d:%02d', intdiv($startM, 60), $startM % 60),
                        'end_lbl'    => sprintf('%02d:%02d', intdiv($endM, 60), $endM % 60),
                        'child_name' => $r->child_name,
                        'status'     => $r->status,
                    ];
                })->sortBy('start_m')->values()->toArray();

                // Assign a stable color_index per reservation so the same child
                // always gets the same color across all columns it appears in.
                foreach ($intervals as $idx => &$iv) {
                    $iv['color_index'] = $idx;
                }
                unset($iv);

                $dayStart = min($defaultStart, collect($intervals)->min('start_m') ?? $defaultStart);
                $dayEnd   = max($defaultEnd,   collect($intervals)->max('end_m')   ?? $defaultEnd);
                $span     = $dayEnd - $dayStart;

                if ($span <= 0) {
                    return [
                        'columns'   => [['type' => 'free', 'pct' => 100]],
                        'day_start' => sprintf('%02d:%02d', intdiv($dayStart, 60), $dayStart % 60),
                        'day_end'   => sprintf('%02d:%02d', intdiv($dayEnd, 60), $dayEnd % 60),
                    ];
                }

                $points = array_unique(array_merge(
                    [$dayStart, $dayEnd],
                    array_column($intervals, 'start_m'),
                    array_column($intervals, 'end_m')
                ));
                sort($points);

                $columns = [];
                for ($i = 0; $i < count($points) - 1; $i++) {
                    $colStart = $points[$i];
                    $colEnd   = $points[$i + 1];
                    $pct      = ($colEnd - $colStart) / $span * 100;

                    $active = array_values(array_filter(
                        $intervals,
                        fn ($iv) => $iv['start_m'] <= $colStart && $iv['end_m'] >= $colEnd
                    ));

                    if (empty($active)) {
                        $columns[] = ['type' => 'free', 'pct' => $pct];
                    } else {
                        // Mark is_start so the blade only shows the name in the
                        // first column of each reservation.
                        $bands = array_map(function ($band) use ($colStart) {
                            $band['is_start'] = ($band['start_m'] === $colStart);
                            return $band;
                        }, $active);
                        $columns[] = ['type' => 'occupied', 'pct' => $pct, 'bands' => $bands];
                    }
                }

                return [
                    'columns'   => $columns,
                    'day_start' => sprintf('%02d:%02d', intdiv($dayStart, 60), $dayStart % 60),
                    'day_end'   => sprintf('%02d:%02d', intdiv($dayEnd, 60), $dayEnd % 60),
                ];
            });

        return view('birthday-reservations.dashboard', compact('reservations', 'byHall', 'date', 'locations', 'timelineByHall'));
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
