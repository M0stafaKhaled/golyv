<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{

    protected $fillable = [
        'trip_id',
        'seat_id',
        'from_station_id',
        'to_station_id',
        'from_order',
        'to_order',
        'user_id'
    ];

    public function seat()
    {
        return $this->belongsTo(BusSeat::class, 'seat_id');
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

}
