<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = env('API_AUTOMATION_KEY');
        $providedKey = $request->header('X-Api-Key');

        if (!$expectedKey || !$providedKey || !hash_equals($expectedKey, $providedKey)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
