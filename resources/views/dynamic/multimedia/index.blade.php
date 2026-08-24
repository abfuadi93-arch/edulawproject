@extends('layouts.app')

@section('title', 'Multimedia Edukasi Hukum | Edulaw Project')
@section('meta_description', 'Tonton video, dokumentasi, dan konten visual Edulaw Project yang membahas hukum, kebijakan publik, riset, serta edukasi untuk masyarakat.')

@section('content')
@php
    $multimediaImage = fn ($item) => $item?->thumbnail_url ?: 'https://images.unsplash.com/photo-1551818255-e6e10975bc17?auto=format&fit=crop&w=1200&q=85';
@endphp

<main class="bg-transparent">
    <x-shared.page-header
        title="Multimedia"
        description="Konten multimedia terbaru dari panel admin."
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Multimedia'],
        ]"
    />

    <section class="py-14 lg:py-20">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if ($featured)
                <article class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="grid lg:grid-cols-[1fr_1fr]">
                        <div class="relative h-72 overflow-hidden bg-slate-200 lg:h-full">
                            <img src="{{ $multimediaImage($featured) }}" alt="{{ $featured->title }}" class="h-full w-full object-cover">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="flex h-16 w-16 items-center justify-center rounded-full bg-white/90 text-brand-black shadow-xl">
                                    <svg class="ml-1 h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M8 5v14l11-7L8 5Z"/>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div class="p-6 sm:p-8">
                            <p class="text-xs font-black uppercase tracking-[0.15em] text-brand-navy">Konten Pilihan</p>
                            <h2 class="mt-3 text-2xl font-extrabold text-brand-ink sm:text-3xl">{{ $featured->title }}</h2>
                            <p class="mt-3 text-slate-600">{{ $featured->description }}</p>
                            <div class="mt-4 flex flex-wrap gap-3 text-xs text-slate-500">
                                <span>{{ $featured->display_type }}</span>
                                <span>•</span>
                                <span>{{ $featured->display_platform }}</span>
                                <span>•</span>
                                <span>{{ optional($featured->published_at)->translatedFormat('d M Y') }}</span>
                            </div>
                            @if ($featured->media_url)
                                <a href="{{ $featured->media_url }}" target="_blank" rel="noopener noreferrer" class="mt-6 inline-flex rounded-xl bg-brand-black px-4 py-2 text-sm font-bold text-white">Buka Konten</a>
                            @endif
                        </div>
                    </div>
                </article>
            @endif

            <form method="GET" action="{{ route('multimedia.index') }}" class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="grid gap-3 md:grid-cols-4">
                    <input type="search" name="q" value="{{ $search }}" placeholder="Cari multimedia..." class="rounded-xl border border-slate-200 px-4 py-2 text-sm">

                    <select name="type" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">
                        <option value="">Semua tipe</option>
                        @foreach (['video', 'podcast', 'poster', 'gallery', 'documentation', 'reels', 'shorts'] as $type)
                            <option value="{{ $type }}" @selected($selectedType === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>

                    <select name="platform" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">
                        <option value="">Semua platform</option>
                        @foreach (['youtube', 'instagram', 'tiktok', 'spotify', 'website', 'other'] as $platform)
                            <option value="{{ $platform }}" @selected($selectedPlatform === $platform)>{{ ucfirst($platform) }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="rounded-xl bg-brand-navy px-4 py-2 text-sm font-bold text-white">Terapkan Filter</button>
                </div>
            </form>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($multimediaItems as $item)
                    <article class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200">
                        <img src="{{ $multimediaImage($item) }}" alt="{{ $item->title }}" class="h-48 w-full object-cover">

                        <div class="p-5">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-navy">{{ $item->display_type }}</p>
                            <h3 class="mt-2 text-lg font-extrabold text-brand-ink">{{ $item->title }}</h3>
                            <p class="mt-2 text-sm text-slate-600">{{ $item->description }}</p>
                            <p class="mt-3 text-xs text-slate-500">{{ optional($item->published_at)->translatedFormat('d M Y') }} • {{ $item->display_platform }}</p>
                            @if ($item->media_url)
                                <a href="{{ $item->media_url }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex text-sm font-bold text-brand-navy">Buka Konten →</a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="md:col-span-2 lg:col-span-3 rounded-2xl bg-white p-6 text-sm text-slate-600 shadow-sm ring-1 ring-slate-200">Belum ada konten multimedia dipublikasikan.</div>
                @endforelse
            </div>

            {{ $multimediaItems->links() }}
        </div>
    </section>
</main>
@endsection
