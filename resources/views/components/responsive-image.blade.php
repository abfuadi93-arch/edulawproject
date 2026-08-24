@props([
    'src',
    'alt' => '',
    'width' => null,
    'height' => null,
    'widths' => [320, 640],
    'sizes' => '100vw',
    'loading' => 'lazy',
    'decoding' => 'async',
])

@php
    $responsiveSrcset = \App\Support\ResponsiveImage::srcset($src, $widths);
@endphp

<img
    src="{{ $src }}"
    @if ($responsiveSrcset) srcset="{{ $responsiveSrcset }}" sizes="{{ $sizes }}" @endif
    alt="{{ $alt }}"
    @if ($width) width="{{ $width }}" @endif
    @if ($height) height="{{ $height }}" @endif
    @if ($loading) loading="{{ $loading }}" @endif
    @if ($decoding) decoding="{{ $decoding }}" @endif
    {{ $attributes }}
>
