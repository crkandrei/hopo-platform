<?php

namespace App\Http\Controllers;

use App\Models\BirthdayReservation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class BirthdayReservationActionController extends Controller
{
    public function __invoke(string $token, string $action): View|RedirectResponse
    {
        $reservation = BirthdayReservation::where('token', $token)
            ->with(['location', 'birthdayHall', 'birthdayPackage'])
            ->firstOrFail();

        $alreadyProcessed = $reservation->status !== 'pending';
        $newStatus = $action === 'confirm' ? 'confirmed' : 'cancelled';

        if (!$alreadyProcessed) {
            $reservation->update(['status' => $newStatus]);
        }

        return view('birthday-reservations.action-result', [
            'reservation' => $reservation,
            'action'      => $action,
            'processed'   => !$alreadyProcessed,
        ]);
    }
}
