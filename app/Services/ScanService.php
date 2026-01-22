<?php

namespace App\Services;

use App\Models\ScanEvent;
use App\Models\PlaySession;
use App\Models\Child;
use App\Models\Location;
use App\Services\PricingService;
use App\Support\ActionLogger;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ScanService
{
    /**
     * Charset pentru generarea codurilor (fără O/0, I/1)
     */
    private const CHARSET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    
    /**
     * Lungimea codului (configurabilă)
     */
    private const CODE_LENGTH = 10;
    
    /**
     * TTL pentru coduri în secunde (configurabil)
     */
    private const CODE_TTL_SECONDS = 60;

    /**
     * Generează un cod random unic pentru o locație
     */
    public function generateRandomCode(Location $location): string
    {
        $maxAttempts = 10;
        $attempts = 0;

        do {
            $code = $this->generateCode();
            $attempts++;
            
            // Verifică dacă codul este unic în intervalul TTL
            $isUnique = !$this->codeExistsInTTL($code, $location);
            
            if ($isUnique) {
                break;
            }
            
            if ($attempts >= $maxAttempts) {
                throw new \Exception('Nu s-a putut genera un cod unic după ' . $maxAttempts . ' încercări');
            }
            
        } while (!$isUnique);

        return $code;
    }

    /**
     * Creează un eveniment de scanare
     */
    public function createScanEvent(Location $location, string $code): ScanEvent
    {
        $now = now();
        $expiresAt = $now->copy()->addSeconds(self::CODE_TTL_SECONDS);

        $scanEvent = ScanEvent::create([
            'location_id' => $location->id,
            'code_used' => $code,
            'status' => 'pending',
            'scanned_at' => $now,
            'expires_at' => $expiresAt,
        ]);

        ActionLogger::logScan('created', $code, [
            'scan_event_id' => $scanEvent->id,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);

        return $scanEvent;
    }

    /**
     * Validează un cod de scanare
     */
    public function validateCode(string $code, Location $location): array
    {
        $scanEvent = ScanEvent::where('code_used', $code)
            ->where('location_id', $location->id)
            ->where('expires_at', '>', now())
            ->first();

        if (!$scanEvent) {
            return [
                'valid' => false,
                'message' => 'Cod invalid sau expirat',
                'scan_event' => null,
            ];
        }

        if ($scanEvent->isExpired()) {
            $scanEvent->update(['status' => 'expired']);
            ActionLogger::logScan('expired', $code, [
                'scan_event_id' => $scanEvent->id,
            ]);
            return [
                'valid' => false,
                'message' => 'Cod expirat',
                'scan_event' => $scanEvent,
            ];
        }

        // Marchează codul ca valid
        $scanEvent->update(['status' => 'valid']);
        ActionLogger::logScan('validated', $code, [
            'scan_event_id' => $scanEvent->id,
        ]);

        return [
            'valid' => true,
            'message' => 'Cod valid',
            'scan_event' => $scanEvent,
        ];
    }

    /**
     * Generează un cod random
     */
    private function generateCode(): string
    {
        $code = '';
        $charsetLength = strlen(self::CHARSET);
        
        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= self::CHARSET[random_int(0, $charsetLength - 1)];
        }
        
        return $code;
    }

    /**
     * Verifică dacă codul există în intervalul TTL
     */
    private function codeExistsInTTL(string $code, Location $location): bool
    {
        $ttlStart = now()->subSeconds(self::CODE_TTL_SECONDS);
        
        return ScanEvent::where('code_used', $code)
            ->where('location_id', $location->id)
            ->where('created_at', '>=', $ttlStart)
            ->exists();
    }

    /**
     * Curăță codurile expirate
     */
    public function cleanupExpiredCodes(): int
    {
        return ScanEvent::where('expires_at', '<', now())
            ->where('status', 'pending')
            ->update(['status' => 'expired']);
    }

    /**
     * Obține statistici pentru o locație
     */
    public function getLocationStats(Location $location, int $days = 7): array
    {
        $startDate = now()->subDays($days);
        
        $totalScans = ScanEvent::where('location_id', $location->id)
            ->where('created_at', '>=', $startDate)
            ->count();
            
        $validScans = ScanEvent::where('location_id', $location->id)
            ->where('status', 'valid')
            ->where('created_at', '>=', $startDate)
            ->count();
            
        $expiredScans = ScanEvent::where('location_id', $location->id)
            ->where('status', 'expired')
            ->where('created_at', '>=', $startDate)
            ->count();

        return [
            'total_scans' => $totalScans,
            'valid_scans' => $validScans,
            'expired_scans' => $expiredScans,
            'success_rate' => $totalScans > 0 ? round(($validScans / $totalScans) * 100, 2) : 0,
        ];
    }

    /**
     * Caută codul de bare și returnează informațiile despre copil și sesiune
     * Verifică dacă codul a fost deja folosit (single-use)
     */
    public function lookupBracelet(string $code, Location $location): array
    {
        // Trim only (no normalization - code should already be correct from frontend)
        $code = trim($code);
        
        if (empty($code)) {
            return [
                'success' => false,
                'message' => 'Codul nu poate fi gol',
            ];
        }

        // Verifică dacă există o sesiune activă cu acest cod
        $activeSession = PlaySession::where('bracelet_code', $code)
            ->where('location_id', $location->id)
            ->whereNull('ended_at')
            ->with(['child.guardian', 'intervals'])
            ->first();

        if ($activeSession) {
            // Există o sesiune activă - o returnăm
            $currentInterval = $activeSession->intervals->whereStrict('ended_at', null)->sortByDesc('started_at')->first();
            $activePayload = [
                'id' => $activeSession->id,
                'started_at' => optional($activeSession->started_at)->toISOString(),
                'is_paused' => $activeSession->isPaused(),
                'effective_seconds' => $activeSession->getEffectiveDurationSeconds(),
                'current_interval_started_at' => $currentInterval && $currentInterval->started_at ? $currentInterval->started_at->toISOString() : null,
            ];

            return [
                'success' => true,
                'message' => 'Cod deja folosit - sesiune activă',
                'bracelet_code' => $code,
                'child' => [
                    'id' => $activeSession->child->id,
                    'name' => $activeSession->child->name,
                    'internal_code' => $activeSession->child->internal_code,
                ],
                'guardian' => $activeSession->child->guardian,
                'active_session' => $activePayload,
                'can_start_session' => false,
            ];
        }

        // Nu există sesiune activă - codul poate fi folosit pentru o sesiune nouă
        // (permite reutilizarea codurilor după închiderea sesiunii)
        return [
            'success' => true,
            'message' => 'Cod disponibil - poate fi folosit pentru o sesiune nouă',
            'bracelet_code' => $code,
            'can_assign' => true,
        ];
    }

    /**
     * Începe o sesiune de joacă pentru un copil cu un cod de bare
     */
    public function startPlaySession(Location $location, Child $child, string $braceletCode): PlaySession
    {
        // Trim only (no normalization - code should already be correct from frontend)
        $braceletCode = trim($braceletCode);
        
        if (empty($braceletCode)) {
            throw new \Exception('Codul nu poate fi gol');
        }

        // Verifică dacă copilul are deja o sesiune activă
        $existingSession = PlaySession::where('child_id', $child->id)
            ->whereNull('ended_at')
            ->first();

        if ($existingSession) {
            throw new \Exception('Copilul are deja o sesiune activă');
        }

        // Permite reutilizarea codurilor după închiderea sesiunii
        // Verifică doar dacă există o sesiune activă cu acest cod
        $activeSessionWithCode = PlaySession::where('bracelet_code', $braceletCode)
            ->where('location_id', $location->id)
            ->whereNull('ended_at')
            ->first();

        if ($activeSessionWithCode) {
            throw new \Exception('Codul este deja folosit într-o sesiune activă. Te rog oprește sesiunea existentă înainte.');
        }

        $session = PlaySession::startSession($location, $child, $braceletCode);

        ActionLogger::logSession('started', $session->id, [
            'child_id' => $child->id,
            'child_name' => $child->name,
            'bracelet_code' => $braceletCode,
        ]);

        return $session;
    }

    /**
     * Oprește o sesiune de joacă
     */
    public function stopPlaySession(int $sessionId): PlaySession
    {
        $session = PlaySession::findOrFail($sessionId);
        
        if (!$session->isActive()) {
            throw new \Exception('Sesiunea nu este activă');
        }

        $session = $session->endSession();

        ActionLogger::logSession('stopped', $sessionId, [
            'duration_minutes' => $session->getCurrentDurationMinutes(),
            'duration_formatted' => $session->getFormattedDuration(),
        ]);

        return $session;
    }

    /** Pune pe pauză o sesiune de joacă */
    public function pausePlaySession(int $sessionId): PlaySession
    {
        $session = PlaySession::findOrFail($sessionId);
        if (!$session->isActive()) {
            throw new \Exception('Sesiunea nu este activă');
        }
        if ($session->isPaused()) {
            return $session;
        }
        $session = $session->pause();
        ActionLogger::logSession('paused', $sessionId, [
            'effective_seconds' => $session->getEffectiveDurationSeconds(),
        ]);
        return $session;
    }

    /** Reia o sesiune de joacă pauzată */
    public function resumePlaySession(int $sessionId): PlaySession
    {
        $session = PlaySession::findOrFail($sessionId);
        if (!$session->isActive()) {
            throw new \Exception('Sesiunea nu este activă');
        }
        if (!$session->isPaused()) {
            return $session;
        }
        $session = $session->resume();
        ActionLogger::logSession('resumed', $sessionId, [
            'effective_seconds' => $session->getEffectiveDurationSeconds(),
        ]);
        return $session;
    }

    /** Stop session (bracelets are single-use, no unassign needed) */
    public function stopAndUnassign(int $sessionId): PlaySession
    {
        return $this->stopPlaySession($sessionId);
    }

    /**
     * Obține sesiunile active pentru o locație
     */
    public function getActiveSessions(Location $location): array
    {
        return PlaySession::where('location_id', $location->id)
            ->whereNull('ended_at')
            ->with(['child.guardian', 'intervals'])
            ->get()
            ->map(function ($session) {
                $child = $session->child;
                $guardian = $child ? $child->guardian : null;
                $childName = $child ? $child->name : '-';
                $effectiveSeconds = $session->getEffectiveDurationSeconds();
                $currentInterval = $session->intervals->whereStrict('ended_at', null)->sortByDesc('started_at')->first();
                return [
                    'id' => $session->id,
                    'child_name' => $childName,
                    'parent_name' => $guardian->name ?? '-',
                    'started_at' => $session->started_at ? $session->started_at->toISOString() : null,
                    'duration' => $session->getFormattedDuration(),
                    'is_paused' => $session->isPaused(),
                    'effective_seconds' => $effectiveSeconds,
                    'current_interval_started_at' => $currentInterval && $currentInterval->started_at ? $currentInterval->started_at->toISOString() : null,
                    'bracelet_code' => $session->bracelet_code,
                    'estimated_price' => $session->calculatePrice(),
                    'formatted_estimated_price' => $session->getFormattedPrice(),
                ];
            })
            ->toArray();
    }

    /**
     * Obține statistici despre sesiuni pentru o locație
     */
    public function getSessionStats(Location $location, int $days = 7): array
    {
        $startDate = now()->subDays($days);
        
        $totalSessions = PlaySession::where('location_id', $location->id)
            ->where('started_at', '>=', $startDate)
            ->count();
            
        $activeSessions = PlaySession::where('location_id', $location->id)
            ->whereNull('ended_at')
            ->count();
            
        $completedSessions = PlaySession::where('location_id', $location->id)
            ->whereNotNull('ended_at')
            ->where('started_at', '>=', $startDate)
            ->count();

        // Recalculează durata din started_at și ended_at pentru sesiunile închise
        $completedSessionsCollection = PlaySession::where('location_id', $location->id)
            ->whereNotNull('ended_at')
            ->where('started_at', '>=', $startDate)
            ->get();

        $totalPlayTime = $completedSessionsCollection->reduce(function ($carry, $session) {
            return $carry + $session->getCurrentDurationMinutes();
        }, 0);

        return [
            'total_sessions' => $totalSessions,
            'active_sessions' => $activeSessions,
            'completed_sessions' => $completedSessions,
            'total_play_time_minutes' => $totalPlayTime,
            'total_play_time_formatted' => $this->formatMinutes($totalPlayTime),
        ];
    }

    /**
     * Return last N completed sessions for a location
     */
    public function getRecentCompletedSessions(Location $location, int $limit = 3): array
    {
        $sessions = PlaySession::where('location_id', $location->id)
            ->whereNotNull('ended_at')
            ->with(['child.guardian', 'intervals'])
            ->orderByDesc('ended_at')
            ->limit($limit)
            ->get();

        return $sessions->map(function ($session) {
            $child = $session->child;
            $childName = $child ? $child->name : '-';
            return [
                'id' => $session->id,
                'child_name' => $childName,
                'started_at' => $session->started_at ? $session->started_at->toISOString() : null,
                'ended_at' => $session->ended_at ? $session->ended_at->toISOString() : null,
                'effective_seconds' => $session->getEffectiveDurationSeconds(),
                'duration_formatted' => $session->getFormattedDuration(),
                'bracelet_code' => $session->bracelet_code,
            ];
        })->toArray();
    }

    /**
     * Formatează minutele în format ore:minute
     */
    private function formatMinutes(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $remainingMinutes);
        }
        
        return sprintf('%dm', $remainingMinutes);
    }
}
