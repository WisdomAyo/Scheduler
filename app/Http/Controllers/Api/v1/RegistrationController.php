<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegistrationResource; 
use App\Models\Event;
use App\Services\RegistrationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request; 
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException; 
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends Controller
{
    public function __construct(protected RegistrationService $registrationService) {

    }
    
    public function store(Request $request, Event $event): JsonResponse
    {
       
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
            $registration->load(['event', 'participant']);

            return (new RegistrationResource($registration))
                    ->response()
                    ->setStatusCode(Response::HTTP_CREATED);

        } catch (ValidationException $e) {
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
