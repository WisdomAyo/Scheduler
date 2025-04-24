<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'registration_id' => $this->id,
            'registered_at' => $this->registered_at->toIso8601String(),
            // Conditionally load related resources if they were eager-loaded
            'event' => new EventResource($this->whenLoaded('event')),
            'participant' => new ParticipantResource($this->whenLoaded('participant')), // Assumes ParticipantResource exists
        ];
    }
}
