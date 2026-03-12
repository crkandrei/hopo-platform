<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function __construct(
        protected VoucherService $voucherService
    ) {}

    public function index(Request $request, Location $location)
    {
        $this->authorize('viewAny', [Voucher::class, $location]);

        $query = Voucher::withoutGlobalScope('location')->where('location_id', $location->id)->withCount('usages');

        if ($request->filled('active')) {
            if ($request->active === '1') {
                $query->where('is_active', true);
            } else {
                $query->where('is_active', false);
            }
        }
        if ($request->filled('expired')) {
            if ($request->expired === '1') {
                $query->whereNotNull('expires_at')->where('expires_at', '<', now());
            } else {
                $query->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                });
            }
        }

        $vouchers = $query->orderByDesc('created_at')->paginate(20);

        return view('locations.vouchers.index', compact('location', 'vouchers'));
    }

    public function create(Location $location)
    {
        $this->authorize('create', [Voucher::class, $location]);
        return view('locations.vouchers.create', compact('location'));
    }

    public function store(Request $request, Location $location)
    {
        $this->authorize('create', [Voucher::class, $location]);

        $validated = $request->validate([
            'type' => 'required|in:amount,hours',
            'initial_value' => 'required|numeric|min:0.01|max:9999.99',
            'expires_at' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:500',
        ]);

        $code = $this->voucherService->generateUniqueCode($location);
        $voucher = Voucher::withoutGlobalScope('location')->create([
            'location_id' => $location->id,
            'code' => $code,
            'type' => $validated['type'],
            'initial_value' => $validated['initial_value'],
            'remaining_value' => $validated['initial_value'],
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => true,
            'created_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('locations.vouchers.show', [$location, $voucher])
            ->with('success', 'Voucherul a fost creat. Cod: ' . $code);
    }

    public function show(Location $location, Voucher $voucher)
    {
        if ($voucher->location_id !== $location->id) {
            abort(404);
        }
        $this->authorize('view', $voucher);

        $voucher->load(['usages' => function ($q) {
            $q->orderByDesc('used_at')->with(['playSession.child', 'standaloneReceipt']);
        }]);

        return view('locations.vouchers.show', compact('location', 'voucher'));
    }

    public function edit(Location $location, Voucher $voucher)
    {
        if ($voucher->location_id !== $location->id) {
            abort(404);
        }
        $this->authorize('update', $voucher);
        return view('locations.vouchers.edit', compact('location', 'voucher'));
    }

    public function update(Request $request, Location $location, Voucher $voucher)
    {
        if ($voucher->location_id !== $location->id) {
            abort(404);
        }
        $this->authorize('update', $voucher);

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $voucher->update([
            'notes' => $validated['notes'] ?? $voucher->notes,
            'is_active' => $request->boolean('is_active', $voucher->is_active),
        ]);

        return redirect()
            ->route('locations.vouchers.show', [$location, $voucher])
            ->with('success', 'Voucherul a fost actualizat.');
    }

    public function destroy(Location $location, Voucher $voucher)
    {
        if ($voucher->location_id !== $location->id) {
            abort(404);
        }
        $this->authorize('delete', $voucher);
        $voucher->delete();
        return redirect()
            ->route('locations.vouchers.index', $location)
            ->with('success', 'Voucherul a fost șters.');
    }

    public function report(Request $request, Location $location)
    {
        $this->authorize('viewAny', [Voucher::class, $location]);

        $stats = $this->voucherService->getVoucherStats($location);

        $query = Voucher::withoutGlobalScope('location')
            ->where('location_id', $location->id)
            ->withCount('usages');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })->where('remaining_value', '>', 0);
            } elseif ($request->status === 'expired') {
                $query->whereNotNull('expires_at')->where('expires_at', '<', now());
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'depleted') {
                $query->where('remaining_value', '<=', 0);
            }
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        $vouchers = $query->withCount('usages')->orderByDesc('created_at')->get();

        return view('locations.vouchers.report', compact('location', 'stats', 'vouchers'));
    }

    /**
     * Validate voucher for real-time frontend (code, location_id, optional type).
     */
    public function validate(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:32',
            'location_id' => 'required|exists:locations,id',
            'type' => 'nullable|in:amount,hours',
        ]);

        $location = Location::findOrFail($request->location_id);
        $this->authorize('validateAt', [Voucher::class, $location]);

        $result = $this->voucherService->validateVoucher(
            $request->code,
            $location,
            $request->type
        );

        if (!$result['valid']) {
            return response()->json([
                'valid' => false,
                'message' => $result['message'],
            ], 400);
        }

        $voucher = $result['voucher'];
        return response()->json([
            'valid' => true,
            'message' => $result['message'],
            'voucher_data' => [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'type' => $voucher->type,
                'initial_value' => (float) $voucher->initial_value,
                'remaining_value' => (float) $voucher->remaining_value,
                'expires_at' => $voucher->expires_at?->toIso8601String(),
            ],
        ]);
    }
}
