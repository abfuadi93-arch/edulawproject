<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ResponsiveImage
{
    /** @var list<int> */
    public const WIDTHS = [64, 96, 160, 240, 320, 480, 640, 768, 960, 1280, 1600];

    private const QUALITY = 80;

    /** @param list<int> $widths */
    public static function srcset(?string $source, array $widths): ?string
    {
        $descriptor = self::descriptor($source);

        if ($descriptor === null) {
            return null;
        }

        $token = self::encode($descriptor);
        $candidates = collect($widths)
            ->map(fn (mixed $width): int => (int) $width)
            ->filter(fn (int $width): bool => in_array($width, self::WIDTHS, true))
            ->unique()
            ->sort()
            ->values();

        if ($candidates->isEmpty()) {
            return null;
        }

        return $candidates
            ->map(fn (int $width): string => route('media.variant', [
                'token' => $token,
                'width' => $width,
            ])." {$width}w")
            ->implode(', ');
    }

    /** @return array{scope: string, path: string, version: int}|null */
    public static function descriptor(?string $source): ?array
    {
        if (blank($source)) {
            return null;
        }

        $path = self::localPath((string) $source);

        if ($path === null) {
            return null;
        }

        [$scope, $relativePath] = $path;
        $absolutePath = self::resolvePath($scope, $relativePath);

        if ($absolutePath === null || ! self::isSupportedImage($absolutePath)) {
            return null;
        }

        return [
            'scope' => $scope,
            'path' => $relativePath,
            'version' => (int) filemtime($absolutePath),
        ];
    }

    /** @return array{path: string, version: int} */
    public static function resolve(string $token): array
    {
        $decoded = self::decode($token);
        $scope = $decoded['scope'] ?? null;
        $relativePath = $decoded['path'] ?? null;
        $version = $decoded['version'] ?? null;

        if (! is_string($scope) || ! is_string($relativePath) || ! is_int($version)) {
            throw new RuntimeException('Invalid responsive image token.');
        }

        $absolutePath = self::resolvePath($scope, $relativePath);

        if ($absolutePath === null
            || ! self::isSupportedImage($absolutePath)
            || (int) filemtime($absolutePath) !== $version) {
            throw new RuntimeException('Responsive image source is unavailable.');
        }

        return ['path' => $absolutePath, 'version' => $version];
    }

    public static function variant(string $sourcePath, int $version, int $width): string
    {
        if (! in_array($width, self::WIDTHS, true)) {
            throw new RuntimeException('Unsupported responsive image width.');
        }

        $cachePath = storage_path('app/private/image-variants/'.hash(
            'sha256',
            "{$sourcePath}|{$version}|{$width}|".self::QUALITY,
        ).'.webp');

        if (is_file($cachePath)) {
            return $cachePath;
        }

        File::ensureDirectoryExists(dirname($cachePath));
        $temporaryPath = tempnam(dirname($cachePath), 'variant-');

        if ($temporaryPath === false) {
            throw new RuntimeException('Unable to create an image variant.');
        }

        try {
            if (extension_loaded('imagick')) {
                self::createWithImagick($sourcePath, $temporaryPath, $width);
            } elseif (extension_loaded('gd')) {
                self::createWithGd($sourcePath, $temporaryPath, $width);
            } else {
                throw new RuntimeException('No supported image extension is installed.');
            }

            if (! is_file($temporaryPath) || filesize($temporaryPath) === 0) {
                throw new RuntimeException('The image variant could not be generated.');
            }

            if (! @rename($temporaryPath, $cachePath) && ! is_file($cachePath)) {
                throw new RuntimeException('The image variant could not be cached.');
            }
        } finally {
            if (is_file($temporaryPath)) {
                @unlink($temporaryPath);
            }
        }

        return $cachePath;
    }

    /** @return array{string, string}|null */
    private static function localPath(string $source): ?array
    {
        $source = trim($source);
        $parts = parse_url($source);

        if ($parts === false) {
            return null;
        }

        if (isset($parts['host'])) {
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

            if (! is_string($appHost) || strcasecmp($parts['host'], $appHost) !== 0) {
                return null;
            }
        }

        $path = rawurldecode($parts['path'] ?? $source);
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            return ['storage', substr($path, strlen('storage/'))];
        }

        if (str_starts_with($path, 'images/')) {
            return ['public', $path];
        }

        if (! isset($parts['host']) && $path !== '') {
            return ['storage', $path];
        }

        return null;
    }

    private static function resolvePath(string $scope, string $relativePath): ?string
    {
        $basePath = match ($scope) {
            'storage' => Storage::disk('public')->path(''),
            'public' => public_path(),
            default => null,
        };

        if ($basePath === null || str_contains($relativePath, "\0")) {
            return null;
        }

        $realBasePath = realpath($basePath);
        $realSourcePath = realpath($basePath.DIRECTORY_SEPARATOR.ltrim($relativePath, '/'));

        if ($realBasePath === false
            || $realSourcePath === false
            || ! str_starts_with($realSourcePath, $realBasePath.DIRECTORY_SEPARATOR)
            || ! is_file($realSourcePath)) {
            return null;
        }

        return $realSourcePath;
    }

    private static function isSupportedImage(string $path): bool
    {
        if (filesize($path) > 25 * 1024 * 1024) {
            return false;
        }

        try {
            $details = getimagesize($path);
        } catch (Throwable) {
            return false;
        }

        if ($details === false || ($details[0] * $details[1]) > 60_000_000) {
            return false;
        }

        return in_array($details['mime'] ?? null, [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
        ], true);
    }

    /** @param array{scope: string, path: string, version: int} $descriptor */
    private static function encode(array $descriptor): string
    {
        return rtrim(strtr(base64_encode(json_encode($descriptor, JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
    }

    /** @return array<string, mixed> */
    private static function decode(string $token): array
    {
        $padding = strlen($token) % 4;
        $encoded = strtr($token, '-_', '+/').($padding === 0 ? '' : str_repeat('=', 4 - $padding));
        $json = base64_decode($encoded, true);

        if ($json === false) {
            throw new RuntimeException('Invalid responsive image token.');
        }

        try {
            $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            throw new RuntimeException('Invalid responsive image token.', previous: $exception);
        }

        if (! is_array($decoded)) {
            throw new RuntimeException('Invalid responsive image token.');
        }

        return $decoded;
    }

    private static function createWithImagick(string $sourcePath, string $destinationPath, int $width): void
    {
        $image = new \Imagick($sourcePath);

        try {
            if ($image->getNumberImages() > 1) {
                $image->setIteratorIndex(0);
            }

            $image->autoOrient();
            $image->setImagePage(0, 0, 0, 0);
            $targetWidth = min($width, $image->getImageWidth());

            if ($targetWidth < $image->getImageWidth()) {
                $targetHeight = max(1, (int) round(
                    $image->getImageHeight() * ($targetWidth / $image->getImageWidth()),
                ));
                $image->resizeImage($targetWidth, $targetHeight, \Imagick::FILTER_LANCZOS, 1);
            }

            $image->stripImage();
            $image->setImageFormat('webp');
            $image->setImageCompressionQuality(self::QUALITY);
            $image->setOption('webp:method', '6');
            $image->writeImage($destinationPath);
        } finally {
            $image->clear();
            $image->destroy();
        }
    }

    private static function createWithGd(string $sourcePath, string $destinationPath, int $width): void
    {
        $contents = file_get_contents($sourcePath);
        $source = $contents === false ? false : imagecreatefromstring($contents);

        if ($source === false) {
            throw new RuntimeException('The source image could not be decoded.');
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $targetWidth = min($width, $sourceWidth);
        $targetHeight = max(1, (int) round($sourceHeight * ($targetWidth / $sourceWidth)));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($target === false) {
            imagedestroy($source);
            throw new RuntimeException('The image variant could not be allocated.');
        }

        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefill($target, 0, 0, $transparent);
        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight,
        );

        if (! imagewebp($target, $destinationPath, self::QUALITY)) {
            imagedestroy($source);
            imagedestroy($target);
            throw new RuntimeException('The image variant could not be encoded.');
        }

        imagedestroy($source);
        imagedestroy($target);
    }
}
