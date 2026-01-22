<?php

namespace App\Http\Controllers;

use App\Models\PlaySession;
use App\Support\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\PricingService;

class AnomaliesController extends Controller
{
    /**
     * Display the anomalies page
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Acces permis doar pentru super admin');
        }
        
        return view('anomalies.index');
    }

    /**
     * Scan for anomalies in the last 7 days
     * Returns JSON with counts for each anomaly type
     */
    public function scan(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            return ApiResponder::error('Acces permis doar pentru super admin', 403);
        }

        $sevenDaysAgo = Carbon::now()->subDays(7);
        $oneDayAgo = Carbon::now()->subDay();
        $now = Carbon::now();

        $results = [];

        // 1. Sesiuni de peste 5 ore (durata efectivă > 5 ore = 18000 secunde)
        // Include atât sesiunile active cât și cele închise
        // Calculăm durata din intervale, sau dacă nu există intervale, din started_at și ended_at/NOW()
        $results['sesiuni_peste_5_ore'] = DB::table('play_sessions as ps')
            ->where('ps.started_at', '>=', $sevenDaysAgo)
            ->whereRaw("(
                COALESCE((
                    SELECT SUM(TIMESTAMPDIFF(SECOND, psi.started_at, COALESCE(psi.ended_at, NOW())))
                    FROM play_session_intervals psi
                    WHERE psi.play_session_id = ps.id
                    AND psi.started_at IS NOT NULL
                ), 
                TIMESTAMPDIFF(SECOND, ps.started_at, COALESCE(ps.ended_at, NOW()))
                )
            ) > 18000")
            ->count();

        // 2. Sesiuni active prea vechi (peste 24 ore)
        $results['sesiuni_active_prea_vechi'] = PlaySession::where('started_at', '>=', $sevenDaysAgo)
            ->whereNull('ended_at')
            ->where('started_at', '<', $oneDayAgo)
            ->count();

        // 3. Sesiuni cu preț zero sau negativ
        $results['sesiuni_pret_zero_negativ'] = PlaySession::where('started_at', '>=', $sevenDaysAgo)
            ->whereNotNull('ended_at')
            ->where(function($q) {
                $q->whereNull('calculated_price')
                   ->orWhere('calculated_price', '<=', 0);
            })
            ->count();

        // 4. Sesiuni cu date invalide (ended_at < started_at sau started_at > now sau intervale invalide)
        $results['sesiuni_date_invalide'] = PlaySession::where('started_at', '>=', $sevenDaysAgo)
            ->where(function($q) use ($now) {
                // Sesiuni cu ended_at < started_at (doar pentru sesiunile închise)
                $q->whereRaw('ended_at IS NOT NULL AND ended_at < started_at')
                  // Sesiuni cu started_at în viitor
                  ->orWhere('started_at', '>', $now)
                  // Intervale cu ended_at < started_at
                  ->orWhereExists(function($subquery) {
                      $subquery->select(DB::raw(1))
                          ->from('play_session_intervals')
                          ->whereColumn('play_session_intervals.play_session_id', 'play_sessions.id')
                          ->whereNotNull('play_session_intervals.ended_at')
                          ->whereNotNull('play_session_intervals.started_at')
                          ->whereRaw('play_session_intervals.ended_at < play_session_intervals.started_at');
                  });
            })
            ->count();

        // 5. Sesiuni cu preț necalculat
        $results['sesiuni_pret_necalculat'] = PlaySession::where('started_at', '>=', $sevenDaysAgo)
            ->whereNotNull('ended_at')
            ->where(function($q) {
                $q->whereNull('calculated_price')
                   ->orWhereNull('price_per_hour_at_calculation');
            })
            ->count();

        // 6. Sesiuni foarte scurte (durata efectivă < 60 secunde)
        // Doar pentru sesiunile închise (sesiunile active pot fi încă în desfășurare)
        $results['sesiuni_foarte_scurte'] = DB::table('play_sessions as ps')
            ->where('ps.started_at', '>=', $sevenDaysAgo)
            ->whereNotNull('ps.ended_at')
            ->whereRaw("(
                COALESCE((
                    SELECT SUM(TIMESTAMPDIFF(SECOND, psi.started_at, COALESCE(psi.ended_at, NOW())))
                    FROM play_session_intervals psi
                    WHERE psi.play_session_id = ps.id
                    AND psi.started_at IS NOT NULL
                ),
                TIMESTAMPDIFF(SECOND, ps.started_at, ps.ended_at)
                )
            ) < 60")
            ->count();

        // 7. Sesiuni cu prea multe pauze (peste 20 intervale)
        $results['sesiuni_prea_multe_pauze'] = DB::table('play_sessions as ps')
            ->where('ps.started_at', '>=', $sevenDaysAgo)
            ->whereRaw('(
                SELECT COUNT(*)
                FROM play_session_intervals psi
                WHERE psi.play_session_id = ps.id
            ) > 20')
            ->count();

