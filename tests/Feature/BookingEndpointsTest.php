<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Trip;
use App\Models\Station;
use App\Models\Bus;
use App\Models\BusSeat;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $trip;
    protected $stations = [];
    protected $bus;
    protected $seats = [];

    public function setUp(): void
    {
        parent::setUp();

        // Create authenticated user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Stations
        $this->stations[] = Station::factory()->create(['name' => 'A']);
        $this->stations[] = Station::factory()->create(['name' => 'B']);
        $this->stations[] = Station::factory()->create(['name' => 'C']);
        $this->stations[] = Station::factory()->create(['name' => 'D']);

        // Trip
        $this->trip = Trip::factory()->create();

        // Attach stations with order
        foreach ($this->stations as $index => $station) {
            $this->trip->stations()->attach($station->id, ['order' => $index + 1]);
        }

        // الحل الحقيقي
        $this->trip->load('stations');

        // Bus + seats
        $this->bus = Bus::factory()->create(['trip_id' => $this->trip->id]);

        for ($i = 1; $i <= 5; $i++) {
            $this->seats[$i] = BusSeat::factory()->create([
                'bus_id' => $this->bus->id,
                'seat_number' => $i,
            ]);
        }
    }


    /** @test */
    public function it_can_book_a_seat_successfully()
    {
        $payload = [
            'trip_id' => $this->trip->id,
            'seat_id' => $this->seats[1]->id,
            'from_station_id' => $this->stations[0]->id, // A
            'to_station_id'   => $this->stations[2]->id, // C
        ];

        $response = $this->postJson('/api/book-seat', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Seat booked successfully'
            ]);

        $this->assertDatabaseHas('bookings', [
            'trip_id' => $this->trip->id,
            'seat_id' => $this->seats[1]->id,
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_prevents_booking_on_overlapping_segment()
    {
        // First booking
        Booking::create([
            'trip_id' => $this->trip->id,
            'seat_id' => $this->seats[1]->id,
            'from_station_id' => $this->stations[0]->id,
            'to_station_id'   => $this->stations[2]->id,
            'from_order' => 1,
            'to_order'   => 3,
            'user_id' => $this->user->id
        ]);

        // Try to book overlapping (B → D)
        $payload = [
            'trip_id' => $this->trip->id,
            'seat_id' => $this->seats[1]->id,
            'from_station_id' => $this->stations[1]->id,
            'to_station_id'   => $this->stations[3]->id,
        ];

        $response = $this->postJson('/api/book-seat', $payload);

        $response
            ->assertStatus(422)
            ->assertJson(['error' => 'Seat already booked in this segment.']);
    }

    /** @test */
    public function it_returns_available_seats()
    {
        // No bookings
        $from = $this->stations[0]->id; // A
        $to   = $this->stations[3]->id; // D

        $response = $this->getJson("/api/trips/{$this->trip->id}/available-seats?from_station_id=$from&to_station_id=$to");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Available seats retrieved successfully']);

        $this->assertCount(5, $response->json('seats'));
    }

    /** @test */
    public function available_seats_excludes_overlapping_seat()
    {
        // Book seat #1 (A → C)
        Booking::create([
            'trip_id' => $this->trip->id,
            'seat_id' => $this->seats[1]->id,
            'from_station_id' => $this->stations[0]->id,
            'to_station_id'   => $this->stations[2]->id,
            'from_order' => 1,
            'to_order'   => 3,
            'user_id' => $this->user->id,
        ]);

        $from = $this->stations[1]->id; // B
        $to   = $this->stations[3]->id; // D

        $response = $this->getJson("/api/trips/{$this->trip->id}/available-seats?from_station_id=$from&to_station_id=$to");

        $response->assertStatus(200);

        // Seat #1 should NOT be available
        $this->assertCount(4, $response->json('seats'));
    }
}
