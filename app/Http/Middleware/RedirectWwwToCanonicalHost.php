<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectWwwToCanonicalHost
{
    /**
     * Redirect the www alias to the canonical host configured in APP_URL.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $canonicalUrl = parse_url((string) config('app.url'));
        $canonicalHost = strtolower((string) ($canonicalUrl['host'] ?? ''));
        $requestHost = strtolower($request->getHost());

        if ($canonicalHost === '' || $requestHost !== 'www.'.$canonicalHost) {
            return $next($request);
        }

        $scheme = (string) ($canonicalUrl['scheme'] ?? $request->getScheme());
        $port = isset($canonicalUrl['port']) ? ':'.$canonicalUrl['port'] : '';
        $target = $scheme.'://'.$canonicalHost.$port.$request->getRequestUri();

        return redirect()->away($target, Response::HTTP_MOVED_PERMANENTLY);
    }
}
