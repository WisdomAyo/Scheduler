<?php
// tests/Feature/EventRegistrationTest.php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase; // Reset DB for each test
use Tests\TestCase;
use App\Models\Event;
use App\Models\Participant;
use Carbon\Carbon;

class EventRegistrationTest extends TestCase
{
    use RefreshDatabase; // Important!

    /** @test */
    public function a_participant_can_register_for_an_event()
    {
        // Arrange: Create an event
        $event = Event::factory()->create([
            'max_participants' => 10,
            'start_datetime' => Carbon::now()->addDay(),
            'end_datetime' => Carbon::now()->addDay()->addHours(2),
        ]);

        $participantData = [
            'name' => 'Olaniyan Wisdom',
            'email' => 'koladeolaniyan@gmail.com',
        ];

        // Act: Send POST request to registration endpoint
        $response = $this->postJson("/api/v1/events/{$event->id}/register", $participantData);

        // Assert: Check response and database state
        $response->assertStatus(201) // Assert Created status
                 ->assertJsonStructure([ // Assert response structure
                     'data' => [
                         'registration_id',
                         'registered_at',
                         'event' => ['id', 'name', /* ... */],
                         'participant' => ['participant_id', 'name', 'email', /* ... */],
                     ]
                 ])
                 ->assertJsonPath('data.participant.email', 'john.doe@example.com')
                 ->assertJsonPath('data.event.id', $event->id);

        // Assert participant and registration exist in DB
        $this->assertDatabaseHas('participants', [
            'email' => 'koladeolaniyan@gmail.com',
        ]);
        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'participant_id' => Participant::where('email', 'john.doe@example.com')->first()->id,
        ]);
    }

    /** @test */
    public function registration_fails_if_event_is_full()
    {
        // Arrange: Create an event with max 1 participant and register one
        $event = Event::factory()->create(['max_participants' => 1]);
        $participant1 = Participant::factory()->create();
        $event->registrations()->create(['participant_id' => $participant1->id]);

        $newParticipantData = ['name' => 'Wisdom Olaniyan', 'email' => 'koladeolaniyan@gmail.com'];

        // Act
        $response = $this->postJson("/api/v1/events/{$event->id}/register", $newParticipantData);

        // Assert
        $response->assertStatus(409) // Assert Conflict or 422 Unprocessable Entity
                 ->assertJsonPath('message', 'Validation Failed') // Or your specific error message
                 ->assertJsonValidationErrors(['event_id']); // Check specific validation error key


        $this->assertDatabaseMissing('participants', ['email' => 'koladeolaniyan@gmail.com']);
        $this->assertDatabaseCount('event_registrations', 1); // Ensure no new registration was added
    }

    /** @test */
    public function registration_fails_for_overlapping_events()
    {
        // Arrange: Create two events with overlapping times
        $event1 = Event::factory()->create([
            'start_datetime' => Carbon::parse('2025-10-10 10:00:00'),
            'end_datetime' => Carbon::parse('2025-10-10 12:00:00'),
            'max_participants' => 5,
        ]);
         $event2 = Event::factory()->create([ // Overlapping event
            'start_datetime' => Carbon::parse('2025-10-10 11:00:00'),
            'end_datetime' => Carbon::parse('2025-10-10 13:00:00'),
            'max_participants' => 5,
        ]);
        $participant = Participant::factory()->create();

        // Register participant for the first event
         $this->postJson("/api/v1/events/{$event1->id}/register", [
             'name' => $participant->name,
             'email' => $participant->email
         ])->assertStatus(201);

        // Act: Try to register the same participant for the second (overlapping) event
        $response = $this->postJson("/api/v1/events/{$event2->id}/register", [
            'name' => $participant->name,
            'email' => $participant->email
        ]);

        // Assert
        $response->assertStatus(409) // Or 422
                 ->assertJsonPath('message', 'Validation Failed')
                 ->assertJsonValidationErrors(['participant_id']); // Check overlap error key


        $this->assertDatabaseHas('event_registrations', ['event_id' => $event1->id, 'participant_id' => $participant->id]);
        $this->assertDatabaseMissing('event_registrations', ['event_id' => $event2->id, 'participant_id' => $participant->id]);
    }

    // Add more tests: validation errors, duplicate registration attempt, etc.
}