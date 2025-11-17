<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripStation extends Model
{
    use HasFactory;
    protected $table = 'trip_stations'; // Explicit table name

    protected $fillable = [
        'trip_id',
        'station_id',
        'order',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function station()
    {
        return $this->belongsToMany(Station::class, 'trip_stations')
            ->withPivot('order')
            ->orderBy('pivot_order');
    }
}
