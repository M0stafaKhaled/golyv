<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Different;

class AvailableSeatsData extends Data
{
    public function __construct(

        #[Required, Exists('stations', 'id')]
        public int $from_station_id,

        #[Required, Exists('stations', 'id'), Different('from_station_id')]
        public int $to_station_id,

        public int $trip_id,
    ) {}
}
