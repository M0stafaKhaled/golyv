<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\BusSeat;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusSeatController extends Controller
{
    public function index(Bus $bus)
    {
        return response()->json($bus->seats()->orderBy('seat_number')->get());
    }

    public function store(Request $request, Bus $bus)
    {
        $data = $request->validate([
            'seat_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('bus_seats', 'seat_number')->where(fn ($q) => $q->where('bus_id', $bus->id)),
            ],
        ]);

        $data['bus_id'] = $bus->id;
        $seat = BusSeat::create($data);
        return response()->json($seat, 201);
    }

    public function show(BusSeat $seat)
    {
        return response()->json($seat->load('bus'));
    }

    public function update(Request $request, BusSeat $seat)
    {
        $data = $request->validate([
            'seat_number' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                Rule::unique('bus_seats', 'seat_number')
                    ->where(fn ($q) => $q->where('bus_id', $seat->bus_id))
                    ->ignore($seat->id),
            ],
        ]);

        $seat->update($data);
        return response()->json($seat);
    }

    public function destroy(BusSeat $seat)
    {
        $seat->delete();
        return response()->json(null, 204);
    }
}
