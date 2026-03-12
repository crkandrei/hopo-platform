<?php

namespace App\Events;

use App\Models\BirthdayReservation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BirthdayReservationCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly BirthdayReservation $reservation) {}
}
