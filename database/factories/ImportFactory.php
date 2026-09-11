<?php

namespace Database\Factories;

use App\Models\Import;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Import>
 */
class ImportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'external_import_id' => 'import-'.fake()->date().'-'.fake()->numberBetween(1, 100),
            'sent_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'status' => fake()->randomElement(['pending', 'processing', 'completed', 'failed']),
            'total_offers' => fake()->numberBetween(5, 100),
            'processed_offers' => 0,
            'error' => null,
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'processed_offers' => 100,
            'completed_at' => now(),
        ]);
    }
}
