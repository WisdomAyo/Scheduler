<?php
namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon; // Added for typing

class RegistrationService
{
    /**
     * Attempt to register a participant for a given event.
     *
     * @param Event $event The event to register for.
     * @param string $participantName The name of the participant.
     * @param string $participantEmail The email of the participant.
     * @return EventRegistration The created registration record.
     * @throws ValidationException If registration fails due to capacity, overlap, or duplication.
     * @throws \Exception For other unexpected errors.
     */
    public function registerParticipant(Event $event, string $participantName, string $participantEmail): EventRegistration
    {
        return DB::transaction(function () use ($event, $participantName, $participantEmail) {

            // 1. Find or Create Participant
            $participant = Participant::firstOrCreate(
                ['email' => $participantEmail],
                ['name' => $participantName]
            );

            // 2. Check Event Capacity (Locking)
            $currentEvent = Event::lockForUpdate()->findOrFail($event->id);
            if ($currentEvent->registrations()->count() >= $currentEvent->max_participants) {
                throw ValidationException::withMessages([
                    'event_id' => ["Event '{$currentEvent->name}' has reached its maximum capacity."],
                ]);
            }

            // 3. Check for Overlapping Events
            $this->ensureNoOverlap($participant, $currentEvent);

            // 4. Create Registration (Check for existing first)
            $existingRegistration = EventRegistration::where('event_id', $currentEvent->id)
                                        ->where('participant_id', $participant->id)
                                        ->first();
            if ($existingRegistration) {
                 throw ValidationException::withMessages([
                    'participant_id' => ["Participant {$participantEmail} is already registered for this event."],
                ]);
            }

            return EventRegistration::create([
                'event_id' => $currentEvent->id,
                'participant_id' => $participant->id,
            ]);
        }); // End transaction
    }

    /**
     * Helper method to check for event overlaps for a participant.
     *
     * @param Participant $participant
     * @param Event $newEvent
     * @throws ValidationException
     */
    protected function ensureNoOverlap(Participant $participant, Event $newEvent): void
    {
        $newEventStart = $newEvent->start_datetime;
        $newEventEnd = $newEvent->end_datetime;

        $hasOverlap = $participant->events()
            ->where('events.id', '!=', $newEvent->id) // Exclude the event itself if needed
            ->where(function ($query) use ($newEventStart, $newEventEnd) {
                $query->where('start_datetime', '<', $newEventEnd)
                      ->where('end_datetime', '>', $newEventStart);
            })
            ->exists();

        if ($hasOverlap) {
             throw ValidationException::withMessages([
                'participant_id' => ["Participant {$participant->email} is already registered for an overlapping event during this time."],
            ]);
        }
    }
}