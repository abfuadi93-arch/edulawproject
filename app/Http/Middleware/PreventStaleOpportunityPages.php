<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventStaleOpportunityPages
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // These pages contain deadline labels and counts that change at midnight WIB.
        $response->headers->set('Cache-Control', 'private, no-store, no-cache, max-age=0, must-revalidate');

        return $response;
    }
}
