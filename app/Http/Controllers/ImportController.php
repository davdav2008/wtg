<?php

namespace App\Http\Controllers;

use App\Actions\Import\CreateImportAction;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\ImportResource;
use App\Models\Import;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function store(ImportRequest $request, CreateImportAction $action)
    {
        $import = $action->execute($request->validated());

        return (new ImportResource($import))
            ->response()
            ->setStatusCode(202);
    }

    public function show(Import $import)
    {
        return new ImportResource($import);
    }
}
