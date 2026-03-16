<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGatewayInterface;
use App\Http\Requests\CreateCheckoutSessionRequest;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function __construct(private PaymentGatewayInterface $gateway)
    {
    }

    public function plans(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->isCompanyAdmin()) {
            abort(403, 'Acces interzis.');
        }

        $company = app('current.company');

        // Dacă compania are planuri specifice configurate, le arătăm doar pe alea
        // Altfel fallback la toate planurile active
        if ($company && $company->subscriptionPlans()->exists()) {
            $plans = $company->subscriptionPlans()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        } else {
            $plans = SubscriptionPlan::active()->orderBy('sort_order')->get();
        }

        return view('checkout.plans', compact('plans'));
    }

    public function createSession(CreateCheckoutSessionRequest $request)
    {
        $plan = SubscriptionPlan::where('id', $request->validated('plan_id'))
            ->where('is_active', true)
            ->firstOrFail();

        $location = app('current.location');

        if (!$location) {
            return redirect()->route('checkout.plans')
                ->withErrors(['Locația curentă nu a putut fi determinată.']);
        }

        $url = $this->gateway->createCheckoutSession($plan, $location, Auth::user());

        return redirect()->away($url);
    }

    public function success(Request $request)
    {
        return view('checkout.success');
    }
}
