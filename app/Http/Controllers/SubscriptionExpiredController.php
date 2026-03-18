<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Auth;

class SubscriptionExpiredController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService)
    {
    }

    public function show()
    {
        $user = Auth::user();
        $location = app('current.location');
        $subscription = null;
        $eligibleLocations = [];

        if ($location) {
            $status = $this->subscriptionService->getStatus($location);
            if ($status === 'active' || $status === 'grace') {
                return redirect()->route('dashboard');
            }
            $subscription = $this->subscriptionService->getActiveSubscription($location);
        }

        if ($user->isCompanyAdmin() && $user->company_id) {
            $locations = Location::where('company_id', $user->company_id)
                ->where('is_active', true)
                ->get();

            $eligibleLocations = $locations->filter(function ($loc) {
                $status = $this->subscriptionService->getStatus($loc);
                return in_array($status, ['active', 'grace']);
            })->values();
        }

        return view('subscription.blocked', compact('location', 'subscription', 'eligibleLocations'));
    }
}
