<?php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request; // Import Request
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException; // Handle validation errors
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException; // Handle 404
use Symfony\Component\Httpkernel\Exception\HttpException; // Handle general HTTP errors
use Throwable;// For logging exceptions

class Handler extends ExceptionHandler
{
    // ... (existing properties like $dontReport, $dontFlash)

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (Throwable $e, Request $request) {
            // Check if the request expects JSON (typical for APIs)
            if ($request->expectsJson()) {
                // Handle Validation Errors (422)
                if ($e instanceof ValidationException) {
                    return response()->json([
                        'message' => 'Validation Failed',
                        'errors' => $e->errors()
                    ], 422);
                }

                // Handle Model Not Found Errors (404)
                if ($e instanceof NotFoundHttpException) {
                    return response()->json([
                        'message' => 'Resource not found.'
                    ], 404);
                }

                 // Handle general HTTP exceptions (like abort(403), abort(409))
                if ($e instanceof HttpException) {
                    return response()->json([
                        'message' => $e->getMessage() ?: 'An error occurred.' // Use exception message if available
                    ], $e->getStatusCode());
                }

                // Default Handler for other exceptions (500 Internal Server Error)
                // Log the error in production, but return a generic message
                // In debug mode, Laravel's default handler shows detailed errors
                 if (!config('app.debug')) {
                     Log::error($e); // Log the actual error
                      return response()->json([
                         'message' => 'An unexpected server error occurred.'
                      ], 500);
                 }
            }
        });

        // Default registration (optional)
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
