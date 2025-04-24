<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Participant;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $participants = Participant::factory(10)->create();
        $events = Event::factory(10)->create();
        foreach ($participants as $participant) {
            // Register each participant for 1 to 3 random events (ensure no overlap issues for seeding simplicity here)
            $eventsToRegister = $events->random(fake()->numberBetween(1, 3))->pluck('id');
            foreach ($eventsToRegister as $eventId) {
                // Basic check to prevent duplicate seeding attempts
                if (!EventRegistration::where('participant_id', $participant->id)->where('event_id', $eventId)->exists()) {
                     // Find the event to check capacity (simplified for seeder)
                     $event = Event::find($eventId);
                     if ($event && $event->registrations()->count() < $event->max_participants) {
                         EventRegistration::create([
                             'participant_id' => $participant->id,
                             'event_id' => $eventId,
                         ]);
                     }
                }
            }
        }

 
    }
}
