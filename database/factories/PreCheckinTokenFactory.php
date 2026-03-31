<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\Location;
use App\Models\PreCheckinToken;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PreCheckinTokenFactory extends Factory
{
    protected $model = PreCheckinToken::class;

    public function definition(): array
    {
        return [
            'token' => (string) Str::uuid(),
            'location_id' => Location::factory(),
            'child_id' => Child::factory(),
            'guardian_id' => Guardian::factory(),
            'status' => 'pending',
            'expires_at' => now()->addMinutes(60),
            'used_at' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(['expires_at' => now()->subMinutes(5)]);
    }

    public function used(): static
    {
        return $this->state([
            'status' => 'used',
            'used_at' => now()->subMinutes(2),
        ]);
    }
}
