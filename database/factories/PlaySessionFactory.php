<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\Location;
use App\Models\PlaySession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlaySession>
 */
class PlaySessionFactory extends Factory
{
    protected $model = PlaySession::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'child_id' => Child::factory(),
            'bracelet_code' => 'BRACELET' . $this->faker->unique()->numerify('####'),
            'started_at' => now()->subHours(2),
            'ended_at' => null,
            'calculated_price' => null,
            'price_per_hour_at_calculation' => null,
            'paid_at' => null,
            'voucher_hours' => null,
            'payment_status' => null,
            'payment_method' => null,
        ];
    }
}
