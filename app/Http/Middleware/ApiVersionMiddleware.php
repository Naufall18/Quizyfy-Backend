<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiVersionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Add API version header to all responses
     */
    public function handle(Request $request, Closure $next, string $version = '1.0'): Response
    {
        $response = $next($request);
        
        // Add API version to response headers
        $response->headers->set('X-API-Version', $version);
        $response->headers->set('X-API-Deprecated', 'false');
        
        return $response;
    }
}
