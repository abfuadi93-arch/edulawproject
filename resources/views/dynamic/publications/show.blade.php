@extends('layouts.app')

@section('title', $publication->title.' - Edulaw Project')

@section('content')
@php
    $publicationImage = $publication->cover_image_url ?: 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1600&q=85';
@endphp

<main class="bg-brand-paper">
    <x-shared.page-header
        :title="$publication->title"
        :eyebrow="$publication->type?->name"
        container-class="relative z-10 mx-auto grid w-full max-w-7xl grid-cols-1 gap-0 px-5 py-11 sm:px-6 lg:min-h-[300px] lg:px-8 lg:py-16"
        title-width-class="max-w-none"
    />

    <section class="py-14 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_320px] lg:px-8">
            <article class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200">
                <img src="{{ $publicationImage }}" alt="{{ $publication->title }}" class="h-72 w-full object-cover">

                <div class="p-6 sm:p-8">
                <h2 class="text-2xl font-extrabold text-brand-ink">Ringkasan</h2>
                <p class="edulaw-readable mt-4 text-slate-600">{{ $publication->excerpt }}</p>

                @if ($publication->description)
                    <div class="edulaw-readable prose prose-slate mt-6 max-w-none">
                        {!! $publication->description !!}
                    </div>
                @endif

                @if ($publication->tags->isNotEmpty())
                    <div class="mt-8 flex flex-wrap gap-2">
                        @foreach ($publication->tags as $tag)
                            <span class="edulaw-badge edulaw-badge-muted normal-case tracking-normal">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                @endif
                </div>
            </article>

            <aside class="space-y-4">
                <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-sm font-black uppercase tracking-[0.15em] text-brand-navy">Informasi</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div><dt class="text-slate-500">Tipe</dt><dd class="font-bold text-brand-ink">{{ $publication->type?->name ?? '-' }}</dd></div>
                        <div><dt class="text-slate-500">Tanggal</dt><dd class="font-bold text-brand-ink">{{ optional($publication->published_at)->translatedFormat('d M Y') }}</dd></div>
                        <div><dt class="text-slate-500">Halaman</dt><dd class="font-bold text-brand-ink">{{ $publication->page_count ? $publication->page_count.' halaman' : '-' }}</dd></div>
                        <div><dt class="text-slate-500">Penulis</dt><dd class="font-bold text-brand-ink">{{ $publication->authors->pluck('name')->join(', ') ?: 'Edulaw Project' }}</dd></div>
                    </dl>

                    @php($downloadUrl = $publication->download_url)
                    @if ($downloadUrl)
                        <a href="{{ $downloadUrl }}" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex w-full justify-center rounded-xl bg-brand-black px-4 py-2 text-sm font-bold text-white">Unduh / Buka Publikasi</a>
                    @endif
                </div>
            </aside>
        </div>
    </section>

    <section class="bg-white py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-extrabold text-brand-ink">Publikasi Terkait</h2>
            <div class="mt-6 grid gap-5 md:grid-cols-3">
                @forelse ($relatedPublications as $item)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-navy">{{ $item->type?->name ?? 'Publikasi' }}</p>
                        <h3 class="mt-2 font-extrabold text-brand-ink">{{ $item->title }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $item->excerpt }}</p>
                        <a href="{{ route('publications.show', $item->slug) }}" class="mt-3 inline-flex text-sm font-bold text-brand-navy">Lihat Detail →</a>
                    </article>
                @empty
                    <p class="text-sm text-slate-600">Tidak ada publikasi terkait.</p>
                @endforelse
            </div>
        </div>
    </section>
</main>
@endsection
