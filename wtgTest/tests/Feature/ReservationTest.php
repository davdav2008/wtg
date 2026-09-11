<?php

namespace Tests\Feature;

use App\Models\Offer;
use App\Models\Property;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_reserve_offer()
    {
        $supplier = Supplier::create(['name' => 'supplier-a']);
        $property = Property::create(['code' => 'BCN-001', 'name' => 'Sagrada Apartment', 'city' => 'Barcelona']);
        $offer = Offer::create([
            'property_id' => $property->id,
            'supplier_id' => $supplier->id,
            'external_id' => 'off-1',
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
            'max_guests' => 4,
            'price' => 100,
            'currency' => 'EUR',
            'available_units' => 1,
            'expires_at' => now()->addDays(10),
        ]);

        $payload = [
            'client_reference' => 'ref-123',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
        ];

        $response = $this->postJson("/api/offers/{$offer->id}/reservations", $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reservations', ['client_reference' => 'ref-123']);
        $this->assertEquals(0, $offer->fresh()->available_units);
    }

    public function test_cannot_reserve_if_no_units()
    {
        $supplier = Supplier::create(['name' => 'supplier-a']);
        $property = Property::create(['code' => 'BCN-001', 'name' => 'Sagrada Apartment', 'city' => 'Barcelona']);
        $offer = Offer::create([
            'property_id' => $property->id,
            'supplier_id' => $supplier->id,
            'external_id' => 'off-1',
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
            'max_guests' => 4,
            'price' => 100,
            'currency' => 'EUR',
            'available_units' => 0,
            'expires_at' => now()->addDays(10),
        ]);

        $payload = [
            'client_reference' => 'ref-123',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
        ];

        $response = $this->postJson("/api/offers/{$offer->id}/reservations", $payload);

        $response->assertStatus(422);
    }
}
