<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Child>
 */
class ChildFactory extends Factory
{
    protected $model = Child::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $namePart = substr($firstName, 0, 2);
        $nextPart = strlen($firstName) > 2 ? substr($firstName, 2, 2) : substr($firstName, 0, 2);
        $internalCode = strtoupper($namePart . $nextPart . rand(100, 999));
        
        return [
            'location_id' => Location::factory(),
            'guardian_id' => Guardian::factory(),
            'name' => $firstName,
            'birth_date' => $this->faker->dateTimeBetween('-10 years', '-2 years'),
            'internal_code' => $internalCode,
            'allergies' => $this->faker->optional()->sentence(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
