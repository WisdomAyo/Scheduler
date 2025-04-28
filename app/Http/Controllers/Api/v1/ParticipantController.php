<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegistrationResource;
use App\Models\Participant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;



/**
 * @group Participants Management
 * APIs related to participants
 */

class ParticipantController extends Controller
{
    //

        /**
     * List Registrations for a Participant
     *
     * Retrieves a list of all events a specific participant is registered for.
     *
     * @urlParam participant integer required The ID of the participant. Example: 12
     * @responseFile status=200 scenario="Success" storage/responses/participant.registrations.json
     * @responseFile status=404 scenario="Participant Not Found" storage/responses/errors.not_found.json
     */
    public function registrations(Participant $participant):AnonymousResourceCollection|JsonResponse
    {
        $registration = $participant->registrations()->with('event')->paginate(20);
        return RegistrationResource::collection($registration);
    }
}