        // 8. Sesiuni cu intervale deschise (sesiuni închise dar cu intervale fără ended_at)
        $results['sesiuni_intervale_deschise'] = PlaySession::where('started_at', '>=', $sevenDaysAgo)
            ->whereNotNull('ended_at')
            ->whereExists(function($q) {
                $q->select(DB::raw(1))
                  ->from('play_session_intervals')
                  ->whereColumn('play_session_intervals.play_session_id', 'play_sessions.id')
                  ->whereNull('play_session_intervals.ended_at');
            })
            ->count();

        // 9. Sesiuni multiple active pentru același copil
        // Numărăm câte sesiuni active sunt pentru copiii care au mai mult de o sesiune activă
        $results['sesiuni_multiple_active_copil'] = DB::table('play_sessions as ps')
            ->where('ps.started_at', '>=', $sevenDaysAgo)
            ->whereNull('ps.ended_at')
            ->whereIn('ps.child_id', function($query) use ($sevenDaysAgo) {
                $query->select('child_id')
                    ->from('play_sessions')
                    ->where('started_at', '>=', $sevenDaysAgo)
                    ->whereNull('ended_at')
                    ->groupBy('child_id')
                    ->havingRaw('COUNT(*) > 1');
            })
            ->count();

        // 10. Sesiuni cu cod brățară invalid
        $results['sesiuni_cod_bratara_invalid'] = PlaySession::where('started_at', '>=', $sevenDaysAgo)
            ->where(function($q) {
                $q->whereNull('bracelet_code')
                   ->orWhere('bracelet_code', '');
            })
            ->count();

        // 11. Sesiuni cu discrepanțe de preț (diferență > 0.10 RON)
        // Procesăm în batch-uri pentru eficiență
        $priceDiscrepancyCount = 0;
        $pricingService = app(PricingService::class);
        
        PlaySession::where('started_at', '>=', $sevenDaysAgo)
            ->whereNotNull('ended_at')
            ->whereNotNull('calculated_price')
            ->with(['location', 'intervals'])
            ->chunk(100, function($sessions) use (&$priceDiscrepancyCount, $pricingService) {
                foreach ($sessions as $session) {
                    $calculatedPrice = (float) $session->calculated_price;
                    $recalculatedPrice = $pricingService->calculateSessionPrice($session);
                    $difference = abs($calculatedPrice - $recalculatedPrice);
                    
                    if ($difference > 0.10) {
                        $priceDiscrepancyCount++;
                    }
                }
            });

        $results['sesiuni_discrepante_pret'] = $priceDiscrepancyCount;

