@props([
    'title',
    'description',
    'canonical' => null,
    'image' => null,
    'imageAlt' => null,
    'type' => 'website',
    'robots' => 'index,follow',
])

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

@if ($image)
    <meta property="og:image" content="{{ $image }}">
    <meta property="og:image:secure_url" content="{{ $image }}">
    <meta property="og:image:alt" content="{{ $imageAlt ?? $title }}">
@endif

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">

@if ($image)
    <meta name="twitter:image" content="{{ $image }}">
@endif
