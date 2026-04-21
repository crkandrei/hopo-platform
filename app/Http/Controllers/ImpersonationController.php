<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function impersonate(User $user): RedirectResponse
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Acces permis doar pentru super admin.');
        }

        if ($user->isSuperAdmin()) {
            abort(403, 'Nu poți impersona un alt super admin.');
        }

        if (session()->has('impersonator_id')) {
            abort(403, 'Ești deja în modul de impersonare.');
        }

        session(['impersonator_id' => $currentUser->id]);
        Auth::loginUsingId($user->id);

        return redirect()->route('dashboard');
    }

    public function stopImpersonating(): RedirectResponse
    {
        $impersonatorId = session('impersonator_id');

        Auth::loginUsingId($impersonatorId);
        session()->forget('impersonator_id');

        return redirect()->route('dashboard');
    }
}
