<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement(['START', 'STANDARD', 'PRO', 'ENTERPRISE']);

        return [
            'name'             => $name,
            'slug'             => strtolower($name),
            'price'            => $this->faker->randomElement([99, 149, 199, 299]),
            'duration_months'  => $this->faker->randomElement([1, 3, 6, 12]),
            'stripe_product_id' => null,
            'stripe_price_id'  => null,
            'features'         => ['Acces complet', 'Suport email'],
            'is_active'        => true,
            'sort_order'       => 0,
        ];
    }
}
