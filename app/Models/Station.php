<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{

    use HasFactory;
    // Allow mass assignment for name
    protected $fillable = ['name'];

    // Relationships
    public function tripStations()
    {
        return $this->hasMany(TripStation::class);
    }

    public function trips()
    {
        return $this->belongsToMany(Trip::class, 'trip_stations')
            ->withPivot('order')
            ->orderByPivot('order');
    }
}
