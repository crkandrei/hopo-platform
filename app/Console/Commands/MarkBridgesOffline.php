<?php

namespace App\Console\Commands;

use App\Models\LocationBridge;
use Illuminate\Console\Command;

class MarkBridgesOffline extends Command
{
    protected $signature = 'bridges:mark-offline';
    protected $description = 'Mark bridge connections as offline if last_seen_at is older than 90 seconds';

    public function handle(): void
    {
        $cutoff = now()->subSeconds(90);

        $count = LocationBridge::where('status', '!=', 'offline')
            ->whereNotNull('last_seen_at')
            ->where('last_seen_at', '<', $cutoff)
            ->update(['status' => 'offline']);

        if ($count > 0) {
            $this->info("Marked {$count} bridge(s) offline.");
        }
    }
}
