<?php

namespace App\Listeners;

use App\Events\SubscriptionActivated;
use App\Mail\SubscriptionActivatedMail;
use App\Mail\SubscriptionPaymentConfirmationMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyAdminOnSubscriptionActivated implements ShouldQueue
{
    public function handle(SubscriptionActivated $event): void
    {
        $subscription = $event->subscription;

        // 1. Notificare SUPER_ADMIN
        try {
            $adminEmail = config('mail.from.address', 'contact@hopo.ro');
            Mail::to($adminEmail)->send(new SubscriptionActivatedMail($subscription, $event->source));
        } catch (\Throwable $e) {
            Log::error('NotifyAdminOnSubscriptionActivated: failed to notify super admin', [
                'subscription_id' => $subscription->id,
                'error'           => $e->getMessage(),
            ]);
        }

        // 2. Confirmare plată spre company admini — doar pentru plăți Stripe
        if ($event->source !== 'stripe') {
            return;
        }

        try {
            $subscription->load('location');
            $companyId = $subscription->location->company_id;

            $companyAdmins = User::whereHas('role', fn($q) => $q->where('name', 'COMPANY_ADMIN'))
                ->where('company_id', $companyId)
                ->where('status', 'active')
                ->whereNotNull('email')
                ->get();

            foreach ($companyAdmins as $admin) {
                Mail::to($admin->email)->send(new SubscriptionPaymentConfirmationMail($subscription));
            }
        } catch (\Throwable $e) {
            Log::error('NotifyAdminOnSubscriptionActivated: failed to notify company admins', [
                'subscription_id' => $subscription->id,
                'error'           => $e->getMessage(),
            ]);
        }
    }
}
