<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Voucher;
use App\Policies\UserPolicy;
use App\Policies\VoucherPolicy;
use App\Repositories\Contracts\PlaySessionRepositoryInterface;
use App\Repositories\Eloquent\PlaySessionRepository;
use App\Repositories\Contracts\ChildRepositoryInterface;
use App\Repositories\Eloquent\ChildRepository;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Repositories\Eloquent\AuditLogRepository;
use App\Services\SubscriptionService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Voucher::class => VoucherPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind(PlaySessionRepositoryInterface::class, PlaySessionRepository::class);
        $this->app->bind(ChildRepositoryInterface::class, ChildRepository::class);
        $this->app->bind(AuditLogRepositoryInterface::class, AuditLogRepository::class);
        $this->app->singleton(SubscriptionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Queue::failing(function (JobFailed $event) {
            $adminEmail = config('mail.from.address', 'contact@hopo.ro');
            $jobName = get_class($event->job);
            $error = $event->exception->getMessage();

            Log::error('Queue job failed', ['job' => $jobName, 'error' => $error]);

            try {
                Mail::raw(
                    "Job esuat: {$jobName}\n\nEroare: {$error}",
                    function ($message) use ($adminEmail, $jobName) {
                        $message->to($adminEmail)
                                ->subject("[HOPO] Queue job esuat: {$jobName}");
                    }
                );
            } catch (\Throwable $e) {
                Log::error('Failed to send job failure notification', ['error' => $e->getMessage()]);
            }
        });
    }
}
