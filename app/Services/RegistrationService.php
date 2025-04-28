<?php
namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use App\Models\EventRegistration;
use App\Notifications\ParticipantRegistered;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon; 

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
        $registration = DB::transaction(function () use ($event, $participantName, $participantEmail) {

            $participant = Participant::firstOrCreate(
                ['email' => $participantEmail],
                ['name' => $participantName]
            );

            $currentEvent = Event::lockForUpdate()->findOrFail($event->id);
            if ($currentEvent->registrations()->count() >= $currentEvent->max_participants) {
                throw ValidationException::withMessages([
                    'event_id' => ["Event '{$currentEvent->name}' has reached its maximum capacity."],
                ]);
            }

            $this->ensureNoOverlap($participant, $currentEvent);

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

        $registration->refresh();

        if($registration){
            $registration->loadMissing('participant');
            $this->dispatchRegistrationNotification($registration->participant, $registration);
            return $registration;
        }
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
            ->where('events.id', '!=', $newEvent->id)
            ->where(function ($query) use ($newEventStart, $newEventEnd) {
                $query->where('start_datetime', '<', $newEventEnd)
                      ->where('end_datetime', '>', $newEventStart);
            })
            ->exists();

        if ($hasOverlap) {
             throw ValidationException::withMessages([
                'participant_id' => ["Participant {$participant->email} is already registered for an overlapping event during this time. you registerd for an initail event with the same time conflict"],
            ]);
        }
    }

    protected function dispatchRegistrationNotification(Participant $participant, EventRegistration $registration): void
    {
        Log::info('Dispatching registration notification for participant ID:', [$participant->id , $registration->id]);
        try {
            if(!$participant){
                Log::channel('notification_logs')->warning(
                    "Failed to queue registration notification.",
                    [
                        $registration->id
                    ]);
                return;
            }
            $participant->notify(new ParticipantRegistered($registration));
            Log::info('Registration notification dispatched successfully.', [$registration->id, $participant->id]);
        } catch (\Exception $e) {
            // Log specifically to the 'notification_errors' channel
            Log::channel('notification_logs')->error(
                "Failed to queue registration notification.",
                [
                    'participant_id' => $participant->id,
                    'event_id' => $registration->event_id,
                    'registration_id' => $registration->id,
                    'error_message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString() // Optional: include stack trace for debugging
                ]
            );

        }
    }
}
