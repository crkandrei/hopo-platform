<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoucherFactory extends Factory
{
    protected $model = Voucher::class;

    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'code' => strtoupper($this->faker->unique()->regexify('[A-Z0-9]{8}')),
            'type' => $this->faker->randomElement(['amount', 'hours']),
            'initial_value' => $this->faker->randomFloat(2, 10, 200),
            'remaining_value' => $this->faker->randomFloat(2, 0, 200),
            'expires_at' => $this->faker->optional(0.7)->dateTimeBetween('now', '+1 year'),
            'is_active' => true,
            'created_by' => null,
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    public function amount(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'amount',
            'initial_value' => $attributes['initial_value'] ?? 50,
            'remaining_value' => $attributes['remaining_value'] ?? 50,
        ]);
    }

    public function hours(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'hours',
            'initial_value' => $attributes['initial_value'] ?? 2,
            'remaining_value' => $attributes['remaining_value'] ?? 2,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
