<?php

namespace App\Http\Controllers;

use App\Data\BookSeatData;
use App\Data\AvailableSeatsData;
use App\Services\BookingService;
use App\Services\AvailableSeatsService;
use Illuminate\Http\Request;
use App\Models\Trip;

class BookingController extends Controller
{
    private $availableSeatsService;
    public function __construct(AvailableSeatsService $availableSeatsService)
    {
        $this->availableSeatsService = $availableSeatsService;
    }

    public function book(Request $request, BookingService $service)
    {
        // Inject logged in user
        $request->merge(['user_id' => auth()->id()]);

        // Build DTO manually (no auto-resolving)
        $data = BookSeatData::from($request);

        try {
            $service->book($data);

            return response()->json([
                'message' => 'Seat booked successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }


    public function available(Request $request, Trip $trip)
    {
        try {
            $data = AvailableSeatsData::from([
                'from_station_id' => $request->get('from_station_id'),
                'to_station_id'   => $request->get('to_station_id'),
                'trip_id'         => $trip->id,
            ]);

            $seats = $this->availableSeatsService->handle($data);

            return response()->json([
                'message' => 'Available seats retrieved successfully',
                'seats'   => $seats
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }

}
