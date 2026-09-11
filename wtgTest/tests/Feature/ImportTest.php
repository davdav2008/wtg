<?php

namespace Tests\Feature;

use App\Jobs\ProcessImportJob;
use App\Models\Import;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Supplier::create(['name' => 'supplier-a']);
    }

    public function test_can_submit_import()
    {
        Queue::fake();

        $payload = [
            'supplier' => 'supplier-a',
            'external_import_id' => 'import-001',
            'sent_at' => '2026-09-01T10:00:00Z',
            'offers' => [
                [
                    'external_id' => 'offer-1',
                    'property' => [
                        'code' => 'P1',
                        'name' => 'Property 1',
                        'city' => 'Barcelona',
                    ],
                    'check_in' => '2026-10-10',
                    'check_out' => '2026-10-15',
                    'max_guests' => 4,
                    'price' => 72500,
                    'currency' => 'EUR',
                    'available_units' => 2,
                    'expires_at' => '2026-09-10T23:59:59Z',
                ]
            ],
        ];

        $response = $this->postJson('/api/imports', $payload);

        $response->assertStatus(202)
            ->assertJsonStructure([
                'data' => ['id', 'status']
            ]);

        $this->assertDatabaseHas('imports', [
            'external_import_id' => 'import-001',
            'status' => 'pending',
        ]);

        Queue::assertPushed(ProcessImportJob::class);
    }

    public function test_import_is_idempotent()
    {
        Queue::fake();

        $payload = [
            'supplier' => 'supplier-a',
            'external_import_id' => 'import-001',
            'sent_at' => '2026-09-01T10:00:00Z',
            'offers' => [
                [
                    'external_id' => 'o1',
                    'property' => ['code'=>'c','name'=>'n','city'=>'c'],
                    'check_in'=>'2026-10-10',
                    'check_out'=>'2026-10-15',
                    'max_guests'=>1,
                    'price'=>100,
                    'currency'=>'EUR',
                    'available_units'=>1,
                    'expires_at'=>'2026-12-01T00:00:00Z'
                ]
            ],
        ];

        $this->postJson('/api/imports', $payload)->assertStatus(202);
        $this->postJson('/api/imports', $payload)->assertStatus(202);

        $this->assertEquals(1, Import::count());
        Queue::assertPushed(ProcessImportJob::class, 1);
    }

    public function test_can_get_import_status()
    {
        $supplier = Supplier::where('name', 'supplier-a')->first();
        $import = Import::create([
            'supplier_id' => $supplier->id,
            'external_import_id' => 'imp-123',
            'sent_at' => now(),
            'status' => 'completed',
            'total_offers' => 10,
            'processed_offers' => 10,
        ]);

        $response = $this->getJson("/api/imports/{$import->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.external_import_id', 'imp-123');
    }

    public function test_import_job_processes_offers()
    {
        $payload = [
            'supplier' => 'supplier-a',
            'external_import_id' => 'import-002',
            'sent_at' => '2026-09-01T10:00:00Z',
            'offers' => [
                [
                    'external_id' => 'offer-a-1',
                    'property' => [
                        'code' => 'BCN-001',
                        'name' => 'Apartment 1',
                        'city' => 'Barcelona',
                    ],
                    'check_in' => '2026-10-10',
                    'check_out' => '2026-10-15',
                    'max_guests' => 4,
                    'price' => 72500,
                    'currency' => 'EUR',
                    'available_units' => 2,
                    'expires_at' => '2026-09-10T23:59:59Z',
                ]
            ],
        ];

        $response = $this->postJson('/api/imports', $payload);
        $importId = $response->json('data.id');

        (new ProcessImportJob($importId, $payload['offers']))->handle();

        $this->assertDatabaseHas('imports', [
            'id' => $importId,
            'status' => 'completed',
            'processed_offers' => 1,
        ]);

        $this->assertDatabaseHas('properties', ['code' => 'BCN-001']);
        $this->assertDatabaseHas('offers', ['external_id' => 'offer-a-1', 'price' => 72500]);
    }
}
