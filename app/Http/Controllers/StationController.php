<?php

namespace App\Http\Controllers;

use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StationController extends Controller
{
    public function index()
    {
        return response()->json(Station::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:stations,name'],
        ]);

        $station = Station::create($data);
        return response()->json($station, 201);
    }

    public function show(Station $station)
    {
        return response()->json($station);
    }

    public function update(Request $request, Station $station)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('stations', 'name')->ignore($station->id)],
        ]);

        $station->update($data);
        return response()->json($station);
    }

    public function destroy(Station $station)
    {
        $station->delete();
        return response()->json(null, 204);
    }
}

