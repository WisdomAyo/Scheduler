<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'start_datetime' => $this->start_datetime->toIso8601String(), // Standard format
            'end_datetime' => $this->end_datetime->toIso8601String(),
            'max_participants' => $this->max_participants,
            // Conditionally load participant count if needed/queried efficiently
            'registered_participants' => $this->whenLoaded('registrations', function () {
                 return $this->registrations->count();
                 // Or use $this->registrations_count if using withCount('registrations')
            }),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
