<?php

namespace App\Actions\Reservation;

use App\Exceptions\OfferNotAvailableException;
use App\Models\Offer;
use App\Models\Reservation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateReservationAction
{
    public function execute(Offer $offer, array $payload): Reservation
    {
        return DB::transaction(function () use ($offer, $payload) {
            $affected = DB::table('offers')
                ->where('id', $offer->id)
                ->where('available_units', '>', 0)
                ->decrement('available_units');

            if ($affected === 0) {
                throw new OfferNotAvailableException('No units available for this offer.');
            }

            return Reservation::query()->create([
                'offer_id' => $offer->id,
                'client_reference' => Arr::get($payload, 'client_reference'),
                'customer_name' => Arr::get($payload, 'customer_name'),
                'customer_email' => Arr::get($payload, 'customer_email'),
            ]);
        });
    }
}
