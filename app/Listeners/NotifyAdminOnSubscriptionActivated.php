<?php

namespace App\Listeners;

use App\Events\SubscriptionActivated;
use App\Mail\SubscriptionActivatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyAdminOnSubscriptionActivated implements ShouldQueue
{
    public function handle(SubscriptionActivated $event): void
    {
        $adminEmail = config('mail.from.address', 'contact@hopo.ro');

        try {
            Mail::to($adminEmail)->send(new SubscriptionActivatedMail(
                $event->subscription,
                $event->source,
            ));
        } catch (\Throwable $e) {
            Log::error('NotifyAdminOnSubscriptionActivated: failed to send email', [
                'subscription_id' => $event->subscription->id,
                'error'           => $e->getMessage(),
            ]);
        }
    }
}
