<?php

namespace Database\Factories;

use App\Models\Offer;
use App\Models\Property;
use App\Models\Supplier;
use DateMalformedStringException;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     * @throws DateMalformedStringException
     */
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('+1 week', '+1 month');
        $checkOut = clone $checkIn;
        $checkOut->modify('+' . fake()->numberBetween(1, 7) . ' days');

        return [
            'property_id' => Property::factory(),
            'supplier_id' => Supplier::factory(),
            'external_id' => 'offer-' . fake()->unique()->numberBetween(10000, 99999),
            'check_in' => $checkIn->format('Y-m-d'),
            'check_out' => $checkOut->format('Y-m-d'),
            'max_guests' => fake()->numberBetween(1, 8),
            'price' => fake()->numberBetween(50, 500) * 100, // In cents
            'currency' => 'EUR',
            'available_units' => fake()->numberBetween(0, 5),
            'expires_at' => fake()->dateTimeBetween('+1 day', '+2 weeks'),
        ];
    }
}
