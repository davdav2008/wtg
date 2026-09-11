<?php

namespace Tests\Feature;

use App\Models\Offer;
use App\Models\Property;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_search_properties()
    {
        $supplier = Supplier::create(['name' => 'supplier-a']);
        $property = Property::create(['code' => 'BCN-001', 'name' => 'Sagrada Apartment', 'city' => 'Barcelona']);

        // Correct offer
        Offer::create([
            'property_id' => $property->id,
            'supplier_id' => $supplier->id,
            'external_id' => 'off-1',
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
            'max_guests' => 4,
            'price' => 100,
            'currency' => 'EUR',
            'available_units' => 2,
            'expires_at' => now()->addDays(10),
        ]);

        // Cheaper offer for same property
        Offer::create([
            'property_id' => $property->id,
            'supplier_id' => $supplier->id,
            'external_id' => 'off-2',
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
            'max_guests' => 4,
            'price' => 80,
            'currency' => 'EUR',
            'available_units' => 1,
            'expires_at' => now()->addDays(10),
        ]);

        // Cheaper offer but expired
        Offer::create([
            'property_id' => $property->id,
            'supplier_id' => $supplier->id,
            'external_id' => 'off-3',
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
            'max_guests' => 4,
            'price' => 50,
            'currency' => 'EUR',
            'available_units' => 1,
            'expires_at' => now()->subDay(),
        ]);

        // Cheaper offer but not enough guests
        Offer::create([
            'property_id' => $property->id,
            'supplier_id' => $supplier->id,
            'external_id' => 'off-4',
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
            'max_guests' => 1,
            'price' => 60,
            'currency' => 'EUR',
            'available_units' => 1,
            'expires_at' => now()->addDays(10),
        ]);

        $response = $this->getJson('/api/properties?city=Barcelona&check_in=2026-10-10&check_out=2026-10-15&guests=2');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.best_offer.price', 80)
            ->assertJsonPath('data.0.code', 'BCN-001')
            ->assertJsonStructure([
                'data',
                'next',
                'prev',
                'per_page',
            ]);
    }
}
