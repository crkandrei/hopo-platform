<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\Location;
use App\Models\PreCheckinToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicPreCheckinController extends Controller
{
    public function showIndex(Location $location)
    {
        abort_if(! $location->pre_checkin_enabled, 404);
        $location->loadMissing('company');

        return view('pre-checkin.index', compact('location'));
    }

    public function submitNew(Request $request, Location $location)
    {
        abort_if(! $location->pre_checkin_enabled, 404);

        // Honeypot: silent rejection if filled
        if ($request->filled('website')) {
            return redirect()->route('pre-checkin.index', $location);
        }

        // Rate limiting per phone: max 5 children / 24h for same number
        $phoneKey = 'pre-checkin-phone:' . $request->input('guardian_phone', '') . ':' . $location->id;
        if (RateLimiter::tooManyAttempts($phoneKey, 5)) {
            return back()->withInput()->withErrors([
                'guardian_phone' => 'Prea multe înregistrări de pe acest număr. Încercați mai târziu.',
            ]);
        }

        // Max 50 pending tokens per location
        $pendingCount = PreCheckinToken::where('location_id', $location->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->count();
        if ($pendingCount >= 50) {
            return back()->withInput()->withErrors([
                'guardian_first_name' => 'Sistemul este momentan ocupat. Vă rugăm să veniți la receptie.',
            ]);
        }

        $validated = $request->validate([
            'guardian_first_name' => 'required|string|max:100',
            'guardian_last_name' => 'required|string|max:100',
            'guardian_phone' => 'required|string|max:20',
            'child_name' => 'required|string|max:255',
            'terms_accept' => 'required|accepted',
            'gdpr_accept' => 'required|accepted',
        ]);

        RateLimiter::hit($phoneKey, 86400);

        $guardianName = strtoupper($validated['guardian_first_name'] . ' ' . $validated['guardian_last_name']);

        $token = DB::transaction(function () use ($validated, $location, $guardianName) {
            $guardian = Guardian::firstOrCreate(
                [
                    'phone' => $validated['guardian_phone'],
                    'location_id' => $location->id,
                ],
                [
                    'name' => $guardianName,
                    'terms_accepted_at' => now(),
                    'gdpr_accepted_at' => now(),
                    'terms_version' => LegalController::TERMS_VERSION,
                    'gdpr_version' => LegalController::GDPR_VERSION,
                ]
            );

            if (! $guardian->wasRecentlyCreated) {
                $guardian->update([
                    'terms_accepted_at' => now(),
                    'gdpr_accepted_at' => now(),
                    'terms_version' => LegalController::TERMS_VERSION,
                    'gdpr_version' => LegalController::GDPR_VERSION,
                ]);
            }

            $child = Child::firstOrCreate(
                [
                    'name' => strtoupper($validated['child_name']),
                    'guardian_id' => $guardian->id,
                    'location_id' => $location->id,
                ],
                [
                    'internal_code' => $this->generateInternalCode($validated['child_name']),
                ]
            );

            return PreCheckinToken::create([
                'token' => (string) Str::uuid(),
                'location_id' => $location->id,
                'child_id' => $child->id,
                'guardian_id' => $guardian->id,
                'status' => 'pending',
                'expires_at' => now()->addMinutes(60),
            ]);
        });

        return redirect()->route('pre-checkin.qr', [
            'location' => $location,
            'token' => $token->token,
        ]);
    }

    public function showQr(Location $location, string $token)
    {
        abort_if(! $location->pre_checkin_enabled, 404);

        $preCheckinToken = PreCheckinToken::where('token', $token)
            ->where('location_id', $location->id)
            ->firstOrFail();

        $location->loadMissing('company');

        return view('pre-checkin.qr', compact('location', 'preCheckinToken'));
    }

    public function checkPhone(Request $request, Location $location)
    {
        abort_if(! $location->pre_checkin_enabled, 404);

        $phone = $request->input('phone', '');
        if (! $phone) {
            return response()->json(['exists' => false]);
        }

        $exists = Guardian::where('phone', $phone)
            ->where('location_id', $location->id)
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    public function submitExisting(Request $request, Location $location)
    {
        abort_if(! $location->pre_checkin_enabled, 404);

        if ($request->filled('website')) {
            return redirect()->route('pre-checkin.index', $location);
        }

        $validated = $request->validate([
            'guardian_phone' => 'required|string|max:20',
        ]);

        $guardian = Guardian::where('phone', $validated['guardian_phone'])
            ->where('location_id', $location->id)
            ->first();

        if (! $guardian) {
            return back()->withInput()->withErrors([
                'guardian_phone' => 'Numărul nu există în sistem. Înregistrează-te ca client nou.',
            ]);
        }

        $needsTerms = $guardian->needsToAcceptTerms();
        $needsGdpr = $guardian->needsToAcceptGdpr();

        if ($needsTerms && ! $request->boolean('terms_accept')) {
            return back()->withInput()->withErrors([
                'terms_accept' => 'Trebuie să accepți termenii și condițiile.',
            ])->with('show_terms', true)->with('guardian_phone', $validated['guardian_phone']);
        }

        if ($needsGdpr && ! $request->boolean('gdpr_accept')) {
            return back()->withInput()->withErrors([
                'gdpr_accept' => 'Trebuie să accepți politica GDPR.',
            ])->with('show_terms', true)->with('guardian_phone', $validated['guardian_phone']);
        }

        if ($needsTerms || $needsGdpr) {
            $guardian->update([
                'terms_accepted_at' => now(),
                'gdpr_accepted_at' => now(),
                'terms_version' => LegalController::TERMS_VERSION,
                'gdpr_version' => LegalController::GDPR_VERSION,
            ]);
        }

        $children = Child::where('guardian_id', $guardian->id)
            ->where('location_id', $location->id)
            ->get();

        $location->loadMissing('company');

        return view('pre-checkin.existing', compact('location', 'guardian', 'children'));
    }

    public function generateExistingToken(Request $request, Location $location)
    {
        abort_if(! $location->pre_checkin_enabled, 404);

        $validated = $request->validate([
            'guardian_phone' => 'required|string|max:20',
            'child_id' => 'required|integer',
        ]);

        $guardian = Guardian::where('phone', $validated['guardian_phone'])
            ->where('location_id', $location->id)
            ->firstOrFail();

        $child = Child::where('id', $validated['child_id'])
            ->where('guardian_id', $guardian->id)
            ->where('location_id', $location->id)
            ->firstOrFail();

        PreCheckinToken::where('child_id', $child->id)
            ->where('status', 'pending')
            ->update(['status' => 'used', 'used_at' => now()]);

        $token = PreCheckinToken::create([
            'token' => (string) Str::uuid(),
            'location_id' => $location->id,
            'child_id' => $child->id,
            'guardian_id' => $guardian->id,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(60),
        ]);

        return response()->json(['token' => $token->token]);
    }

    public function lookupPhone(Request $request, Location $location)
    {
        abort_if(! $location->pre_checkin_enabled, 404);

        if ($request->filled('website')) {
            return redirect()->route('pre-checkin.index', $location);
        }

        $validated = $request->validate([
            'guardian_phone' => 'required|string|max:20',
        ]);

        $guardian = Guardian::where('phone', $validated['guardian_phone'])
            ->where('location_id', $location->id)
            ->first();

        if (! $guardian) {
            $location->loadMissing('company');

            return view('pre-checkin.index', [
                'location' => $location,
                'show_new_form' => true,
                'prefill_phone' => $validated['guardian_phone'],
            ]);
        }

        $needsTerms = $guardian->needsToAcceptTerms();
        $needsGdpr = $guardian->needsToAcceptGdpr();

        if ($needsTerms && ! $request->boolean('terms_accept')) {
            return back()->withInput()->withErrors([
                'terms_accept' => 'Trebuie să accepți termenii și condițiile.',
            ])->with('show_terms', true);
        }

        if ($needsGdpr && ! $request->boolean('gdpr_accept')) {
            return back()->withInput()->withErrors([
                'gdpr_accept' => 'Trebuie să accepți politica GDPR.',
            ])->with('show_terms', true);
        }

        if ($needsTerms || $needsGdpr) {
            $guardian->update([
                'terms_accepted_at' => now(),
                'gdpr_accepted_at' => now(),
                'terms_version' => LegalController::TERMS_VERSION,
                'gdpr_version' => LegalController::GDPR_VERSION,
            ]);
        }

        session(['pre_checkin_guardian_id' => $guardian->id]);

        return redirect()->route('pre-checkin.show-existing', $location);
    }

    public function showExistingPage(Request $request, Location $location)
    {
        abort_if(! $location->pre_checkin_enabled, 404);

        $guardianId = session('pre_checkin_guardian_id');
        if (! $guardianId) {
            return redirect()->route('pre-checkin.index', $location);
        }

        $guardian = Guardian::where('id', $guardianId)
            ->where('location_id', $location->id)
            ->firstOrFail();

        $children = Child::where('guardian_id', $guardian->id)
            ->where('location_id', $location->id)
            ->get();

        $location->loadMissing('company');

        return view('pre-checkin.existing', compact('location', 'guardian', 'children'));
    }

    public function addChildToExisting(Request $request, Location $location)
    {
        abort_if(! $location->pre_checkin_enabled, 404);

        $validated = $request->validate([
            'guardian_phone' => 'required|string|max:20',
            'child_name' => 'required|string|max:255',
            'terms_accept' => 'required|accepted',
            'gdpr_accept' => 'required|accepted',
        ]);

        $guardian = Guardian::where('phone', $validated['guardian_phone'])
            ->where('location_id', $location->id)
            ->firstOrFail();

        $phoneKey = 'pre-checkin-phone:' . $validated['guardian_phone'] . ':' . $location->id;
        if (RateLimiter::tooManyAttempts($phoneKey, 5)) {
            return back()->withInput()->withErrors([
                'child_name' => 'Prea multe înregistrări de pe acest număr. Încercați mai târziu.',
            ]);
        }
        RateLimiter::hit($phoneKey, 86400);

        $guardian->update([
            'terms_accepted_at' => now(),
            'gdpr_accepted_at' => now(),
            'terms_version' => LegalController::TERMS_VERSION,
            'gdpr_version' => LegalController::GDPR_VERSION,
        ]);

        $token = DB::transaction(function () use ($validated, $location, $guardian) {
            $child = Child::firstOrCreate(
                [
                    'name' => strtoupper($validated['child_name']),
                    'guardian_id' => $guardian->id,
                    'location_id' => $location->id,
                ],
                [
                    'internal_code' => $this->generateInternalCode($validated['child_name']),
                ]
            );

            return PreCheckinToken::create([
                'token' => (string) Str::uuid(),
                'location_id' => $location->id,
                'child_id' => $child->id,
                'guardian_id' => $guardian->id,
                'status' => 'pending',
                'expires_at' => now()->addMinutes(60),
            ]);
        });

        return redirect()->route('pre-checkin.qr', [
            'location' => $location,
            'token' => $token->token,
        ]);
    }

    public function viewRules(Location $location)
    {
        abort_if(! $location->rules_document_path, 404);

        $path = $location->rules_document_path;
        abort_if(! Storage::disk('public')->exists($path), 404);

        return response()->file(
            Storage::disk('public')->path($path),
            ['Content-Disposition' => 'inline; filename="' . basename($path) . '"']
        );
    }

    private function generateInternalCode(string $name): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
        $prefix = $prefix ?: 'COD';

        return $prefix . str_pad(random_int(1, 999), 3, '0', STR_PAD_LEFT);
    }
}
