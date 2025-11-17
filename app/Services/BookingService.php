<?php

namespace App\Services;

use App\Data\BookSeatData;
use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\BusSeat;
use App\Models\Trip;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function book(BookSeatData $data): Booking
    {
        return DB::transaction(function () use ($data) {

            // Load tripStations instead of stations
            $trip = Trip::with('tripStations')
                ->lockForUpdate()
                ->findOrFail($data->trip_id);

            // Correct way to get station orders
            $fromOrder = optional(
                $trip->tripStations->firstWhere('station_id', $data->from_station_id)
            )->order;

            $toOrder = optional(
                $trip->tripStations->firstWhere('station_id', $data->to_station_id)
            )->order;

            if ($fromOrder === null || $toOrder === null || $toOrder <= $fromOrder) {
                throw new BookingException('Invalid station range.');
            }

            $seat = BusSeat::with('bus')
                ->lockForUpdate()
                ->findOrFail($data->seat_id);

            if ($seat->bus->trip_id !== $trip->id) {
                throw new BookingException('Seat does not belong to this trip.');
            }

            $overlapExists = Booking::where('trip_id', $trip->id)
                ->where('seat_id', $seat->id)
                ->lockForUpdate()
                ->whereRaw('(from_order < ? AND to_order > ?)', [$toOrder, $fromOrder])
                ->exists();

            if ($overlapExists) {
                throw new BookingException('Seat already booked in this segment.');
            }

            $data->from_order = $fromOrder;
            $data->to_order   = $toOrder;

            return Booking::create($data->toArray());
        });
    }
}