        return ApiResponder::success($results);
    }

    /**
     * Get sessions for a specific anomaly type
     */
    public function getSessions(Request $request, $type)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            return ApiResponder::error('Acces permis doar pentru super admin', 403);
        }

        $sevenDaysAgo = Carbon::now()->subDays(7);
        $oneDayAgo = Carbon::now()->subDay();
        $now = Carbon::now();

        $sessions = collect();

        switch ($type) {
            case 'sesiuni_peste_5_ore':
                $sessionIds = DB::table('play_sessions as ps')
                    ->where('ps.started_at', '>=', $sevenDaysAgo)
                    ->whereRaw("(
                        COALESCE((
                            SELECT SUM(TIMESTAMPDIFF(SECOND, psi.started_at, COALESCE(psi.ended_at, NOW())))
                            FROM play_session_intervals psi
                            WHERE psi.play_session_id = ps.id
                            AND psi.started_at IS NOT NULL
                        ), 
                        TIMESTAMPDIFF(SECOND, ps.started_at, COALESCE(ps.ended_at, NOW()))
                        )
                    ) > 18000")
                    ->pluck('ps.id');
                break;

            case 'sesiuni_active_prea_vechi':
                $sessionIds = PlaySession::where('started_at', '>=', $sevenDaysAgo)
                    ->whereNull('ended_at')
                    ->where('started_at', '<', $oneDayAgo)
                    ->pluck('id');
                break;

            case 'sesiuni_pret_zero_negativ':
                $sessionIds = PlaySession::where('started_at', '>=', $sevenDaysAgo)
                    ->whereNotNull('ended_at')
                    ->where(function($q) {
                        $q->whereNull('calculated_price')
                           ->orWhere('calculated_price', '<=', 0);
                    })
                    ->pluck('id');
                break;

            case 'sesiuni_date_invalide':
                $sessionIds = PlaySession::where('started_at', '>=', $sevenDaysAgo)
                    ->where(function($q) use ($now) {
                        $q->whereRaw('ended_at IS NOT NULL AND ended_at < started_at')
                          ->orWhere('started_at', '>', $now)
                          ->orWhereExists(function($subquery) {
                              $subquery->select(DB::raw(1))
                                  ->from('play_session_intervals')
                                  ->whereColumn('play_session_intervals.play_session_id', 'play_sessions.id')
                                  ->whereNotNull('play_session_intervals.ended_at')
                                  ->whereNotNull('play_session_intervals.started_at')
                                  ->whereRaw('play_session_intervals.ended_at < play_session_intervals.started_at');
                          });
                    })
                    ->pluck('id');
                break;

            case 'sesiuni_pret_necalculat':
                $sessionIds = PlaySession::where('started_at', '>=', $sevenDaysAgo)
                    ->whereNotNull('ended_at')
                    ->where(function($q) {
                        $q->whereNull('calculated_price')
                           ->orWhereNull('price_per_hour_at_calculation');
                    })
                    ->pluck('id');
                break;

            case 'sesiuni_foarte_scurte':
                $sessionIds = DB::table('play_sessions as ps')
                    ->where('ps.started_at', '>=', $sevenDaysAgo)
                    ->whereNotNull('ps.ended_at')
                    ->whereRaw("(
                        COALESCE((
                            SELECT SUM(TIMESTAMPDIFF(SECOND, psi.started_at, COALESCE(psi.ended_at, NOW())))
                            FROM play_session_intervals psi
                            WHERE psi.play_session_id = ps.id
                            AND psi.started_at IS NOT NULL
                        ),
                        TIMESTAMPDIFF(SECOND, ps.started_at, ps.ended_at)
                        )
                    ) < 60")
                    ->pluck('ps.id');
                break;

            case 'sesiuni_prea_multe_pauze':
                $sessionIds = DB::table('play_sessions as ps')
                    ->where('ps.started_at', '>=', $sevenDaysAgo)
                    ->whereRaw('(
                        SELECT COUNT(*)
                        FROM play_session_intervals psi
                        WHERE psi.play_session_id = ps.id
                    ) > 20')
                    ->pluck('ps.id');
                break;

            case 'sesiuni_intervale_deschise':
                $sessionIds = PlaySession::where('started_at', '>=', $sevenDaysAgo)
                    ->whereNotNull('ended_at')
                    ->whereExists(function($q) {
                        $q->select(DB::raw(1))
                          ->from('play_session_intervals')
                          ->whereColumn('play_session_intervals.play_session_id', 'play_sessions.id')
                          ->whereNull('play_session_intervals.ended_at');
                    })
                    ->pluck('id');
                break;

            case 'sesiuni_multiple_active_copil':
                $sessionIds = DB::table('play_sessions as ps')
                    ->where('ps.started_at', '>=', $sevenDaysAgo)
                    ->whereNull('ps.ended_at')
                    ->whereIn('ps.child_id', function($query) use ($sevenDaysAgo) {
                        $query->select('child_id')
                            ->from('play_sessions')
                            ->where('started_at', '>=', $sevenDaysAgo)
                            ->whereNull('ended_at')
                            ->groupBy('child_id')
                            ->havingRaw('COUNT(*) > 1');
                    })
                    ->pluck('ps.id');
                break;

            case 'sesiuni_cod_bratara_invalid':
                $sessionIds = PlaySession::where('started_at', '>=', $sevenDaysAgo)
                    ->where(function($q) {
                        $q->whereNull('bracelet_code')
                           ->orWhere('bracelet_code', '');
                    })
                    ->pluck('id');
                break;

            case 'sesiuni_discrepante_pret':
                $sessionIds = collect();
                $pricingService = app(PricingService::class);
                
                PlaySession::where('started_at', '>=', $sevenDaysAgo)
                    ->whereNotNull('ended_at')
                    ->whereNotNull('calculated_price')
                    ->with(['location', 'intervals'])
                    ->chunk(100, function($sessions) use (&$sessionIds, $pricingService) {
                        foreach ($sessions as $session) {
                            $calculatedPrice = (float) $session->calculated_price;
                            $recalculatedPrice = $pricingService->calculateSessionPrice($session);
                            $difference = abs($calculatedPrice - $recalculatedPrice);
                            
                            if ($difference > 0.10) {
                                $sessionIds->push($session->id);
                            }
                        }
                    });
                break;

            default:
                return ApiResponder::error('Tip de anomalie invalid', 400);
        }

        // Load sessions with relationships
        $sessions = PlaySession::whereIn('id', $sessionIds)
            ->with(['child', 'location'])
            ->orderBy('started_at', 'desc')
            ->get()
            ->map(function($session) {
                return [
                    'id' => $session->id,
                    'child_name' => $session->child ? $session->child->name : 'N/A',
                    'started_at' => $session->started_at ? $session->started_at->format('d.m.Y H:i') : null,
                    'ended_at' => $session->ended_at ? $session->ended_at->format('d.m.Y H:i') : null,
                    'is_active' => is_null($session->ended_at),
                    'bracelet_code' => $session->bracelet_code,
                ];
            });

        return ApiResponder::success([
            'sessions' => $sessions,
            'count' => $sessions->count(),
        ]);
    }
}

