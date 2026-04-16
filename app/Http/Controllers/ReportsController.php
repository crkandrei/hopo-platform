<?php

namespace App\Http\Controllers;

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
        if (Auth::user()->isStaff()) {
            abort(403, 'Acces interzis');
        }

        return view('reports.gdpr-compliance');
    }

    public function gdprComplianceData(Request $request)
    {
        if (Auth::user()->isStaff()) {
            abort(403, 'Acces interzis');
        }

        return response()->json(['success' => true, 'data' => [], 'meta' => ['page' => 1, 'per_page' => 10, 'total' => 0, 'total_pages' => 1], 'summary' => ['total' => 0, 'both_accepted' => 0, 'pending' => 0]]);
    }

    public function gdprCompliancePdf(Request $request)
    {
        if (Auth::user()->isStaff()) {
            abort(403, 'Acces interzis');
        }

        return view('reports.gdpr-compliance-pdf');
    }
}
