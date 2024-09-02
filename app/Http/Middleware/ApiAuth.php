<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Retrieve the API key from the headers
        $apiKey = $request->header('X-API-KEY');

        // Define the expected API key (for example purposes)
        $expectedApiKey = env('API_KEY');

        // Check if the API key matches the expected key
        if ($apiKey !== $expectedApiKey) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
