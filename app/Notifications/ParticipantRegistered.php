<?php
namespace App\Notifications;

use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Channels\LogChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\LogMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;


class ParticipantRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    public EventRegistration $registration;

    public function __construct(EventRegistration $registration)
    {
        $this->registration = $registration;
    }

    public function via(object $notifiable): array
    {
        // return ['mail']; // To send actual email
        return ['LogChannel::class']; // Use 'log' for assessment simplicity
    }

    // Example using the 'log' channel (writes to laravel.log)
    public function toLog(object $notifiable): LogMessage
    {
        $event = $this->registration->event ? $this->registration->event->name: "Unknown Event";
        $participantName = $notifiable->name ?? "Unknown Participant";
        return (new LogMessage)
            ->level('info')
            ->subject("Participant Register Notification")
            ->line("Participant {$participantName} registered for event {$event}: {$this->registration->event->name}.");
    }

    public function failed(\Throwable $exception): void
    {
        // Log job failure details (will go to default log stack)
        Log::error('Notification Job Failed: ParticipantRegistered', [
            'registration_id' => $this->registration->id,
            'participant_id' => $this->registration->participant_id,
            'error' => $exception->getMessage(),
        ]);
    }

    // If using mail channel:
    // public function toMail(object $notifiable): MailMessage
    // {
    //     return (new MailMessage)
    //                 ->line('You have successfully registered for an event!')
    //                 ->line("Event: {$this->registration->event->name}")
    //                 ->line('Thank you for using our application!');
    // }
}
