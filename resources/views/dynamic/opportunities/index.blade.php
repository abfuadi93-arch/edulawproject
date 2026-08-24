@extends('layouts.app')

@section('title', 'Peluang dan Kesempatan Hukum | Edulaw Project')
@section('meta_description', 'Temukan beasiswa, fellowship, magang, kompetisi, call for paper, dan peluang kolaborasi hukum yang telah dikurasi oleh Edulaw Project.')

@section('content')
@php
    $opportunityImage = fn ($opportunity) => $opportunity?->poster_url ?: 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1200&q=85';
    $opportunityUrl = fn ($opportunity) => filled($opportunity?->slug)
        ? route('opportunities.show', $opportunity->slug)
        : route('opportunities.index');
    $isExternalOpportunity = fn ($opportunity) => false;
@endphp

<main class="bg-transparent">
    <x-shared.page-header
        title="Opportunities"
        description="Daftar peluang terbaru yang dikelola dari panel admin."
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Opportunities'],
        ]"
    />

    <section class="py-14 lg:py-20">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if ($featuredOpportunity)
                <article class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="grid lg:grid-cols-[0.8fr_1.2fr]">
                        <img
                            src="{{ $opportunityImage($featuredOpportunity) }}"
                            alt="{{ $featuredOpportunity->title }}"
                            class="h-72 w-full object-cover lg:h-full"
                        >

                        <div class="p-6 sm:p-8">
                            <p class="text-xs font-black uppercase tracking-[0.15em] text-brand-navy">Opportunity Pilihan</p>
                            <h2 class="mt-3 text-2xl font-extrabold text-brand-ink sm:text-3xl">{{ $featuredOpportunity->title }}</h2>
                            <p class="mt-3 text-slate-600">{{ $featuredOpportunity->excerpt }}</p>
                            <div class="mt-4 flex flex-wrap gap-3 text-xs text-slate-500">
                                <span>{{ $featuredOpportunity->display_type }}</span>
                                <span>•</span>
                                <span>{{ optional($featuredOpportunity->deadline)->translatedFormat('d M Y') }}</span>
                                <span>•</span>
                                <span>{{ $featuredOpportunity->format ?: '-' }}</span>
                            </div>
                            <a
                                href="{{ $opportunityUrl($featuredOpportunity) }}"
                                @if ($isExternalOpportunity($featuredOpportunity)) target="_blank" rel="noopener noreferrer" @endif
                                class="mt-6 inline-flex rounded-xl bg-brand-black px-4 py-2 text-sm font-bold text-white"
                            >
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </article>
            @endif

            <form method="GET" action="{{ route('opportunities.index') }}" class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="grid gap-3 md:grid-cols-3">
                    <input type="search" name="q" value="{{ $search }}" placeholder="Cari opportunity..." class="rounded-xl border border-slate-200 px-4 py-2 text-sm">
                    <select name="type" class="rounded-xl border border-slate-200 px-4 py-2 text-sm">
                        <option value="">Semua jenis</option>
                        @foreach (['scholarship' => 'Beasiswa', 'internship' => 'Magang', 'volunteer' => 'Volunteer', 'fellowship' => 'Fellowship', 'call_for_paper' => 'Call for Papers', 'competition' => 'Kompetisi', 'career' => 'Karier', 'open_collaboration' => 'Kolaborasi Terbuka'] as $key => $label)
                            <option value="{{ $key }}" @selected($selectedType === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-xl bg-brand-navy px-4 py-2 text-sm font-bold text-white">Terapkan Filter</button>
                </div>
            </form>

            <div class="space-y-4">
                @forelse ($opportunities as $opportunity)
                    <article class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200">
                        <a
                            href="{{ $opportunityUrl($opportunity) }}"
                            @if ($isExternalOpportunity($opportunity)) target="_blank" rel="noopener noreferrer" @endif
                            class="grid md:grid-cols-[220px_1fr]"
                        >
                            <img
                                src="{{ $opportunityImage($opportunity) }}"
                                alt="{{ $opportunity->title }}"
                                class="h-56 w-full object-cover md:h-full"
                            >

                            <div class="p-5">
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="edulaw-badge edulaw-badge-navy normal-case tracking-normal">{{ $opportunity->display_type }}</span>
                                    <span class="edulaw-badge edulaw-badge-muted normal-case tracking-normal">{{ $opportunity->format ?: '-' }}</span>
                                    <span class="edulaw-badge edulaw-badge-sky normal-case tracking-normal">{{ $opportunity->display_status }}</span>
                                </div>
                                <h3 class="mt-3 text-xl font-extrabold text-brand-ink">{{ $opportunity->title }}</h3>
                                <p class="mt-2 text-sm text-slate-600">{{ $opportunity->excerpt }}</p>
                                <div class="mt-3 text-xs text-slate-500">Batas akhir: {{ optional($opportunity->deadline)->translatedFormat('d M Y') }}</div>
                                <span class="mt-4 inline-flex text-sm font-bold text-brand-navy">Lihat Detail →</span>
                            </div>
                        </a>
                    </article>
                @empty
                    <div class="rounded-2xl bg-white p-6 text-sm text-slate-600 shadow-sm ring-1 ring-slate-200">Belum ada opportunity tersedia.</div>
                @endforelse
            </div>

            {{ $opportunities->links() }}
        </div>
    </section>
</main>
@endsection
