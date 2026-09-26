<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class SocialPreviewImage
{
    /** Never fetch remote images while rendering a page. */
    public static function metadata(?string $source): array
    {
        $source = trim((string) $source);
        if ($source === '' || preg_match('~^(?!https?://)[a-z][a-z0-9+.-]*:~i', $source)) {
            $source = asset('images/hero/hero-edulaw.jpg');
        }
        if (str_starts_with($source, '//')) {
            $source = 'https:'.$source;
        } elseif (! preg_match('~^https?://~i', $source)) {
            $source = url('/'.ltrim($source, '/'));
        }

        $host = strtolower((string) parse_url($source, PHP_URL_HOST));
        $canonical = parse_url((string) config('edulaw.site.url', config('app.url')));
        $canonicalHost = strtolower($canonical['host'] ?? '');
        // Upgrade only our own origin. External hosts may not support HTTPS.
        if (($canonical['scheme'] ?? '') === 'https' && $canonicalHost !== ''
            && in_array($host, [$canonicalHost, 'www.'.$canonicalHost], true)) {
            $source = preg_replace('~^https?://[^/]+~i', 'https://'.$canonicalHost.(isset($canonical['port']) ? ':'.$canonical['port'] : ''), $source);
        } elseif (request()->isSecure() && $host === strtolower(request()->getHost())) {
            $source = preg_replace('~^http:~i', 'https:', $source);
        }

        $metadata = ['url' => $source, 'secure_url' => str_starts_with($source, 'https://') ? $source : null];
        $descriptor = ResponsiveImage::descriptor($source);
        if ($descriptor !== null) {
            $path = $descriptor['scope'] === 'public'
                ? public_path($descriptor['path'])
                : Storage::disk('public')->path($descriptor['path']);
            $size = @getimagesize($path);
            if ($size !== false) {
                $metadata += ['width' => $size[0], 'height' => $size[1], 'type' => $size['mime']];
            }
        }

        return $metadata;
    }
}
