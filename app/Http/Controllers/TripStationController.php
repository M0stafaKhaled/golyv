<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Station;
use App\Models\TripStation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class TripStationController extends Controller
{
    public function index(Trip $trip)
    {
        $stations = $trip->tripStations()->with('station')->orderBy('order')->get();
        return response()->json($stations);
    }

    public function store(Request $request, Trip $trip)
    {
        $data = $request->validate([
            'station_id' => ['required', 'exists:stations,id', Rule::unique('trip_stations', 'station_id')->where(fn ($q) => $q->where('trip_id', $trip->id))],
            'order' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('trip_stations', 'order')->where(fn ($q) => $q->where('trip_id', $trip->id)),
            ],
        ]);

        if (empty($data['order'])) {
            $max = $trip->tripStations()->max('order');
            $data['order'] = ($max ?? 0) + 1;
        }

        $data['trip_id'] = $trip->id;

        $tripStation = TripStation::create($data);
        return response()->json($tripStation->load('station'), 201);
    }

    public function update(Request $request, Trip $trip, Station $station)
    {
        $tripStation = TripStation::where('trip_id', $trip->id)
            ->where('station_id', $station->id)
            ->firstOrFail();

        $data = $request->validate([
            'station_id' => [
                'sometimes',
                'required',
                'exists:stations,id',
                Rule::unique('trip_stations', 'station_id')
                    ->where(fn ($q) => $q->where('trip_id', $trip->id))
                    ->ignore($tripStation->id),
            ],
            'order' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                Rule::unique('trip_stations', 'order')
                    ->where(fn ($q) => $q->where('trip_id', $trip->id))
                    ->ignore($tripStation->id),
            ],
        ]);

        $tripStation->update($data);
        return response()->json($tripStation->load('station'));
    }

    public function destroy(Trip $trip, Station $station)
    {
        $tripStation = TripStation::where('trip_id', $trip->id)
            ->where('station_id', $station->id)
            ->firstOrFail();

        $tripStation->delete();
        return response()->json(null, 204);
    }

    public function reorder(Request $request, Trip $trip)
    {
        $payload = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:trip_stations,id'],
            'items.*.order' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($payload, $trip) {
            foreach ($payload['items'] as $item) {
                TripStation::where('id', $item['id'])->where('trip_id', $trip->id)->update([
                    'order' => $item['order'],
                ]);
            }
        });

        $stations = $trip->tripStations()->with('station')->orderBy('order')->get();
        return response()->json($stations);
    }
}
