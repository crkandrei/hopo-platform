<?php
// database/factories/LocationBridgeFactory.php

namespace Database\Factories;

use App\Models\Location;
use App\Models\LocationBridge;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationBridgeFactory extends Factory
{
    protected $model = LocationBridge::class;

    public function definition(): array
    {
        return [
            'location_id'    => Location::factory(),
            'api_key'        => bin2hex(random_bytes(32)),
            'client_id'      => null,
            'status'         => 'never_connected',
            'version'        => null,
            'mode'           => null,
            'last_seen_at'   => null,
            'last_print_at'  => null,
            'print_count'    => 0,
            'z_report_count' => 0,
            'error_count'    => 0,
            'uptime'         => null,
        ];
    }

    public function online(): static
    {
        return $this->state([
            'status'       => 'online',
            'last_seen_at' => now(),
        ]);
    }

    public function offline(): static
    {
        return $this->state([
            'status'       => 'offline',
            'last_seen_at' => now()->subMinutes(5),
        ]);
    }
}
