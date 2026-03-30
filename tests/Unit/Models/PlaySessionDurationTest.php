<?php

namespace Tests\Unit\Models;

use App\Models\PlaySession;
use App\Models\PlaySessionInterval;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Testează getEffectiveDurationSeconds() — suma intervalelor de joc,
 * excluzând complet timpii de pauză dintre intervale.
 */
class PlaySessionDurationTest extends TestCase
{
    public function test_single_closed_interval_gives_correct_duration(): void
    {
        $session = PlaySession::factory()->create([
            'started_at' => Carbon::parse('2024-01-15 10:00:00'),
            'ended_at'   => Carbon::parse('2024-01-15 11:00:00'),
        ]);

        PlaySessionInterval::create([
            'play_session_id' => $session->id,
            'started_at'      => Carbon::parse('2024-01-15 10:00:00'),
            'ended_at'        => Carbon::parse('2024-01-15 11:00:00'),
            'duration_seconds' => 3600,
        ]);

        $this->assertEquals(3600, $session->getEffectiveDurationSeconds());
    }

    public function test_pause_time_is_excluded_from_duration(): void
    {
        $session = PlaySession::factory()->create([
            'started_at' => Carbon::parse('2024-01-15 10:00:00'),
            'ended_at'   => Carbon::parse('2024-01-15 12:00:00'),
        ]);

        // Joacă: 10:00-10:30 = 30 min
        PlaySessionInterval::create([
            'play_session_id' => $session->id,
            'started_at'      => Carbon::parse('2024-01-15 10:00:00'),
            'ended_at'        => Carbon::parse('2024-01-15 10:30:00'),
            'duration_seconds' => 1800,
        ]);

        // Pauză 10:30-11:00 — nu se numără

        // Joacă: 11:00-12:00 = 60 min
        PlaySessionInterval::create([
            'play_session_id' => $session->id,
            'started_at'      => Carbon::parse('2024-01-15 11:00:00'),
            'ended_at'        => Carbon::parse('2024-01-15 12:00:00'),
            'duration_seconds' => 3600,
        ]);

        // 30 min + 60 min = 90 min = 5400 sec (nu 120 min)
        $this->assertEquals(5400, $session->getEffectiveDurationSeconds());
    }

    public function test_multiple_pauses_all_excluded(): void
    {
        $session = PlaySession::factory()->create([
            'started_at' => Carbon::parse('2024-01-15 10:00:00'),
            'ended_at'   => Carbon::parse('2024-01-15 14:00:00'),
        ]);

        // 3 intervale de 30 min fiecare, separate de pauze
        PlaySessionInterval::create([
            'play_session_id' => $session->id,
            'started_at'      => Carbon::parse('2024-01-15 10:00:00'),
            'ended_at'        => Carbon::parse('2024-01-15 10:30:00'),
            'duration_seconds' => 1800,
        ]);
        PlaySessionInterval::create([
            'play_session_id' => $session->id,
            'started_at'      => Carbon::parse('2024-01-15 11:00:00'),
            'ended_at'        => Carbon::parse('2024-01-15 11:30:00'),
            'duration_seconds' => 1800,
        ]);
        PlaySessionInterval::create([
            'play_session_id' => $session->id,
            'started_at'      => Carbon::parse('2024-01-15 13:00:00'),
            'ended_at'        => Carbon::parse('2024-01-15 13:30:00'),
            'duration_seconds' => 1800,
        ]);

        // Total joacă: 3 × 30 min = 90 min = 5400 sec
        $this->assertEquals(5400, $session->getEffectiveDurationSeconds());
    }

    public function test_open_interval_counts_time_until_now(): void
    {
        $startedAt = now()->subMinutes(30);

        $session = PlaySession::factory()->create([
            'started_at' => $startedAt,
            'ended_at'   => null,
        ]);

        PlaySessionInterval::create([
            'play_session_id'  => $session->id,
            'started_at'       => $startedAt,
            'ended_at'         => null,
            'duration_seconds' => null,
        ]);

        $duration = $session->getEffectiveDurationSeconds();

        // ~30 min = ~1800 sec, toleranță ±10 sec pentru execuție test
        $this->assertGreaterThanOrEqual(1790, $duration);
        $this->assertLessThanOrEqual(1815, $duration);
    }

