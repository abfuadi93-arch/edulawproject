<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectWwwToCanonicalHost
{
    /**
     * Normalize the public Edulaw origin without introducing a scheme chain.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $canonicalUrl = parse_url((string) config('edulaw.site.url', config('app.url')));
        $canonicalHost = strtolower((string) ($canonicalUrl['host'] ?? ''));
        $canonicalScheme = strtolower((string) ($canonicalUrl['scheme'] ?? ''));
        $requestHost = strtolower($request->getHost());
        $isCanonicalHost = $requestHost === $canonicalHost;
        $isWwwAlias = $requestHost === 'www.'.$canonicalHost;
        $hasWrongScheme = $isCanonicalHost
            && $canonicalScheme !== ''
            && strtolower($request->getScheme()) !== $canonicalScheme;
        $requestPath = (string) parse_url($request->getRequestUri(), PHP_URL_PATH);
        $hasTrailingSlash = $request->isMethodSafe()
            && $requestPath !== '/'
            && str_ends_with($requestPath, '/');
        $legacyPath = $request->isMethodSafe()
            ? $this->legacyDestination($requestPath)
            : null;

        $normalizedPath = $legacyPath ?? rtrim($requestPath, '/');
        $normalizedQuery = $request->query();
        $originalQuery = $normalizedQuery;

        if ($request->isMethodSafe()) {
            if (preg_match('#^/insight/[^/]+$#', $normalizedPath) === 1
                && in_array($normalizedQuery['source'] ?? null, ['home-highlight', 'home-editor-pick'], true)) {
                unset($normalizedQuery['source']);
            }

            $pageKey = match ($normalizedPath) {
                '/insight', '/opportunities', '/riset-publikasi', '/program/arsip' => 'page',
                '/multimedia' => 'video_page',
                default => null,
            };
            if ($pageKey !== null && ($normalizedQuery[$pageKey] ?? null) === '1') {
                unset($normalizedQuery[$pageKey]);
            }
            if ($normalizedPath === '/riset-publikasi' && ($normalizedQuery['type'] ?? null) === 'semua') {
                unset($normalizedQuery['type']);
            }
        }
        $hasRedundantQuery = $normalizedQuery !== $originalQuery;

        if (($canonicalHost === '' || (! $isWwwAlias && ! $hasWrongScheme))
            && ! $hasTrailingSlash
            && $legacyPath === null
            && ! $hasRedundantQuery) {
            return $next($request);
        }

        $normalizeOrigin = $isWwwAlias || $hasWrongScheme;
        $scheme = $normalizeOrigin && $canonicalScheme !== ''
            ? $canonicalScheme
            : $request->getScheme();
        $targetHost = $normalizeOrigin ? $canonicalHost : $request->getHost();
        $port = $normalizeOrigin
            ? (isset($canonicalUrl['port']) ? ':'.$canonicalUrl['port'] : '')
            : ($request->getPort() && ! in_array($request->getPort(), [80, 443], true) ? ':'.$request->getPort() : '');
        $requestUri = $request->getRequestUri();

        if ($legacyPath !== null) {
            [, $query] = array_pad(explode('?', $requestUri, 2), 2, null);
            $requestUri = $legacyPath.($query !== null ? '?'.$query : '');
        } elseif ($hasTrailingSlash) {
            [$path, $query] = array_pad(explode('?', $requestUri, 2), 2, null);
            $requestUri = rtrim($path, '/').($query !== null ? '?'.$query : '');
        }

        if ($hasRedundantQuery) {
            $requestUri = explode('?', $requestUri, 2)[0];
            $requestUri .= $normalizedQuery !== [] ? '?'.http_build_query($normalizedQuery) : '';
        }

        $target = $scheme.'://'.$targetHost.$port.$requestUri;

        return redirect()->away($target, Response::HTTP_MOVED_PERMANENTLY);
    }

    private function legacyDestination(string $path): ?string
    {
        $path = rtrim($path, '/');

        if ($path === '/publikasi') {
            return '/riset-publikasi';
        }

        if (preg_match('#^/publikasi/([^/]+)$#', $path, $matches) === 1) {
            return '/riset-publikasi/'.$matches[1];
        }

        if ($path === '/insight/worklife-balance-di-era-hustle-culture-menakar-perlindungan-hukum-terhadap-hak-atas-kesehatan-mental') {
            return '/insight/work-life-balance-di-era-hustle-culture-menakar-perlindungan-hukum-terhadap-hak-atas-kesehatan-mental';
        }

        return $path === '/peluang' ? '/opportunities' : null;
    }
}
