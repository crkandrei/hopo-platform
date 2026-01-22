<?php

namespace App\Http\Controllers;

use App\Models\ScanEvent;
use App\Models\Location;
use App\Services\ScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    protected ScanService $scanService;

    public function __construct(ScanService $scanService)
    {
        $this->scanService = $scanService;
    }

    /**
     * Generează un cod de scanare nou
     */
    public function generateCode(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // SUPER_ADMIN poate genera coduri pentru orice locație
        if ($user->isSuperAdmin()) {
            $locationId = $request->get('location_id');
            if (!$locationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pentru SUPER_ADMIN, specificați location_id',
                ], 400);
            }
            $location = Location::find($locationId);
        } else {
            if (!$user->location_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilizatorul nu este asociat cu nicio locație',
                ], 400);
            }
            $location = Location::find($user->location_id);
        }
        
        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Locația nu a fost găsită',
            ], 404);
        }

        try {
            $code = $this->scanService->generateRandomCode($location);
            $scanEvent = $this->scanService->createScanEvent($location, $code);

            return response()->json([
                'success' => true,
                'code' => $code,
                'expires_at' => $scanEvent->expires_at->toISOString(),
                'message' => 'Cod generat cu succes',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Eroare la generarea codului: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validează un cod de scanare
     */
    public function validateCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:10',
        ]);

        $user = Auth::user();
        
        // SUPER_ADMIN poate valida coduri pentru orice locație
        if ($user->isSuperAdmin()) {
            $locationId = $request->get('location_id');
            if (!$locationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pentru SUPER_ADMIN, specificați location_id',
                ], 400);
            }
            $location = Location::find($locationId);
        } else {
            if (!$user->location_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilizatorul nu este asociat cu nicio locație',
                ], 400);
            }
            $location = Location::find($user->location_id);
        }
        
        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Locația nu a fost găsită',
            ], 404);
        }

        $result = $this->scanService->validateCode($request->code, $location);

        return response()->json([
            'success' => $result['valid'],
            'message' => $result['message'],
            'scan_event' => $result['scan_event'],
        ]);
    }

    /**
     * Obține scanările recente pentru tenant
     */
    public function getRecentScans(Request $request): JsonResponse
    {
        $user = Auth::user();
        $limit = $request->get('limit', 10);
        
        if ($user->isSuperAdmin()) {
            $locationId = $request->get('location_id');
            if (!$locationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pentru SUPER_ADMIN, specificați location_id',
                ], 400);
            }
            $scans = ScanEvent::where('location_id', $locationId)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        } else {
            if (!$user->location_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilizatorul nu este asociat cu nicio locație',
                ], 400);
            }
            
            $scans = ScanEvent::where('location_id', $user->location_id)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        }

        return response()->json([
            'success' => true,
            'scans' => $scans,
        ]);
    }

    /**
     * Obține statistici pentru tenant
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = Auth::user();
        $days = $request->get('days', 7);
        
        if ($user->isSuperAdmin()) {
            $locationId = $request->get('location_id');
            if (!$locationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pentru SUPER_ADMIN, specificați location_id',
                ], 400);
            }
            $location = Location::find($locationId);
        } else {
            if (!$user->location_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilizatorul nu este asociat cu nicio locație',
                ], 400);
            }
            $location = Location::find($user->location_id);
        }
        
        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Locația nu a fost găsită',
            ], 404);
        }
        
        $stats = $this->scanService->getLocationStats($location, $days);

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Curăță codurile expirate
     */
    public function cleanupExpiredCodes(): JsonResponse
    {
        $cleaned = $this->scanService->cleanupExpiredCodes();

        return response()->json([
            'success' => true,
            'message' => "Au fost curățate {$cleaned} coduri expirate",
            'cleaned_count' => $cleaned,
        ]);
    }
}
