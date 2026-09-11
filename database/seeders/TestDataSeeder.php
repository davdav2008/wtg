<?php

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\Property;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $supplierA = Supplier::query()->where('name', 'supplier-a')->firstOrFail();

        // Two properties
        $bcn = Property::query()->firstOrCreate(
            ['code' => 'BCN-0001'],
            ['name' => 'Apartment near Sagrada Familia', 'city' => 'Barcelona']
        );
        $madrid = Property::query()->firstOrCreate(
            ['code' => 'MAD-0001'],
            ['name' => 'Apartment in Madrid Center', 'city' => 'Madrid']
        );

        // Cheaper offer for Barcelona
        Offer::query()->updateOrCreate(
            ['supplier_id' => $supplierA->id, 'external_id' => 'offer-a-10001'],
            [
                'property_id' => $bcn->id,
                'check_in' => '2026-10-10',
                'check_out' => '2026-10-15',
                'max_guests' => 4,
                'price' => 50000,
                'currency' => 'EUR',
                'available_units' => 2,
                'expires_at' => now()->addDays(30),
            ]
        );

        // More expensive offer for Barcelona
        Offer::query()->updateOrCreate(
            ['supplier_id' => $supplierA->id, 'external_id' => 'offer-a-10002'],
            [
                'property_id' => $bcn->id,
                'check_in' => '2026-10-10',
                'check_out' => '2026-10-15',
                'max_guests' => 4,
                'price' => 72500,
                'currency' => 'EUR',
                'available_units' => 2,
                'expires_at' => now()->addDays(30),
            ]
        );

        // Offer for Madrid
        Offer::query()->updateOrCreate(
            ['supplier_id' => $supplierA->id, 'external_id' => 'offer-a-20001'],
            [
                'property_id' => $madrid->id,
                'check_in' => '2026-10-10',
                'check_out' => '2026-10-15',
                'max_guests' => 4,
                'price' => 60000,
                'currency' => 'EUR',
                'available_units' => 1,
                'expires_at' => now()->addDays(30),
            ]
        );
    }
}
