<?php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Participant;
use App\Models\Event;
use Carbon\Carbon; // For date comparisons if needed

class NoOverlappingEvents implements ValidationRule
{
    protected Participant $participant;
    protected Event $eventToRegister;

    /**
     * Create a new rule instance.
     * Pass the participant and the event they are trying to register for.
     */
    public function __construct(Participant $participant, Event $eventToRegister)
    {
        $this->participant = $participant;
        $this->eventToRegister = $eventToRegister;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $newEventStart = $this->eventToRegister->start_datetime;
        $newEventEnd = $this->eventToRegister->end_datetime;

        $hasOverlap = $this->participant->events()
            ->where('events.id', '!=', $this->eventToRegister->id) // Exclude the event itself if editing
            ->where(function ($query) use ($newEventStart, $newEventEnd) {
                $query->where('start_datetime', '<', $newEventEnd)
                      ->where('end_datetime', '>', $newEventStart);
            })
            ->exists();

        if ($hasOverlap) {
            // Use the $fail callback to indicate failure
            $fail('The participant is already registered for an event during this time slot.');
        }
    }
}
