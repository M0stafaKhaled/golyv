<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Exists;

class BookSeatData extends Data
{
    public function __construct(

        #[Required, Exists('trips', 'id')]
        public int $trip_id,

        #[Required, Exists('bus_seats', 'id')]
        public int $seat_id,

        #[Required, Exists('stations', 'id')]
        public int $from_station_id,

        #[Required, Exists('stations', 'id')]
        public int $to_station_id,

        #[Required, Exists('users', 'id')]
        public int $user_id,

        public ?int $from_order = null,

        public ?int $to_order = null,
    ) {}


    public static function casts(): array
    {
        return [
            'trip_id'         => 'integer',
            'seat_id'         => 'integer',
            'from_station_id' => 'integer',
            'to_station_id'   => 'integer',
            'user_id'         => 'integer',
            'from_order'      => 'integer',
            'to_order'        => 'integer',
        ];
    }
}
