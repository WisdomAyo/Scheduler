<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegistrationResource; // You would create this resource
use App\Models\Event;
use App\Services\RegistrationService;
use Illuminate\Support\Facades\Log;
use App\Models\Participant;
use App\Models\EventRegistration;
use Illuminate\Http\Request; // Using base Request, but a FormRequest is better
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB; // For transactions
use Illuminate\Validation\ValidationException; // To throw validation errors
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends Controller
{
    public function __construct(protected RegistrationService $registrationService) {

    }
    
    public function store(Request $request, Event $event): JsonResponse
    {
        // Validate basic participant info (better in a FormRequest)
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        // --- Core Logic ---
        try {
            // Call the service method
            $registration = $this->registrationService->registerParticipant(
                $event,
                $validated['name'],
                $validated['email']
            );

            // Load relationships if needed by the resource
            $registration->load(['event', 'participant']);

            return (new RegistrationResource($registration))
                    ->response()
                    ->setStatusCode(Response::HTTP_CREATED);

        } catch (ValidationException $e) {
             // Return validation errors (e.g., 409 Conflict or 422 Unprocessable)
             return response()->json([
                'message' => 'Registration Failed',
                'errors' => $e->errors()
            ], Response::HTTP_CONFLICT); // Using 409 for business logic conflicts
        } catch (\Exception $e) {
            Log::error('Registration failed via service: ' . $e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred during registration.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
}
}
