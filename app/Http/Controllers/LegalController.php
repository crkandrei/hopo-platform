<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LegalController extends Controller
{
    /**
     * Current version of terms and conditions
     */
    const TERMS_VERSION = '1.0';
    
    /**
     * Current version of GDPR policy
     */
    const GDPR_VERSION = '1.0';

    /**
     * Display terms and conditions page
     */
    public function terms()
    {
        $locationName = 'Locului de Joacă';
        
        // Try to get location name from authenticated user
        if (Auth::check() && Auth::user()->location) {
            $locationName = Auth::user()->location->name;
        }
        
        return view('legal.terms', [
            'version' => self::TERMS_VERSION,
            'locationName' => $locationName,
        ]);
    }

    /**
     * Display GDPR policy page
     */
    public function gdpr()
    {
        $locationName = 'Locului de Joacă';

        // Try to get location name from authenticated user
        if (Auth::check() && Auth::user()->location) {
            $locationName = Auth::user()->location->name;
        }

        return view('legal.gdpr', [
            'version' => self::GDPR_VERSION,
            'locationName' => $locationName,
        ]);
    }

    /**
     * Display GDPR policy page for public (unauthenticated) access
     */
    public function gdprPublic()
    {
        return view('legal.gdpr-public', [
            'version' => self::GDPR_VERSION,
        ]);
    }

    /**
     * Display terms and conditions page for public (unauthenticated) access
     */
    public function termsPublic()
    {
        return view('legal.terms-public', [
            'version' => self::TERMS_VERSION,
        ]);
    }
}
