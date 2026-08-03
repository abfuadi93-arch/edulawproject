<?php

namespace App\Support;

use App\Models\Multimedia;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class MultimediaThumbnail
{
    public static function importFromSource(Multimedia $multimedia): void
    {
        if (filled($multimedia->thumbnail)
            || ! in_array($multimedia->type, ['shorts', 'reels'], true)
            || $multimedia->platform !== 'instagram'
            || blank($multimedia->media_url)) {
            return;
        }

        try {
            $page = self::request()->get($multimedia->media_url);

            if (! $page->successful()) {
                return;
            }

            $thumbnailUrl = self::openGraphImage($page->body());

            if (! self::isTrustedInstagramImage($thumbnailUrl)) {
                return;
            }

            $image = self::request()->get($thumbnailUrl);
            $mimeType = Str::before((string) $image->header('Content-Type'), ';');
            $extension = match ($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => null,
            };

            if (! $image->successful() || $extension === null || strlen($image->body()) > 5 * 1024 * 1024) {
                return;
            }

            $path = 'multimedia/thumbnails/short-'.$multimedia->getKey().'-'.Str::random(8).'.'.$extension;
            Storage::disk('public')->put($path, $image->body());

            $multimedia->forceFill(['thumbnail' => $path])->saveQuietly();
        } catch (\Throwable) {
            // Instagram may reject automated requests. Saving the content must still succeed.
        }
    }

    private static function request(): PendingRequest
    {
        return Http::timeout(10)
            ->connectTimeout(5)
            ->withHeaders([
                'Accept' => 'text/html,image/avif,image/webp,image/*,*/*;q=0.8',
                'User-Agent' => 'Mozilla/5.0 (compatible; EdulawProject/1.0)',
            ]);
    }

    private static function openGraphImage(string $html): ?string
    {
        if (preg_match('~<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']~i', $html, $matches)
            || preg_match('~<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']~i', $html, $matches)) {
            return html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);
        }

        return null;
    }

    private static function isTrustedInstagramImage(?string $url): bool
    {
        if (blank($url) || parse_url($url, PHP_URL_SCHEME) !== 'https') {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return collect(['cdninstagram.com', 'fbcdn.net', 'instagram.com'])
            ->contains(fn (string $domain): bool => $host === $domain || Str::endsWith($host, '.'.$domain));
    }
}
