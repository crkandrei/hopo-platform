<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        
        return view('dashboard', compact('user'));
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
