@extends('layouts.app')

@section('title', 'Pencarian Konten | Edulaw Project')
@section('meta_description', 'Cari insight, riset, publikasi, program, multimedia, dan konten hukum lain yang tersedia di situs Edulaw Project secara cepat dan mudah.')
@section('robots', 'noindex,follow')

@section('content')
@php
    $query = request('q');

    $popularKeywords = [
        'Konstitusi',
        'Hak Warga Negara',
        'Regulasi Digital',
        'Hukum Acara',
        'Legal Research',
        'Beasiswa Hukum',
    ];

    $contentTypes = [
        'Semua',
        'Editorial',
        'Riset & Publikasi',
        'Program',
        'Multimedia',
        'Opportunities',
    ];

    $results = [
        [
            'type' => 'Editorial',
            'title' => 'Pembaruan Regulasi Perlindungan Data Pribadi: Apa yang Perlu Diketahui?',
            'excerpt' => 'Menelaah perkembangan terbaru regulasi perlindungan data pribadi di Indonesia dan implikasinya bagi masyarakat, komunitas, dan pengelola data.',
            'date' => '23 Mei 2026',
            'url' => '/insight/pembaruan-regulasi-perlindungan-data-pribadi',
            'meta' => '6 min read',
        ],
        [
            'type' => 'Riset & Publikasi',
            'title' => 'Reformasi Hukum Acara Perdata di Indonesia',
            'excerpt' => 'Kajian ringkas mengenai arah pembaruan hukum acara perdata, efisiensi penyelesaian sengketa, dan penguatan akses keadilan.',
            'date' => 'Mei 2026',
            'url' => '/riset-publikasi/reformasi-hukum-acara-perdata-di-indonesia',
            'meta' => 'Policy Brief',
        ],
        [
            'type' => 'Program',
            'title' => 'DIKSI: Diskusi Literasi Konstitusi',
            'excerpt' => 'Forum diskusi tematik untuk membahas isu konstitusi, hukum publik, demokrasi, dan kebijakan negara secara terbuka dan reflektif.',
            'date' => 'Program berjalan',
            'url' => '/program/diksi-diskusi-literasi-konstitusi',
            'meta' => 'Online',
        ],
        [
            'type' => 'Opportunities',
            'title' => 'Beasiswa Studi Hukum dan Kebijakan Publik',
            'excerpt' => 'Kesempatan pendanaan studi untuk bidang hukum, kebijakan publik, demokrasi, dan tata kelola pemerintahan.',
            'date' => 'Batas akhir: 30 Juli 2026',
            'url' => '/opportunities',
            'meta' => 'Beasiswa',
        ],
        [
            'type' => 'Multimedia',
            'title' => 'Memahami Konstitusi dalam Kehidupan Sehari-hari',
            'excerpt' => 'Video singkat tentang bagaimana konstitusi hadir dalam perlindungan hak warga negara dan praktik kehidupan demokratis.',
            'date' => '24 Mei 2026',
            'url' => 'https://www.youtube.com/',
            'meta' => 'YouTube',
            'external' => true,
        ],
    ];

    $shownResults = $query ? $results : [];
@endphp

