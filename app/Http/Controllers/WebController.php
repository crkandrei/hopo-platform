<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Location;
use App\Models\PlaySession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WebController extends Controller
{
    /**
     * Show dashboard
     */
    public function dashboard()
    {
        $user = Auth::user()->load(['role', 'location', 'company']);

        // STAFF nu are acces la dashboard
        if ($user->isStaff()) {
            abort(403, 'Acces interzis');
        }

        if ($user->isSuperAdmin()) {
            return $this->superAdminDashboard();
        }

        return view('dashboard', compact('user'));
    }

    private function superAdminDashboard()
    {
        $today = now()->toDateString();

        // Per-company sessions today
        $sessionsByCompany = DB::table('play_sessions')
            ->join('locations', 'play_sessions.location_id', '=', 'locations.id')
            ->whereDate('play_sessions.started_at', $today)
            ->select('locations.company_id', DB::raw('COUNT(*) as sessions_today'))
            ->groupBy('locations.company_id')
            ->pluck('sessions_today', 'company_id');

        // Per-company income today (paid sessions)
        $incomeByCompany = DB::table('play_sessions')
            ->join('locations', 'play_sessions.location_id', '=', 'locations.id')
            ->whereDate('play_sessions.paid_at', $today)
            ->whereNotNull('play_sessions.paid_at')
            ->select('locations.company_id', DB::raw('SUM(play_sessions.calculated_price) as income_today'))
            ->groupBy('locations.company_id')
            ->pluck('income_today', 'company_id');

        // Per-company active sessions right now
        $activeByCompany = DB::table('play_sessions')
            ->join('locations', 'play_sessions.location_id', '=', 'locations.id')
            ->whereNull('play_sessions.ended_at')
            ->select('locations.company_id', DB::raw('COUNT(*) as active_now'))
            ->groupBy('locations.company_id')
            ->pluck('active_now', 'company_id');

        $companies = Company::withCount(['locations', 'users'])
            ->with('locations:id,company_id,is_active')
            ->orderBy('name')
            ->get()
            ->map(function ($company) use ($sessionsByCompany, $incomeByCompany, $activeByCompany) {
                $company->sessions_today = $sessionsByCompany[$company->id] ?? 0;
                $company->income_today   = (float) ($incomeByCompany[$company->id] ?? 0);
                $company->active_now     = $activeByCompany[$company->id] ?? 0;
                $company->active_locations = $company->locations->where('is_active', true)->count();
                return $company;
            });

        $stats = [
            'companies'       => $companies->count(),
            'locations'       => Location::count(),
            'users'           => User::whereHas('role', fn($q) => $q->whereIn('name', ['COMPANY_ADMIN', 'STAFF']))->count(),
            'sessions_today'  => $sessionsByCompany->sum(),
            'income_today'    => (float) $incomeByCompany->sum(),
            'active_now'      => $activeByCompany->sum(),
        ];

        return view('dashboard-superadmin', compact('stats', 'companies'));
    }

    /**
     * Show landing page
     * Landing page is always accessible, regardless of authentication status
     * On production: www.hopo.ro -> landing, app.hopo.ro -> application
     */
    public function index()
    {
        // Always show landing page - no redirect for authenticated users
        // Users should access the app via app.hopo.ro or /dashboard directly
        return view('landing');
    }
}
