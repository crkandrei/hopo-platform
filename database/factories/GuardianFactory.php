<?php

namespace Database\Factories;

use App\Models\Guardian;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guardian>
 */
class GuardianFactory extends Factory
{
    protected $model = Guardian::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'terms_accepted_at' => now(),
            'gdpr_accepted_at' => now(),
            'terms_version' => '1.0',
            'gdpr_version' => '1.0',
        ];
    }
}
