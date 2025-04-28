<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Resources\EventResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Models\Event;
use Illuminate\Http\JsonResponse; 

use Symfony\Component\HttpFoundation\Response; 


class EventController extends Controller
{

   /**
     * Store a newly created event in storage.
     * Handles image upload if present.
     * @group Events Management
     * @authenticated // If using Sanctum/Passport
     *
     * @bodyParam name string required The name of the event. Example: Laravel Conf EU
     * @bodyParam start_datetime string required The start date and time (ISO 8601). Example: 2025-10-01T09:00:00Z
     * @bodyParam end_datetime string required The end date and time (ISO 8601). Example: 2025-10-01T17:00:00Z
     * @bodyParam max_participants integer required Max attendees. Example: 150
     * @bodyParam event_image file nullable An optional image for the event (max 2MB). Example: (binary)
     *
     * @responseFile status=201 scenario="Success" storage/responses/event.show.json
     * @responseFile status=422 scenario="Validation Error" storage/responses/errors.validation.json
*/


    public function index(): AnonymousResourceCollection // Return type hint for resource collection
    {
        $events = Event::withCount('registrations')->paginate(15); // Adjust page size as needed
        return EventResource::collection($events);
    }
    public function store(StoreEventRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $eventImage = null;

        try {
        if ($request->hasFile('event_image') && $request->file('event_image')->isValid()) {
            $eventImage = $request->file('event_image')->store('event_images', 'public');
        }

        $event = Event::create([
            'name' => $validatedData['name'],
            'start_datetime' => $validatedData['start_datetime'],
            'end_datetime' => $validatedData['end_datetime'],
            'max_participants' => $validatedData['max_participants'],
            'event_image' => $eventImage
        ]);

        // Return the created event using an API Resource
        return (new EventResource($event))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED); // 201 status code

        } catch (\Exception $e) {
            Log::error('Error creating event: ', [
                'error'=> $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'An error occurred while creating the event.'], 500);
        }
    }

    public function show(Event $event): EventResource
    {
        $event->loadCount('registrations');

        return new EventResource($event);
    }

}
