<?php
// app/Http/Controllers/Api/V1/EventController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Resources\EventResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Models\Event;
use Illuminate\Http\JsonResponse; // Added for response type hint
use Symfony\Component\HttpFoundation\Response; // Added for status code

class EventController extends Controller
{
    public function index(): AnonymousResourceCollection // Return type hint for resource collection
    {
        // Eager-load registration count for efficiency if displaying it
        // Paginate results for better performance and usability
        $events = Event::withCount('registrations')->paginate(15); // Adjust page size as needed

        // Return a collection of events using the resource
        return EventResource::collection($events);
    }
    public function store(StoreEventRequest $request): JsonResponse // Use specific request class
    {
        $event = Event::create($request->validated());

        // Return the created event using an API Resource
        return (new EventResource($event))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED); // 201 status code
    }

    public function show(Event $event): EventResource // Route model binding injects the found Event
    {
         // Eager-load registration count or participants if needed
        $event->loadCount('registrations');
        // Example: If you wanted to show participant names (consider privacy/necessity)
        // $event->load('registrations.participant');

        // Return a single event resource
        return new EventResource($event);
    }

    // ... other methods like index, show ...
}
