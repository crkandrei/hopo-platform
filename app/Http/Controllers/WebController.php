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
        $user = Auth::user()->load(['role', 'tenant']);
        
        // STAFF nu are acces la dashboard
        if ($user->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        return view('dashboard', compact('user'));
    }

    /**
     * Show landing page or redirect to app
     */
    public function index()
    {
        if (Auth::check()) {
            // STAFF merge la scan, alții la dashboard
            if (Auth::user()->isStaff()) {
                return redirect('/scan');
            }
            return redirect('/dashboard');
        }
        
        // Show landing page for non-authenticated users
        return view('landing');
    }
}
