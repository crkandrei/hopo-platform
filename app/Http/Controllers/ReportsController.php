<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    public function traffic()
    {
        if (!Auth::user()) {
            return redirect()->route('login');
        }

        if (Auth::user()->isStaff()) {
            abort(403, 'Acces interzis');
        }

        return view('reports.traffic');
    }

    public function gdprCompliance()
    {
        $user = Auth::user();
        if ($user->isStaff()) {
            abort(403, 'Acces interzis');
        }

        return view('reports.gdpr-compliance');
    }

    public function gdprComplianceData(Request $request)
    {
        $user = Auth::user();
        if ($user->isStaff()) {
            abort(403, 'Acces interzis');
        }
        if (!$user->location) {
            return response()->json(['success' => false, 'message' => 'Fără locație asociată'], 400);
        }

        $locationId = $user->location->id;

        $request->validate([
            'page'         => 'nullable|integer|min:1',
            'per_page'     => 'nullable|integer|in:10,25,50,100',
            'terms_status' => 'nullable|in:all,accepted,not_accepted',
            'gdpr_status'  => 'nullable|in:all,accepted,not_accepted',
            'sort_by'      => 'nullable|in:name,terms_accepted_at,gdpr_accepted_at,created_at',
            'sort_dir'     => 'nullable|in:asc,desc',
        ]);

        $page    = max(1, (int) $request->input('page', 1));
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }
        $termsStatus = $request->input('terms_status', 'all');
        $gdprStatus  = $request->input('gdpr_status', 'all');
        $sortBy      = $request->input('sort_by', 'name');
        $sortDir     = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $baseQuery = Guardian::where('location_id', $locationId);

        // Summary (pe toate, fără filtrele de status)
        $totalAll       = (clone $baseQuery)->count();
        $bothAccepted   = (clone $baseQuery)->whereNotNull('terms_accepted_at')->whereNotNull('gdpr_accepted_at')->count();
        $pending        = $totalAll - $bothAccepted;

        // Filtre status
        $query = (clone $baseQuery)
            ->when($termsStatus === 'accepted', fn($q) => $q->whereNotNull('terms_accepted_at'))
            ->when($termsStatus === 'not_accepted', fn($q) => $q->whereNull('terms_accepted_at'))
            ->when($gdprStatus === 'accepted', fn($q) => $q->whereNotNull('gdpr_accepted_at'))
            ->when($gdprStatus === 'not_accepted', fn($q) => $q->whereNull('gdpr_accepted_at'));

        $allowedSorts = ['name', 'terms_accepted_at', 'gdpr_accepted_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'name';
        }
        $query->orderBy($sortBy, $sortDir);

        $total      = $query->count();
        $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 1;

        $rows = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['id', 'name', 'phone', 'terms_accepted_at', 'terms_version', 'gdpr_accepted_at', 'gdpr_version', 'created_at']);

        $data = $rows->map(fn($g) => [
            'id'               => $g->id,
            'name'             => $g->name,
            'phone'            => $g->phone ?? '—',
            'terms_accepted_at' => $g->terms_accepted_at?->format('d.m.Y H:i'),
            'terms_version'    => $g->terms_version,
            'gdpr_accepted_at' => $g->gdpr_accepted_at?->format('d.m.Y H:i'),
            'gdpr_version'     => $g->gdpr_version,
            'created_at'       => $g->created_at->format('d.m.Y'),
        ]);

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $total,
                'total_pages' => $totalPages,
            ],
            'summary' => [
                'total'         => $totalAll,
                'both_accepted' => $bothAccepted,
                'pending'       => $pending,
            ],
        ]);
    }

    public function gdprCompliancePdf(Request $request)
    {
        $user = Auth::user();
        if ($user->isStaff()) {
            abort(403, 'Acces interzis');
        }

        if (!$user->location) {
            abort(400, 'Fără locație asociată');
        }

        $locationId = $user->location->id;

        $request->validate([
            'terms_status' => 'nullable|in:all,accepted,not_accepted',
            'gdpr_status'  => 'nullable|in:all,accepted,not_accepted',
        ]);

        $termsStatus = $request->input('terms_status', 'all');
        $gdprStatus  = $request->input('gdpr_status', 'all');

        $guardians = Guardian::where('location_id', $locationId)
            ->when($termsStatus === 'accepted', fn($q) => $q->whereNotNull('terms_accepted_at'))
            ->when($termsStatus === 'not_accepted', fn($q) => $q->whereNull('terms_accepted_at'))
            ->when($gdprStatus === 'accepted', fn($q) => $q->whereNotNull('gdpr_accepted_at'))
            ->when($gdprStatus === 'not_accepted', fn($q) => $q->whereNull('gdpr_accepted_at'))
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'terms_accepted_at', 'terms_version', 'gdpr_accepted_at', 'gdpr_version', 'created_at']);

        $total        = Guardian::where('location_id', $locationId)->count();
        $bothAccepted = Guardian::where('location_id', $locationId)
            ->whereNotNull('terms_accepted_at')
            ->whereNotNull('gdpr_accepted_at')
            ->count();

        return view('reports.gdpr-compliance-pdf', [
            'guardians'   => $guardians,
            'location'    => $user->location,
            'generatedAt' => now()->format('d.m.Y H:i'),
            'summary'     => [
                'total'         => $total,
                'both_accepted' => $bothAccepted,
                'pending'       => $total - $bothAccepted,
            ],
            'filters' => [
                'terms_status' => $termsStatus,
                'gdpr_status'  => $gdprStatus,
            ],
        ]);
    }
}
