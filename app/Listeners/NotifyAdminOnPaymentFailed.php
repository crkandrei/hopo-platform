<?php

namespace App\Listeners;

use App\Events\SubscriptionPaymentFailed;
use App\Mail\PaymentFailedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyAdminOnPaymentFailed implements ShouldQueue
{
    public function handle(SubscriptionPaymentFailed $event): void
    {
        $adminEmail = config('mail.from.address', 'contact@hopo.ro');

        try {
            Mail::to($adminEmail)->send(new PaymentFailedMail(
                $event->location,
                $event->plan,
                $event->stripeEventId,
            ));
        } catch (\Throwable $e) {
            Log::error('NotifyAdminOnPaymentFailed: failed to send email', [
                'location_id'    => $event->location->id,
                'stripe_event'   => $event->stripeEventId,
                'error'          => $e->getMessage(),
            ]);
        }
    }
}
