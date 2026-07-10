<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'api:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 60)) {
            return response()->json([
                'message' => __('translate.toomanyrequests'),
            ], 429);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
