<?php

namespace App\Http\Controllers;

use App\Support\ResponsiveImage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ImageVariantController extends Controller
{
    public function __invoke(Request $request, string $token, int $width): BinaryFileResponse
    {
        try {
            $source = ResponsiveImage::resolve($token);
            $variant = ResponsiveImage::variant($source['path'], $source['version'], $width);
        } catch (Throwable) {
            abort(404);
        }

        $response = response()->file($variant, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'Content-Type' => 'image/webp',
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->setAutoEtag();
        $response->setAutoLastModified();
        $response->isNotModified($request);

        return $response;
    }
}
