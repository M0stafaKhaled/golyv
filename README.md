# Golyv – Bus Trips & Seat Booking API (Laravel)

Repository: https://github.com/M0stafaKhaled/golyv

A clean Laravel API that manages inter-station trips, ordered trip stops, buses and seats, with safe seat booking between two stations on a trip and querying available seats for a segment.

## Key Features

- Stations CRUD (Admin)
- Trips CRUD (start/end stations) with ordered intermediate stations
- Manage trip stations order (add/update/remove/reorder)
- Buses CRUD per trip
- Seats CRUD per bus
- Book a seat between two stations on a specific trip (conflict-safe)
- Query available seats for a trip segment
- SQLite ready for local development (zero config)

## Tech Stack

- PHP 8.2+, Laravel (Framework)
- Database: SQLite (default), can switch to MySQL/Postgres
- Dev tooling: Artisan, Composer, Vite (optional)

## Project Structure (high level)

- app/Models: Station, Trip, TripStation, Bus, BusSeat, Booking
- app/Http/Controllers: StationController, TripController, TripStationController, BusController, BusSeatController, BookingController
- app/Services: BookingService, AvailableSeatsService
- app/Data: BookSeatData, AvailableSeatsData
- routes/api.php: All API routes

## Data Model (relations & constraints)

- Station has many TripStations; belongsToMany Trips through trip_stations (with pivot `order`)
- Trip belongsTo startStation, endStation (Station); hasMany TripStations (ordered), hasMany Buses
- TripStation belongsTo Trip and Station
  - Unique per trip: (trip_id, station_id)
  - Unique station order per trip: (trip_id, order)
- Bus belongsTo Trip; hasMany BusSeats
- BusSeat belongsTo Bus
  - Unique seat per bus: (bus_id, seat_number)
- Booking belongsTo Trip and BusSeat
  - Stores from_order and to_order derived from the trip stations ordering

## Booking Logic (conflict-safe)

- When booking, the system locks records in a DB transaction, derives `from_order` and `to_order` from the trip’s station ordering, and rejects overlaps using strict segment overlap:
  - Overlap condition: (existing.from_order < new.to_order) AND (existing.to_order > new.from_order)
- Prevents double-booking the same seat across overlapping segments.

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- SQLite (bundled), or MySQL/Postgres if preferred
- Node.js (optional; not needed for API only)

### Installation

1) Clone
- git clone https://github.com/M0stafaKhaled/golyv.git
- cd golyv

2) Install dependencies
- composer install

3) Configure environment
- cp .env.example .env
- Set APP_KEY and database
  - php artisan key:generate
  - For SQLite (recommended local):
    - In .env: DB_CONNECTION=sqlite
    - Ensure DB_DATABASE points to database/database.sqlite (or comment host/user/pass)
    - Create the file:
      - Windows PowerShell: New-Item -ItemType File .\database\database.sqlite -Force
      - CMD: type nul > database\database.sqlite

4) Migrate and seed
- php artisan migrate --seed

5) Run the server
- php artisan serve
- API base URL: http://127.0.0.1:8000/api

## API Overview

Unless stated, all endpoints are public for local development. The /api/user route uses Sanctum if you enable auth.

### Stations

- GET    /api/stations
- POST   /api/stations { name }
- GET    /api/stations/{station}
- PUT    /api/stations/{station} { name }
- DELETE /api/stations/{station}

### Trips

- GET    /api/trips
- POST   /api/trips { name, start_station_id, end_station_id, stations?: [ { station_id, order? } ] }
- GET    /api/trips/{trip}
- PUT    /api/trips/{trip} { name?, start_station_id?, end_station_id? }
- DELETE /api/trips/{trip}

Notes:
- On create, you may pass an optional stations array to initialize trip stops and their order. If order is omitted it auto-increments.

### Trip Stations (manage stops for a trip)

- GET    /api/trips/{trip}/stations
- POST   /api/trips/{trip}/stations { station_id, order? }
- PUT    /api/trips/{trip}/stations/{station} { station_id?, order? }
- DELETE /api/trips/{trip}/stations/{station}
- POST   /api/trips/{trip}/stations/reorder { items: [ { id, order }, ... ] }

### Buses

- GET    /api/trips/{trip}/buses
- POST   /api/trips/{trip}/buses { total_seats }
- GET    /api/buses/{bus}
- PUT    /api/buses/{bus} { total_seats? }
- DELETE /api/buses/{bus}

### Bus Seats

- GET    /api/buses/{bus}/seats
- POST   /api/buses/{bus}/seats { seat_number }
- GET    /api/seats/{seat}
- PUT    /api/seats/{seat} { seat_number? }
- DELETE /api/seats/{seat}

### Booking

- POST   /api/book-seat
  - Body: { trip_id, seat_id, from_station_id, to_station_id, user_id? }
  - Response: { message, booking }
- GET    /api/trips/{trip}/available-seats?from_station_id=..&to_station_id=..
  - Response: { message, seats: [ ...bus seats not overlapping segment... ] }

## Example Requests

Create a station
- curl -X POST http://127.0.0.1:8000/api/stations -H "Content-Type: application/json" -d '{"name":"Cairo"}'

Create a trip
- curl -X POST http://127.0.0.1:8000/api/trips -H "Content-Type: application/json" -d '{"name":"Cairo-Alex","start_station_id":1,"end_station_id":2,"stations":[{"station_id":1,"order":1},{"station_id":3,"order":2},{"station_id":2,"order":3}]}'

Add a bus for the trip
- curl -X POST http://127.0.0.1:8000/api/trips/1/buses -H "Content-Type: application/json" -d '{"total_seats":12}'

Create seats for a bus (repeat with different seat_number)
- curl -X POST http://127.0.0.1:8000/api/buses/1/seats -H "Content-Type: application/json" -d '{"seat_number":1}'

Check available seats for a segment
- curl "http://127.0.0.1:8000/api/trips/1/available-seats?from_station_id=1&to_station_id=3"

Book a seat for a segment
- curl -X POST http://127.0.0.1:8000/api/book-seat -H "Content-Type: application/json" -d '{"trip_id":1,"seat_id":1,"from_station_id":1,"to_station_id":3}'

## Error Handling

- Validation: 422 with validation errors
- Business rules (e.g., invalid station range, overlap seat booking): 422 with error message

## Development Tips

- List routes: php artisan route:list
- Clear caches if classes/routes change: composer dump-autoload && php artisan optimize:clear (run commands separately in PowerShell)
- Factories and seeders are available for quick sample data

## Switching Databases

- For MySQL/Postgres, update .env DB_* variables and run php artisan migrate --seed

## License

- See repository for license details. If none provided, assume internal use.

