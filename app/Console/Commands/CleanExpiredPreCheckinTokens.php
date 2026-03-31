<?php

namespace App\Console\Commands;

use App\Models\PreCheckinToken;
use Illuminate\Console\Command;

class CleanExpiredPreCheckinTokens extends Command
{
    protected $signature = 'pre-checkin:cleanup';
    protected $description = 'Șterge tokenurile pre-checkin expirate și folosite';

    public function handle(): int
    {
        $expiredCount = PreCheckinToken::where('status', 'pending')
            ->where('expires_at', '<', now()->subHours(24))
            ->delete();

        $usedCount = PreCheckinToken::where('status', 'used')
            ->where('used_at', '<', now()->subDays(7))
            ->delete();

        $this->info("Cleanup complet: {$expiredCount} tokenuri expirate, {$usedCount} tokenuri folosite șterse.");

        return self::SUCCESS;
    }
}
