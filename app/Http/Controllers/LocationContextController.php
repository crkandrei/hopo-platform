<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LocationContextController extends Controller
{
    /**
     * Set the selected location context for COMPANY_ADMIN
     * Updates the user's location_id in database to enable switching between locations
     */
    public function setLocation(Request $request)
    {
        $user = Auth::user();
        
        // Only COMPANY_ADMIN can change location context
        if (!$user->isCompanyAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $locationId = $request->input('location_id');
        
        if (!$locationId) {
            // Clear location_id from user
            $user->location_id = null;
            $user->save();
            Session::forget('selected_location_id');
            return response()->json(['success' => true, 'message' => 'Location context cleared']);
        }
        
        // Verify location belongs to user's company
        $location = Location::where('id', $locationId)
            ->where('company_id', $user->company_id)
            ->where('is_active', true)
            ->first();
        
        if (!$location) {
            return response()->json(['error' => 'Location not found or not accessible'], 404);
        }
        
        // Update user's location_id in database
        $user->location_id = $locationId;
        $user->save();
        
        // Also store in session for immediate use
        Session::put('selected_location_id', $locationId);
        
        return response()->json([
            'success' => true,
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
            ],
            'message' => 'Location context updated'
        ]);
    }
    
    /**
     * Get available locations for COMPANY_ADMIN
     */
    public function getLocations()
    {
        $user = Auth::user();
        
        if (!$user->isCompanyAdmin() || !$user->company_id) {
            return response()->json(['locations' => []]);
        }
        
        $locations = Location::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json(['locations' => $locations]);
    }
}
