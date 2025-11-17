<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\TripController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StationController;
use App\Http\Controllers\TripStationController;
use App\Http\Controllers\BusController;
use App\Http\Controllers\BusSeatController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




// Stations
Route::apiResource('stations', StationController::class);

// Trips
Route::apiResource('trips', TripController::class);

// Trip Stations (nested)
Route::get('trips/{trip}/stations', [TripStationController::class, 'index']);
Route::post('trips/{trip}/stations', [TripStationController::class, 'store']);
Route::put('trips/{trip}/stations/{station}', [TripStationController::class, 'update']);
Route::delete('trips/{trip}/stations/{station}', [TripStationController::class, 'destroy']);
Route::post('trips/{trip}/stations/reorder', [TripStationController::class, 'reorder']);

// Buses for trip
Route::get('trips/{trip}/buses', [BusController::class, 'index']);
Route::post('trips/{trip}/buses', [BusController::class, 'store']);
Route::get('buses/{bus}', [BusController::class, 'show']);
Route::put('buses/{bus}', [BusController::class, 'update']);
Route::delete('buses/{bus}', [BusController::class, 'destroy']);

// Seats for a bus
Route::get('buses/{bus}/seats', [BusSeatController::class, 'index']);
Route::post('buses/{bus}/seats', [BusSeatController::class, 'store']);
Route::get('seats/{seat}', [BusSeatController::class, 'show']);
Route::put('seats/{seat}', [BusSeatController::class, 'update']);
Route::delete('seats/{seat}', [BusSeatController::class, 'destroy']);

// Booking APIs
Route::post('/book-seat', [BookingController::class, 'book']);
Route::get('/trips/{trip}/available-seats', [BookingController::class, 'available']);
