<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Auth;

class CompanySubscriptionController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user->isCompanyAdmin()) {
            abort(403, 'Acces permis doar pentru administratori de companie.');
        }

        $location = app('current.location');

        if (!$location) {
            abort(404, 'Locația curentă nu a putut fi determinată.');
        }

        $subscription = $location->subscriptions()->with('plan')->latest('starts_at')->first();
        $status = app(SubscriptionService::class)->getStatus($location);

        return view('subscription.manage', compact('subscription', 'status', 'location'));
    }
}