    public function test_closed_plus_open_interval_sums_correctly(): void
    {
        $session = PlaySession::factory()->create([
            'started_at' => Carbon::parse('2024-01-15 10:00:00'),
            'ended_at'   => null,
        ]);

        // Interval închis: 30 min
        PlaySessionInterval::create([
            'play_session_id' => $session->id,
            'started_at'      => Carbon::parse('2024-01-15 10:00:00'),
            'ended_at'        => Carbon::parse('2024-01-15 10:30:00'),
            'duration_seconds' => 1800,
        ]);

        // Interval deschis: pornit acum 10 min
        PlaySessionInterval::create([
            'play_session_id'  => $session->id,
            'started_at'       => now()->subMinutes(10),
            'ended_at'         => null,
            'duration_seconds' => null,
        ]);

        $duration = $session->getEffectiveDurationSeconds();

        // 1800 sec (închis) + ~600 sec (deschis) = ~2400 sec
        $this->assertGreaterThanOrEqual(2390, $duration);
        $this->assertLessThanOrEqual(2420, $duration);
    }

    public function test_fallback_to_started_at_ended_at_when_no_intervals(): void
    {
        $session = PlaySession::factory()->create([
            'started_at' => Carbon::parse('2024-01-15 10:00:00'),
            'ended_at'   => Carbon::parse('2024-01-15 11:30:00'),
        ]);

        // Fără niciun interval (date istorice)
        $this->assertEquals(5400, $session->getEffectiveDurationSeconds());
    }

    public function test_two_minute_session_below_one_hour_still_returns_correct_seconds(): void
    {
        $session = PlaySession::factory()->create([
            'started_at' => Carbon::parse('2024-01-15 10:00:00'),
            'ended_at'   => Carbon::parse('2024-01-15 10:02:00'),
        ]);

        PlaySessionInterval::create([
            'play_session_id'  => $session->id,
            'started_at'       => Carbon::parse('2024-01-15 10:00:00'),
            'ended_at'         => Carbon::parse('2024-01-15 10:02:00'),
            'duration_seconds' => 120,
        ]);

        $this->assertEquals(120, $session->getEffectiveDurationSeconds());
    }

    // =========================================================
    // Intrări invalide / date istorice corupte
    // =========================================================

    public function test_interval_with_null_started_at_is_skipped_gracefully(): void
    {
        // DB impune NOT NULL pe started_at, deci un interval corupt nu poate fi
        // inserat în mod normal. Testăm comportamentul prin încărcarea relației
        // direct în memorie cu un obiect fals care are started_at null.
        $session = PlaySession::factory()->create([
            'started_at' => Carbon::parse('2024-01-15 10:00:00'),
            'ended_at'   => Carbon::parse('2024-01-15 11:00:00'),
        ]);

        $validInterval = new PlaySessionInterval([
            'play_session_id'  => $session->id,
            'started_at'       => Carbon::parse('2024-01-15 10:00:00'),
            'ended_at'         => Carbon::parse('2024-01-15 10:30:00'),
            'duration_seconds' => 1800,
        ]);

        $corruptInterval = new PlaySessionInterval([
            'play_session_id'  => $session->id,
            'started_at'       => null,
            'ended_at'         => Carbon::parse('2024-01-15 10:45:00'),
            'duration_seconds' => null,
        ]);

        // Încărcăm manual relația în memorie — ocolim DB-ul
        $session->setRelation('intervals', collect([$validInterval, $corruptInterval]));

        // Intervalul corupt (started_at null) e ignorat — se numără doar cel valid
        $this->assertEquals(1800, $session->getEffectiveDurationSeconds());
    }

    public function test_zero_effective_duration_when_interval_starts_and_ends_at_same_time(): void
    {
        $session = PlaySession::factory()->create([
            'started_at' => Carbon::parse('2024-01-15 10:00:00'),
            'ended_at'   => Carbon::parse('2024-01-15 10:00:00'),
        ]);

        PlaySessionInterval::create([
            'play_session_id'  => $session->id,
            'started_at'       => Carbon::parse('2024-01-15 10:00:00'),
            'ended_at'         => Carbon::parse('2024-01-15 10:00:00'),
            'duration_seconds' => 0,
        ]);

        $this->assertEquals(0, $session->getEffectiveDurationSeconds());
    }

    public function test_session_with_only_pause_no_active_play_time(): void
    {
        // Sesiune care a fost imediat pausată și nu a mai reluat niciodată
        $session = PlaySession::factory()->create([
            'started_at' => Carbon::parse('2024-01-15 10:00:00'),
            'ended_at'   => null,
        ]);

        // Singurul interval e de 0 secunde (pause imediat)
        PlaySessionInterval::create([
            'play_session_id'  => $session->id,
            'started_at'       => Carbon::parse('2024-01-15 10:00:00'),
            'ended_at'         => Carbon::parse('2024-01-15 10:00:00'),
            'duration_seconds' => 0,
        ]);

        $this->assertEquals(0, $session->getEffectiveDurationSeconds());
    }
}
