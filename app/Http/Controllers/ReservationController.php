<?php

namespace App\Http\Controllers;

use App\Actions\Reservation\CreateReservationAction;
use App\Http\Requests\ReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Offer;

class ReservationController extends Controller
{
    public function store(
        ReservationRequest $request,
        Offer $offer,
        CreateReservationAction $action
    ) {
        $reservation = $action->execute($offer, $request->validated());

        return (new ReservationResource($reservation))
            ->response()
            ->setStatusCode(201);
    }
}
