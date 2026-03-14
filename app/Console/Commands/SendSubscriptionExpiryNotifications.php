<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionExpiryMail;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionExpiryNotifications extends Command
{
    protected $signature = 'subscriptions:notify-expiring';
    protected $description = 'Trimite notificări email pentru abonamentele care expiră în curând';

    public function __construct(private SubscriptionService $subscriptionService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $expiring3 = $this->subscriptionService->getExpiringSoon(3);
        $expiring1 = $this->subscriptionService->getExpiringSoon(1);

        $dispatched = 0;

        foreach ($expiring3 as $subscription) {
            Mail::to(config('mail.from.address'))->queue(new SubscriptionExpiryMail($subscription, 3, 'superadmin'));
            $dispatched++;

            $companyAdmin = $subscription->location->company->users
                ->filter(fn ($user) => $user->role?->name === 'COMPANY_ADMIN' && filter_var($user->email, FILTER_VALIDATE_EMAIL))
                ->first();

            if ($companyAdmin) {
                Mail::to($companyAdmin->email)->queue(new SubscriptionExpiryMail($subscription, 3, 'company_admin'));
                $dispatched++;
            }
        }

        foreach ($expiring1 as $subscription) {
            Mail::to(config('mail.from.address'))->queue(new SubscriptionExpiryMail($subscription, 1, 'superadmin'));
            $dispatched++;

            $companyAdmin = $subscription->location->company->users
                ->filter(fn ($user) => $user->role?->name === 'COMPANY_ADMIN' && filter_var($user->email, FILTER_VALIDATE_EMAIL))
                ->first();

            if ($companyAdmin) {
                Mail::to($companyAdmin->email)->queue(new SubscriptionExpiryMail($subscription, 1, 'company_admin'));
                $dispatched++;
            }
        }

        $this->info("Dispatch-uite {$dispatched} emailuri de notificare abonament.");

        return Command::SUCCESS;
    }
}
