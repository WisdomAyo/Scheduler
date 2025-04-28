<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NoOverlappingEvents;
use App\Models\Participant;
use App\Models\Event;

class RegistrationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        // Assuming route model binding provides $this->event
        $event = $this->route('event');

        // Need to get the participant based on input email *before* full validation
        $participant = Participant::firstWhere('email', $this->input('email'));

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'participant_id_for_overlap' => [ 
                'sometimes', // Only run if participant exists
                 $participant ? new NoOverlappingEvents($participant, $event) : null,
            ],
        ];
    }


     protected function prepareForValidation()
     {
         // Example: Fetch participant and merge a field if needed for validation rule
         // $participant = Participant::firstWhere('email', $this->input('email'));
         // if ($participant) {
         //     $this->merge(['participant_obj' => $participant]);
         // }
     }
}
