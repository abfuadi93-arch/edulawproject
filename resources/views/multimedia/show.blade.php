@extends('layouts.app')

@php
    $indexReady = $isIndexable ?? \App\Support\PublicContentQuality::multimedia($video);
@endphp

@section('title', $video->title)
@section('meta_description', trim(strip_tags($video->description ?: $video->title)))
@section('canonical_url', $video->watch_url)
@section('robots', $indexReady ? '' : 'noindex,follow')
@section('og_type', 'video.other')
@section('og_image', $video->thumbnail_url)

@push('head')
    @if ($schema = \App\Support\StructuredData::video($video))
        <x-structured-data :data="$schema" />
    @endif
@endpush

@section('content')
<article class="mx-auto max-w-5xl px-4 py-6 sm:px-6">
    <nav class="mb-4 text-sm text-brand-navy" aria-label="Breadcrumb">
        <a href="{{ route('multimedia.index') }}">← Semua Multimedia</a>
    </nav>
    <div class="relative aspect-video overflow-hidden rounded-xl bg-black">
        <iframe
            src="https://www.youtube.com/embed/{{ $video->youtube_video_id }}"
            title="{{ $video->title }}"
            width="1280" height="720"
            class="absolute inset-0 h-full w-full"
            loading="eager"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            referrerpolicy="strict-origin-when-cross-origin"
            allowfullscreen
        ></iframe>
    </div>
    <h1 class="mt-5 text-2xl font-bold text-brand-navy sm:text-3xl">{{ $video->title }}</h1>
    @if ($video->published_at)
        <p class="mt-3 text-sm text-slate-500"><time datetime="{{ $video->published_at->toIso8601String() }}">{{ $video->published_at->translatedFormat('d F Y') }}</time></p>
    @endif
    <div class="mt-5 whitespace-pre-line text-base leading-7 text-slate-700">{{ trim(strip_tags($video->description ?? '')) }}</div>
    <a href="https://www.youtube.com/watch?v={{ $video->youtube_video_id }}" target="_blank" rel="noopener noreferrer" class="mt-6 inline-block font-bold text-brand-navy">Tonton di YouTube ↗</a>
</article>
@endsection
