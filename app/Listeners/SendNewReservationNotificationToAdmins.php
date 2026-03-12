<?php

namespace App\Listeners;

use App\Events\BirthdayReservationCreated;
use App\Mail\NewBirthdayReservationMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNewReservationNotificationToAdmins implements ShouldQueue
{
    public int $tries = 3;

    public array $backoff = [30, 60];

    public function handle(BirthdayReservationCreated $event): void
    {
        $reservation = $event->reservation;
        $reservation->loadMissing('location');

        $companyId = $reservation->location?->company_id;

        if (!$companyId) {
            Log::warning('BirthdayReservation notification: location has no company_id', [
                'reservation_id' => $reservation->id,
                'location_id'    => $reservation->location_id,
            ]);
            return;
        }

        $admins = User::where('company_id', $companyId)
            ->where('status', 'active')
            ->whereHas('role', fn ($q) => $q->where('name', 'COMPANY_ADMIN'))
            ->get();

        if ($admins->isEmpty()) {
            Log::warning('BirthdayReservation notification: no active COMPANY_ADMIN found for company', [
                'company_id'     => $companyId,
                'reservation_id' => $reservation->id,
            ]);
            return;
        }

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new NewBirthdayReservationMail($reservation));
        }
    }
}
