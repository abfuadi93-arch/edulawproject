@extends('layouts.app')

@section('title', 'Riset dan Publikasi Hukum | Edulaw Project')
@section('meta_description', 'Jelajahi hasil riset, policy brief, laporan, dan publikasi hukum Edulaw Project yang menyajikan analisis berbasis bukti untuk kepentingan publik.')

@section('content')
@php
    $publicationImage = fn ($publication) => $publication?->cover_image_url ?: 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1200&q=85';
@endphp

<main class="bg-transparent">
    <x-shared.page-header
        title="Riset & Publikasi"
        description="Konten publikasi yang dikelola dari panel admin Edulaw."
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Riset & Publikasi'],
        ]"
    />

    <section class="py-14 lg:py-20">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if ($featuredPublication)
                <article class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="grid lg:grid-cols-[0.8fr_1.2fr]">
                        <img
                            src="{{ $publicationImage($featuredPublication) }}"
                            alt="{{ $featuredPublication->title }}"
                            class="h-72 w-full object-cover lg:h-full"
                        >

                        <div class="p-6 sm:p-8">
                            <p class="text-xs font-black uppercase tracking-[0.15em] text-brand-navy">Publikasi Pilihan</p>
                            <h2 class="mt-3 text-2xl font-extrabold text-brand-ink sm:text-3xl">{{ $featuredPublication->title }}</h2>
                            <p class="mt-3 text-slate-600">{{ $featuredPublication->excerpt }}</p>
                            <div class="mt-4 flex flex-wrap gap-3 text-xs text-slate-500">
                                <span>{{ $featuredPublication->type?->name }}</span>
                                <span>•</span>
                                <span>{{ $featuredPublication->publication_date_display }}</span>
                            </div>
                            <a href="{{ route('publications.show', $featuredPublication->slug) }}" class="mt-6 inline-flex rounded-xl bg-brand-black px-4 py-2 text-sm font-bold text-white">Baca Detail</a>
                        </div>
                    </div>
                </article>
            @endif

            <form method="GET" action="{{ route('publications.index') }}" class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="grid gap-3 md:grid-cols-3">
                    <input type="search" name="q" value="{{ $search }}" placeholder="Cari publikasi..." class="rounded-xl border border-slate-200 px-4 py-2 text-sm">

                    <select name="type" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">
                        <option value="">Semua tipe</option>
                        @foreach ($publicationTypes as $type)
                            <option value="{{ $type->slug }}" @selected($selectedType === $type->slug)>{{ $type->name }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="rounded-xl bg-brand-navy px-4 py-2 text-sm font-bold text-white">Terapkan Filter</button>
                </div>
            </form>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($publications as $publication)
                    <article class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200">
                        <a href="{{ route('publications.show', $publication->slug) }}" class="block">
                            <img
                                src="{{ $publicationImage($publication) }}"
                                alt="{{ $publication->title }}"
                                class="h-48 w-full object-cover"
                            >
                            <div class="p-5">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-navy">{{ $publication->type?->name ?? 'Publikasi' }}</p>
                                <h3 class="mt-2 text-lg font-extrabold text-brand-ink">{{ $publication->title }}</h3>
                                <p class="mt-2 text-sm text-slate-600">{{ $publication->excerpt }}</p>
                                <p class="mt-3 text-xs text-slate-500">{{ $publication->publication_date_display }}</p>
                                <span class="mt-4 inline-flex text-sm font-bold text-brand-navy">Baca Selengkapnya →</span>
                            </div>
                        </a>
                    </article>
                @empty
                    <div class="md:col-span-2 lg:col-span-3 rounded-2xl bg-white p-6 text-sm text-slate-600 shadow-sm ring-1 ring-slate-200">
                        Belum ada publikasi yang tersedia.
                    </div>
                @endforelse
            </div>

            {{ $publications->links() }}
        </div>
    </section>
</main>
@endsection
