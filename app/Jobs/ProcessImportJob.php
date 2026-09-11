<?php

namespace App\Jobs;

use App\Models\Import;
use App\Models\Offer;
use App\Models\Property;
use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $importId,
        public array $offers,
    ) {}

    public function handle(): void
    {
        $import = Import::query()->find($this->importId);

        if (! $import) {
            return;
        }

        $import->update(['status' => 'processing']);

        $supplier = Supplier::query()->find($import->supplier_id);
        $processed = 0;
        $failures = [];

        foreach ($this->offers as $offerData) {
            try {
                DB::transaction(function () use ($offerData, $supplier) {
                    $this->processOffer($offerData, $supplier);
                });
                $processed++;
            } catch (\Throwable $e) {
                $externalId = Arr::get($offerData, 'external_id', '(unknown)');
                $failures[] = "Offer {$externalId}: {$e->getMessage()}";
                report($e);
            }
        }

        $import->update([
            'status' => $processed > 0 ? 'completed' : 'failed',
            'processed_offers' => $processed,
            'completed_at' => now(),
            'error' => $failures ? implode("\n", $failures) : null,
        ]);
    }

    protected function processOffer(array $data, Supplier $supplier): void
    {
        $property = Property::query()->firstOrCreate(
            ['code' => Arr::get($data, 'property.code')],
            [
                'name' => Arr::get($data, 'property.name'),
                'city' => Arr::get($data, 'property.city'),
            ]
        );

        $offer = Offer::query()->firstOrNew([
            'supplier_id' => $supplier->id,
            'external_id' => Arr::get($data, 'external_id'),
        ]);

        $offer->fill([
            'property_id' => $property->id,
            'check_in' => Arr::get($data, 'check_in'),
            'check_out' => Arr::get($data, 'check_out'),
            'max_guests' => Arr::get($data, 'max_guests'),
            'price' => Arr::get($data, 'price'),
            'currency' => Arr::get($data, 'currency'),
            'available_units' => Arr::get($data, 'available_units'),
            'expires_at' => Arr::get($data, 'expires_at'),
        ]);

        $offer->save();
    }

    public function failed(\Throwable $e): void
    {
        Import::query()->where('id', $this->importId)->update([
            'status' => 'failed',
            'error' => $e->getMessage(),
            'completed_at' => now(),
        ]);
    }
}
