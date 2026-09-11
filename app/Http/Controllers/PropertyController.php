<?php

namespace App\Http\Controllers;

use App\Actions\Property\SearchPropertiesAction;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\PropertyResource;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index(SearchRequest $request, SearchPropertiesAction $action)
    {
        $properties = $action->execute($request->validated());

        return PropertyResource::collection($properties);
    }
}
