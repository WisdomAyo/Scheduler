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
        // This can be slightly tricky; often done in the controller or service before validation
        // Or fetch participant in the `prepareForValidation` method.
        $participant = Participant::firstWhere('email', $this->input('email'));

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            // Apply the custom rule if participant exists and event is available
            // Note: You'd still need capacity check logic elsewhere (controller/service)
            'participant_id_for_overlap' => [ // Dummy field or apply rule to email/participant object
                'sometimes', // Only run if participant exists
                 $participant ? new NoOverlappingEvents($participant, $event) : null,
            ],
        ];
    }

     /**
      * Prepare the data for validation.
      *
      * You might add a pseudo-field here based on the fetched participant
      * to make the rule application cleaner in the rules() method.
      *
      * @return void
      */
     protected function prepareForValidation()
     {
         // Example: Fetch participant and merge a field if needed for validation rule
         // $participant = Participant::firstWhere('email', $this->input('email'));
         // if ($participant) {
         //     $this->merge(['participant_obj' => $participant]);
         // }
     }
}
