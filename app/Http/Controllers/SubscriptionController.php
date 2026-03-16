<?php

namespace App\Http\Controllers;

use App\Events\SubscriptionActivated;
use App\Models\Location;
use App\Models\LocationSubscription;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService)
    {
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Acces interzis.');
        }

        $allLocations = Location::with('company')->orderBy('name')->get();

        $locationIds = $allLocations->pluck('id');

        // Bulk-load the latest subscription per location in a single query.
        $now = now();
        $latestSubscriptions = LocationSubscription::whereIn('location_id', $locationIds)
            ->where('starts_at', '<=', $now)
            ->orderByDesc('starts_at')
            ->get()
            ->unique('location_id')
            ->keyBy('location_id');

        $locations = $allLocations->map(function ($location) use ($latestSubscriptions, $now) {
            $subscription = $latestSubscriptions->get($location->id);

            if ($subscription === null) {
                $status = 'none';
                $expiresAt = null;
                $daysRemaining = null;
            } elseif ($subscription->expires_at >= $now) {
                $status = 'active';
                $expiresAt = $subscription->expires_at;
                $daysRemaining = (int) $now->diffInDays($subscription->expires_at, false);
            } elseif ($subscription->expires_at >= $now->copy()->subDays(7)) {
                $status = 'grace';
                $expiresAt = $subscription->expires_at;
                $daysRemaining = (int) $now->diffInDays($subscription->expires_at, false);
            } else {
                $status = 'expired';
                $expiresAt = $subscription->expires_at;
                $daysRemaining = (int) $now->diffInDays($subscription->expires_at, false);
            }

            return [
                'location'       => $location,
                'subscription'   => $subscription,
                'status'         => $status,
                'expires_at'     => $expiresAt,
                'days_remaining' => $daysRemaining,
                'payment_source' => $subscription?->payment_source,
            ];
        });

        $statusFilter = null;
        $allowedStatuses = ['active', 'grace', 'expired', 'none'];
        if (in_array($request->query('status'), $allowedStatuses)) {
            $statusFilter = $request->query('status');
            $locations = $locations->filter(fn($item) => $item['status'] === $statusFilter)->values();
        }

        return view('subscriptions.index', compact('locations', 'statusFilter'));
    }

    public function create(Location $location)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Acces interzis.');
        }

        $location->load('company');
        $plans = SubscriptionPlan::active()->orderBy('sort_order')->get();

        return view('subscriptions.create', compact('location', 'plans'));
    }

    public function store(Request $request, Location $location)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Acces interzis.');
        }

        $validated = $request->validate([
            'plan_id'        => 'required|exists:subscription_plans,id',
            'starts_at'      => 'required|date',
            'expires_at'     => 'required|date|after:starts_at',
            'payment_method' => 'nullable|in:bank_transfer,cash,card,other',
            'notes'          => 'nullable|string',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        $expiresAt = Carbon::parse($validated['expires_at'])->setTime(2, 0, 0);

        $subscription = LocationSubscription::create([
            'location_id'    => $location->id,
            'plan_id'        => $plan->id,
            'plan_type'      => 'standard',
            'starts_at'      => $validated['starts_at'],
            'expires_at'     => $expiresAt,
            'price_paid'     => $plan->price,
            'payment_method' => $validated['payment_method'] ?? null,
            'payment_source' => 'manual',
            'notes'          => $validated['notes'] ?? null,
            'created_by'     => Auth::id(),
        ]);

        SubscriptionActivated::dispatch($subscription, 'manual');

        return redirect()->route('admin.subscriptions.index')
            ->with('success', "Abonamentul pentru {$location->name} a fost activat cu succes.");
    }

    public function edit(LocationSubscription $subscription)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Acces interzis.');
        }

        $subscription->load('location.company');
        $plans = SubscriptionPlan::active()->orderBy('sort_order')->get();

        return view('subscriptions.edit', compact('subscription', 'plans'));
    }

    public function update(Request $request, LocationSubscription $subscription)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Acces interzis.');
        }

        $validated = $request->validate([
            'plan_id'        => 'required|exists:subscription_plans,id',
            'starts_at'      => 'required|date',
            'expires_at'     => 'required|date|after:starts_at',
            'payment_method' => 'nullable|in:bank_transfer,cash,card,other',
            'notes'          => 'nullable|string',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        $expiresAt = Carbon::parse($validated['expires_at'])->setTime(2, 0, 0);

        $subscription->update([
            'plan_id'        => $plan->id,
            'starts_at'      => $validated['starts_at'],
            'expires_at'     => $expiresAt,
            'price_paid'     => $plan->price,
            'payment_method' => $validated['payment_method'] ?? null,
            'notes'          => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.subscriptions.history', $subscription->location)
            ->with('success', 'Abonamentul a fost actualizat cu succes.');
    }

    public function suspend(LocationSubscription $subscription)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Acces interzis.');
        }

        // Set expires_at in the past to immediately expire (outside grace period)
        $subscription->update([
            'expires_at' => Carbon::now()->subDays(8)->setTime(2, 0, 0),
        ]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', "Abonamentul pentru {$subscription->location->name} a fost suspendat.");
    }

    public function history(Location $location)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Acces interzis.');
        }

        $location->load('company');

        $subscriptions = LocationSubscription::where('location_id', $location->id)
            ->with('createdBy')
            ->orderByDesc('starts_at')
            ->orderByDesc('created_at')
            ->get();

        $currentSubscription = $this->subscriptionService->getActiveSubscription($location);

        return view('subscriptions.history', compact('location', 'subscriptions', 'currentSubscription'));
    }
}
