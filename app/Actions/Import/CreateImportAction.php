<?php

namespace App\Actions\Import;

use App\Jobs\ProcessImportJob;
use App\Models\Import;
use App\Models\Supplier;
use Illuminate\Support\Arr;

class CreateImportAction
{
    public function execute(array $payload): Import
    {
        $supplier = Supplier::query()->where('name', Arr::get($payload, 'supplier'))->firstOrFail();

        $import = Import::query()->firstOrCreate(
            [
                'supplier_id'        => $supplier->id,
                'external_import_id' => Arr::get($payload, 'external_import_id'),
            ],
            [
                'sent_at'          => Arr::get($payload, 'sent_at'),
                'status'           => 'pending',
                'total_offers'     => count(Arr::get($payload, 'offers')),
                'processed_offers' => 0,
            ]
        );

        if ($import->wasRecentlyCreated) {
            ProcessImportJob::dispatch($import->id, Arr::get($payload, 'offers'));
        }

        return $import;
    }
}
