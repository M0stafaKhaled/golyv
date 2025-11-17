<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('seat_id');

            $table->unsignedBigInteger('from_station_id');
            $table->unsignedBigInteger('to_station_id');

            $table->unsignedInteger('from_order');
            $table->unsignedInteger('to_order');

            $table->unsignedBigInteger('user_id');

            $table->timestamps();

            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->foreign('seat_id')->references('id')->on('bus_seats')->onDelete('cascade');
            $table->foreign('from_station_id')->references('id')->on('stations')->onDelete('cascade');
            $table->foreign('to_station_id')->references('id')->on('stations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
