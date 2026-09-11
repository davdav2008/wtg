<?php

namespace App\Actions\Property;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SearchPropertiesAction
{
    public function execute(array $filters): LengthAwarePaginator
    {
        $city = Arr::get($filters, 'city');
        $checkIn = Arr::get($filters, 'check_in');
        $checkOut = Arr::get($filters, 'check_out');
        $guests = Arr::get($filters, 'guests');
        $perPage = Arr::get($filters, 'per_page', 15);

        $subquery = DB::table('properties as p')
            ->join('offers as o', 'o.property_id', '=', 'p.id')
            ->join('suppliers as s', 's.id', '=', 'o.supplier_id')
            ->select([
                'p.code',
                'p.name',
                'p.city',
                'o.id as offer_id',
                's.name as supplier_name',
                'o.price',
                'o.currency',
                'o.available_units',
                'o.expires_at',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY p.id ORDER BY o.price ASC, o.id ASC) as rn'),
            ])
            ->whereDate('o.check_in', $checkIn)
            ->whereDate('o.check_out', $checkOut)
            ->where('o.max_guests', '>=', $guests)
            ->where('o.available_units', '>', 0)
            ->where('o.expires_at', '>', now())
            ->when($city, fn ($q) => $q->where('p.city', $city));

        return DB::query()
            ->fromSub($subquery, 'ranked')
            ->where('rn', 1)
            ->orderBy('price', 'asc')
            ->paginate($perPage);
    }
}
