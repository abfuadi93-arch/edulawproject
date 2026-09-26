@props([
    'title',
    'description',
    'canonical' => null,
    'image' => null,
    'imageAlt' => null,
    'type' => 'website',
    'robots' => 'index,follow',
])

@php
    $previewImage = \App\Support\SocialPreviewImage::metadata($image);
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $robots }}">

<link rel="canonical" href="{{ $canonical ?? url()->current() }}">

<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical ?? url()->current() }}">
<meta property="og:site_name" content="Edulaw Project">
<meta property="og:locale" content="id_ID">

    <meta property="og:image" content="{{ $previewImage['url'] }}">
    @if ($previewImage['secure_url'])
        <meta property="og:image:secure_url" content="{{ $previewImage['secure_url'] }}">
    @endif
    @if (isset($previewImage['width']))
        <meta property="og:image:width" content="{{ $previewImage['width'] }}">
        <meta property="og:image:height" content="{{ $previewImage['height'] }}">
        <meta property="og:image:type" content="{{ $previewImage['type'] }}">
    @endif
    <meta property="og:image:alt" content="{{ $imageAlt ?? $title }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">

<meta name="twitter:image" content="{{ $previewImage['url'] }}">
