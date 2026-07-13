<?php

namespace App\Http\Middleware;

use App\Models\PageVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackPageVisit
{
    private const COOKIE_NAME = 'edulaw_visitor_id';

    public function handle(Request $request, Closure $next): Response
    {
        $visitorId = $request->cookies->get(self::COOKIE_NAME) ?: (string) Str::uuid();
        $isNewVisitor = ! $request->cookies->has(self::COOKIE_NAME);

        /** @var Response $response */
        $response = $next($request);

        if ($isNewVisitor) {
            $response->headers->setCookie(cookie(
                name: self::COOKIE_NAME,
                value: $visitorId,
                minutes: 60 * 24 * 365,
                path: null,
                domain: null,
                secure: $request->isSecure(),
                httpOnly: true,
                raw: false,
                sameSite: 'Lax',
            ));
        }

        if (! $this->shouldTrack($request, $response)) {
            return $response;
        }

        try {
            PageVisit::query()->create([
                'visitor_id' => $visitorId,
                'ip_hash' => $this->hashIp($request->ip()),
                'method' => $request->method(),
                'path' => $request->path(),
                'full_url' => Str::limit($request->fullUrl(), 2048, ''),
                'route_name' => $request->route()?->getName(),
                'status_code' => $response->getStatusCode(),
                'referrer' => Str::limit((string) $request->headers->get('referer'), 2000, '') ?: null,
                'user_agent' => Str::limit((string) $request->userAgent(), 2000, '') ?: null,
                'visited_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            Log::debug('Page visit tracking skipped.', [
                'message' => $exception->getMessage(),
                'path' => $request->path(),
            ]);
        }

        return $response;
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($response->getStatusCode() >= 400) {
            return false;
        }

        if ($request->is('admin') || $request->is('admin/*')) {
            return false;
        }

        return ! $this->isBot((string) $request->userAgent());
    }

    private function isBot(string $userAgent): bool
    {
        if ($userAgent === '') {
            return false;
        }

        return Str::contains(Str::lower($userAgent), [
            'bot',
            'crawler',
            'spider',
            'slurp',
            'preview',
            'facebookexternalhit',
            'whatsapp',
            'telegrambot',
            'linkedinbot',
        ]);
    }

    private function hashIp(?string $ip): ?string
    {
        if (! $ip) {
            return null;
        }

        return hash('sha256', $ip.'|'.config('app.key'));
    }
}
