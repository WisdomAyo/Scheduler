<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Use the Log facade
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log basic request info before handling
        Log::info('API Request:', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'input' => $request->except(['password', 'password_confirmation']), // Exclude sensitive fields
        ]);

        // Process the request and get the response
        $response = $next($request);

        // Log basic response info after handling (optional)
        // Log::info('API Response:', [
        //     'status' => $response->getStatusCode(),
        // ]);

        return $response;
    }
}