<main class="bg-brand-paper">
    {{-- Header --}}
    <x-shared.page-header
        title="Temukan Konten Edulaw"
        eyebrow="Pencarian"
        description="Cari editorial, riset dan publikasi, program, multimedia, opportunities, atau topik hukum yang tersedia di Edulaw Project."
        background-image="https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1800&q=85"
        background-alt="Pencarian konten hukum Edulaw Project"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Pencarian'],
        ]"
    >
        {{-- Search form --}}
        <form
            method="GET"
            action="{{ url('/search') }}"
            class="max-w-2xl rounded-4xl border border-white/15 bg-white p-4 shadow-xl shadow-slate-950/15 sm:p-5"
        >
                <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m21 21-4.3-4.3M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </span>

                        <input
                            type="search"
                            name="q"
                            value="{{ $query }}"
                            placeholder="Cari topik, artikel, publikasi, program..."
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50 py-3.5 pl-12 pr-4 text-sm text-brand-ink outline-none transition placeholder:text-slate-400 focus:border-brand-blue focus:bg-white focus:ring-4 focus:ring-brand-mist"
                        >
                    </div>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-black px-6 py-3.5 text-sm font-extrabold text-white shadow-sm transition hover:bg-brand-navy hover:text-white"
                    >
                        Cari
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </form>
    </x-shared.page-header>

    {{-- Content --}}
    <section class="py-14 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_320px] lg:px-8">
            {{-- Results --}}
            <div>
                @if ($query)
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                                Hasil Pencarian
                            </p>

                            <h2 class="mt-3 text-2xl font-extrabold tracking-tight text-brand-ink sm:text-3xl">
                                Hasil untuk “{{ $query }}”
                            </h2>

                            <p class="mt-3 text-sm leading-6 text-slate-600">
                                Menampilkan hasil pencarian statis sementara. Nanti hasil ini dapat
                                dihubungkan dengan database dan fitur pencarian dinamis.
                            </p>
                        </div>

                        <div class="edulaw-badge edulaw-badge-lg edulaw-badge-neutral normal-case tracking-normal">
                            <span class="h-2 w-2 rounded-full bg-brand-blue"></span>
                            {{ count($shownResults) }} hasil ditemukan
                        </div>
                    </div>

                    <div class="mt-8 space-y-5">
                        @forelse ($shownResults as $result)
                            <article class="group rounded-4xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:border-brand-silver hover:shadow-xl hover:shadow-slate-900/5 sm:p-6">
                                <a
                                    href="{{ str_starts_with($result['url'], 'http') ? $result['url'] : url($result['url']) }}"
                                    @if (!empty($result['external']))
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    @endif
                                    class="block"
                                >
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="edulaw-badge edulaw-badge-navy">
                                            {{ $result['type'] }}
                                        </span>

                                        <span class="edulaw-badge edulaw-badge-muted">
                                            {{ $result['meta'] }}
                                        </span>
                                    </div>

                                    <h3 class="mt-4 text-xl font-extrabold leading-tight text-brand-ink sm:text-2xl">
                                        {{ $result['title'] }}
                                    </h3>

                                    <p class="mt-3 text-sm leading-6 text-slate-600 sm:text-[15px]">
                                        {{ $result['excerpt'] }}
                                    </p>

                                    <div class="mt-5 flex flex-wrap items-center justify-between gap-4">
                                        <p class="text-xs font-semibold text-slate-500">
                                            {{ $result['date'] }}
                                        </p>

                                        <span class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-ink transition group-hover:text-brand-ink">
                                            Buka
                                            <svg class="h-4 w-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </div>
                                </a>
                            </article>
                        @empty
                            <div class="rounded-4xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-mist text-brand-ink">
                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="m21 21-4.3-4.3M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </div>

                                <h2 class="mt-5 text-2xl font-extrabold text-brand-ink">
                                    Tidak ada hasil ditemukan.
                                </h2>

                                <p class="mt-3 text-sm leading-6 text-slate-600">
                                    Coba gunakan kata kunci lain atau jelajahi kanal utama Edulaw Project.
                                </p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Static pagination placeholder --}}
                    @if (count($shownResults) > 0)
                        <div class="mt-10 flex justify-center">
                            <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white p-1 shadow-sm">
                                <button class="rounded-full px-4 py-2 text-sm font-semibold text-slate-400" disabled>
                                    Sebelumnya
                                </button>
                                <button class="rounded-full bg-brand-black px-4 py-2 text-sm font-bold text-white">
                                    1
                                </button>
                                <button class="rounded-full px-4 py-2 text-sm font-semibold text-slate-400" disabled>
                                    Berikutnya
                                </button>
                            </div>
                        </div>
                    @endif
                @else
                    {{-- Empty state before search --}}
                    <div class="rounded-4xl border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-navy text-white">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m21 21-4.3-4.3M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </div>

                        <h2 class="mt-6 max-w-2xl text-3xl font-extrabold leading-tight tracking-tight text-brand-ink sm:text-4xl">
                            Masukkan kata kunci untuk mulai mencari.
                        </h2>

                        <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">
                            Gunakan kolom pencarian di atas untuk menemukan konten tentang hukum,
                            kebijakan publik, konstitusi, program, publikasi, atau opportunities.
                        </p>

                        <div class="mt-8">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                                Kata Kunci Populer
                            </p>

                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach ($popularKeywords as $keyword)
                                    <a
                                        href="{{ url('/search') }}?q={{ urlencode($keyword) }}"
                                        class="edulaw-badge edulaw-badge-lg edulaw-badge-muted normal-case tracking-normal transition hover:border-brand-navy hover:bg-brand-mist hover:text-brand-navy"
                                    >
                                        {{ $keyword }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <aside class="space-y-6">
                <div class="rounded-4xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                        Jenis Konten
                    </p>

                    <div class="mt-5 grid gap-3">
                        @foreach ($contentTypes as $index => $type)
                            <button
                                type="button"
                                class="flex items-center justify-between rounded-2xl border px-4 py-3 text-left text-sm font-bold transition
                                    {{ $index === 0
                                        ? 'border-brand-black bg-brand-black text-white'
                                        : 'border-slate-200 bg-white text-slate-700 hover:border-brand-silver hover:bg-brand-paper hover:text-brand-ink' }}"
                            >
                                <span>{{ $type }}</span>
                                <span class="{{ $index === 0 ? 'text-white/60' : 'text-slate-400' }}">→</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-4xl bg-brand-navy p-6 text-white shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-silver">
                        Jelajahi Kanal
                    </p>

                    <h3 class="mt-4 text-xl font-extrabold leading-tight">
                        Temukan konten berdasarkan kanal utama.
                    </h3>

                    <div class="mt-6 grid gap-3">
                        <a
                            href="{{ url('/insight') }}"
                            class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/10"
                        >
                            Editorial
                        </a>

                        <a
                            href="{{ url('/riset-publikasi') }}"
                            class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/10"
                        >
                            Riset &amp; Publikasi
                        </a>

                        <a
                            href="{{ url('/program') }}"
                            class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/10"
                        >
                            Program
                        </a>

                        <a
                            href="{{ url('/opportunities') }}"
                            class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/10"
                        >
                            Opportunities
                        </a>

                        <a
                            href="{{ url('/multimedia') }}"
                            class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/10"
                        >
                            Multimedia
                        </a>
                    </div>
                </div>

                <x-shared.cta-card
                    eyebrow="Belum Menemukan?"
                    title="Hubungi Edulaw Project."
                    body="Sampaikan pertanyaan atau kebutuhan informasi melalui halaman kontak."
                    :url="url('/kontak')"
                    label="Hubungi Kami"
                />
            </aside>
        </div>
    </section>
</main>
@endsection
