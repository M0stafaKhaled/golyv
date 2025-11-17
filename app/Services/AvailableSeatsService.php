<?php

namespace App\Services;

use App\Data\AvailableSeatsData;
use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\BusSeat;
use App\Models\Trip;

class AvailableSeatsService
{
    public function handle(AvailableSeatsData $data)
    {
        $trip = Trip::with('stations')->findOrFail($data->trip_id);

        // FIX: Must check pivot fields
        $fromPivot = $trip->stations->firstWhere('pivot.station_id', $data->from_station_id);
        $toPivot   = $trip->stations->firstWhere('pivot.station_id', $data->to_station_id);

        $fromOrder = $fromPivot->pivot->order ?? null;
        $toOrder   = $toPivot->pivot->order ?? null;

        if ($fromOrder === null || $toOrder === null || $toOrder <= $fromOrder) {
            throw new BookingException('Invalid station range.');
        }

        $bookedSeatIds = Booking::where('trip_id', $trip->id)
            ->whereRaw('(from_order < ? AND to_order > ?)', [$toOrder, $fromOrder])
            ->pluck('seat_id');

        return BusSeat::with('bus')
            ->whereHas('bus', fn ($q) => $q->where('trip_id', $trip->id))
            ->whereNotIn('id', $bookedSeatIds)
            ->orderBy('seat_number')
            ->get();
    }
}
