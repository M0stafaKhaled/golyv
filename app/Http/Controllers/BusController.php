<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\Trip;
use Illuminate\Http\Request;

class BusController extends Controller
{
    public function index(Trip $trip)
    {
        return response()->json($trip->buses()->with('seats')->orderBy('id', 'desc')->get());
    }

    public function store(Request $request, Trip $trip)
    {
        $data = $request->validate([
            'total_seats' => ['required', 'integer', 'min:1'],
        ]);

        $bus = $trip->buses()->create($data);
        return response()->json($bus->load('trip'), 201);
    }

    public function show(Bus $bus)
    {
        return response()->json($bus->load(['trip', 'seats']));
    }

    public function update(Request $request, Bus $bus)
    {
        $data = $request->validate([
            'total_seats' => ['sometimes', 'required', 'integer', 'min:1'],
        ]);

        $bus->update($data);
        return response()->json($bus->load(['trip', 'seats']));
    }

    public function destroy(Bus $bus)
    {
        $bus->delete();
        return response()->json(null, 204);
    }
}
