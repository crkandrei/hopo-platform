<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\PaymentGatewayInterface;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubscriptionPlanController extends Controller
{
    public function __construct(private PaymentGatewayInterface $gateway)
    {
    }

    public function index()
    {
        $this->authorizeSuperAdmin();

        $plans = SubscriptionPlan::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.subscription-plans.index', compact('plans'));
    }

    public function create()
    {
        $this->authorizeSuperAdmin();

        return view('admin.subscription-plans.create');
    }

    public function store(Request $request)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'name'            => 'required|string|max:100',
            'price'           => 'required|numeric|min:0',
            'duration_months' => 'required|integer|min:1',
            'features'        => 'nullable|string',
            'sort_order'      => 'nullable|integer|min:0',
        ]);

        $plan = SubscriptionPlan::create([
            'name'            => $validated['name'],
            'slug'            => Str::slug($validated['name']),
            'price'           => $validated['price'],
            'duration_months' => $validated['duration_months'],
            'features'        => $this->parseFeatures($validated['features'] ?? null),
            'sort_order'      => $validated['sort_order'] ?? 0,
            'is_active'       => true,
        ]);

        try {
            $stripeIds = $this->gateway->createPlan($plan);
            $plan->update([
                'stripe_product_id' => $stripeIds['product_id'],
                'stripe_price_id'   => $stripeIds['price_id'],
            ]);
        } catch (\Throwable $e) {
            Log::error('SubscriptionPlanController: Stripe sync failed on create', [
                'plan_id' => $plan->id,
                'error'   => $e->getMessage(),
            ]);
            return redirect()->route('admin.subscription-plans.index')
                ->with('warning', 'Planul a fost creat local, dar sincronizarea cu Stripe a eșuat. Verificați log-urile pentru detalii.');
        }

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', "Planul «{$plan->name}» a fost creat și sincronizat cu Stripe.");
    }

    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        $this->authorizeSuperAdmin();

        return view('admin.subscription-plans.edit', ['plan' => $subscriptionPlan]);
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'name'            => 'required|string|max:100',
            'price'           => 'required|numeric|min:0',
            'duration_months' => 'required|integer|min:1',
            'features'        => 'nullable|string',
            'sort_order'      => 'nullable|integer|min:0',
        ]);

        $priceChanged = (float) $subscriptionPlan->price !== (float) $validated['price'];
        $nameChanged  = $subscriptionPlan->name !== $validated['name'];

        $subscriptionPlan->update([
            'name'            => $validated['name'],
            'price'           => $validated['price'],
            'duration_months' => $validated['duration_months'],
            'features'        => $this->parseFeatures($validated['features'] ?? null),
            'sort_order'      => $validated['sort_order'] ?? $subscriptionPlan->sort_order,
        ]);

        try {
            if ($priceChanged) {
                // Arhivează prețul vechi și creează produs+preț nou
                $this->gateway->archivePlan($subscriptionPlan);
                $stripeIds = $this->gateway->createPlan($subscriptionPlan->fresh());
                $subscriptionPlan->update([
                    'stripe_product_id' => $stripeIds['product_id'],
                    'stripe_price_id'   => $stripeIds['price_id'],
                ]);
            } elseif ($nameChanged) {
                // Actualizează doar numele produsului în Stripe
                $this->gateway->updatePlanName($subscriptionPlan->fresh());
            }
        } catch (\Throwable $e) {
            Log::error('SubscriptionPlanController: Stripe sync failed on update', [
                'plan_id' => $subscriptionPlan->id,
                'error'   => $e->getMessage(),
            ]);
            return redirect()->route('admin.subscription-plans.index')
                ->with('warning', 'Planul a fost actualizat local, dar sincronizarea cu Stripe a eșuat. Verificați log-urile pentru detalii.');
        }

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', "Planul «{$subscriptionPlan->name}» a fost actualizat.");
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        $this->authorizeSuperAdmin();

        if ($subscriptionPlan->subscriptions()->exists()) {
            $subscriptionPlan->update(['is_active' => false]);

            try {
                $this->gateway->archivePlan($subscriptionPlan);
            } catch (\Throwable $e) {
                Log::error('SubscriptionPlanController: Stripe archive failed on destroy', [
                    'plan_id' => $subscriptionPlan->id,
                    'error'   => $e->getMessage(),
                ]);
            }

            return redirect()->route('admin.subscription-plans.index')
                ->with('success', "Planul «{$subscriptionPlan->name}» a fost dezactivat (are abonamente existente).");
        }

        try {
            $this->gateway->archivePlan($subscriptionPlan);
        } catch (\Throwable $e) {
            Log::warning('SubscriptionPlanController: Stripe archive failed (continuing with delete)', [
                'plan_id' => $subscriptionPlan->id,
                'error'   => $e->getMessage(),
            ]);
        }

        $name = $subscriptionPlan->name;
        $subscriptionPlan->delete();

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', "Planul «{$name}» a fost șters.");
    }

    public function resync(SubscriptionPlan $subscriptionPlan)
    {
        $this->authorizeSuperAdmin();

        try {
            // Archive old Stripe plan if it exists (ignore errors — it may not exist in Stripe)
            try {
                $this->gateway->archivePlan($subscriptionPlan);
            } catch (\Throwable) {
            }

            $stripeIds = $this->gateway->createPlan($subscriptionPlan);
            $subscriptionPlan->update([
                'stripe_product_id' => $stripeIds['product_id'],
                'stripe_price_id'   => $stripeIds['price_id'],
            ]);
        } catch (\Throwable $e) {
            Log::error('SubscriptionPlanController: Stripe resync failed', [
                'plan_id' => $subscriptionPlan->id,
                'error'   => $e->getMessage(),
            ]);
            return redirect()->route('admin.subscription-plans.index')
                ->with('warning', "Re-sincronizarea cu Stripe a eșuat: {$e->getMessage()}");
        }

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', "Planul «{$subscriptionPlan->name}» a fost re-sincronizat cu Stripe.");
    }

    private function authorizeSuperAdmin(): void
    {
        if (!Auth::user()?->isSuperAdmin()) {
            abort(403, 'Acces interzis.');
        }
    }

    private function parseFeatures(?string $input): ?array
    {
        if (!$input) {
            return null;
        }

        return array_values(array_filter(
            array_map('trim', explode("\n", $input))
        ));
    }
}
