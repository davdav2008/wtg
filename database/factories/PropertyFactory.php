<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cities = [
            'Barcelona',
            'Madrid',
            'Paris',
            'London',
            'Berlin',
        ];

        return [
            'code' => strtoupper(fake()->city()).'-'.fake()->unique()->numberBetween(1, 9999),
            'name' => fake()->sentence(3),
            'city' => fake()->randomElement($cities),
        ];
    }
}
