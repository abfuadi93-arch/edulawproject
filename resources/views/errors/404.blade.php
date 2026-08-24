@extends('layouts.app')

@section('title', 'Halaman Tidak Ditemukan | Edulaw Project')
@section('meta_description', 'Halaman yang Anda cari tidak ditemukan. Kembali ke Edulaw Project untuk menjelajahi editorial, riset, publikasi, dan program hukum.')
@section('robots', 'noindex,nofollow')

@section('content')
@php
    $quickLinks = [
        [
            'title' => 'Editorial',
            'description' => 'Analisis hukum dan pembaruan isu publik.',
            'url' => route('insights.index'),
        ],
        [
            'title' => 'Riset & Publikasi',
            'description' => 'Kajian, artikel jurnal, dan buku digital.',
            'url' => route('publications.index'),
        ],
        [
            'title' => 'Program',
            'description' => 'Kelas, diskusi, dan pelatihan hukum.',
            'url' => route('programs.index'),
        ],
        [
            'title' => 'Opportunities',
            'description' => 'Peluang pengembangan yang masih tersedia.',
            'url' => route('opportunities.index'),
        ],
    ];
@endphp

<main class="overflow-x-clip bg-[#f7f8fa] text-brand-ink">
    <x-shared.page-header
        title="Halaman tidak ditemukan"
        :compact="true"
        eyebrow="Error 404"
        :channel-header="true"
        grid-class="gap-5 px-5 py-7 sm:w-full sm:px-6 sm:py-8 lg:min-h-[240px] lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center lg:px-8 lg:py-8"
        description="Alamat yang Anda buka tidak tersedia, telah dipindahkan, atau mungkin belum tepat."
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => '404'],
        ]"
    >
        <p class="font-display text-7xl font-black leading-none tracking-[-0.06em] text-brand-amber sm:text-8xl" aria-hidden="true">404</p>
    </x-shared.page-header>

    <section class="py-9 sm:py-10 lg:py-11">
        <div class="section-shell grid gap-5 lg:grid-cols-[0.42fr_0.58fr] lg:items-stretch">
            <div class="flex flex-col justify-between rounded-[14px] bg-white p-6 sm:p-7">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-teal">Arah Berikutnya</p>
                    <h2 class="mt-2 font-display text-2xl font-black leading-tight text-brand-navy sm:text-3xl">Mari kembali ke ruang pengetahuan Edulaw.</h2>
                    <p class="mt-3 text-base leading-7 text-slate-600">Gunakan pencarian untuk menemukan konten tertentu atau kembali ke halaman utama untuk melanjutkan penelusuran.</p>
                </div>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-brand-navy px-5 py-3 text-sm font-black text-white transition hover:bg-[#294f82]">
                        Kembali ke Beranda <span aria-hidden="true">→</span>
                    </a>
                    <a href="{{ route('search.index') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-5 py-3 text-sm font-black text-brand-navy transition hover:border-brand-teal/40 hover:bg-brand-teal-soft">
                        Cari Konten
                    </a>
                </div>
            </div>

            <nav class="rounded-[14px] bg-brand-navy p-5 sm:p-6" aria-label="Kanal utama Edulaw">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-amber">Jelajahi Kanal</p>
                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    @foreach ($quickLinks as $link)
                        <a href="{{ $link['url'] }}" class="group rounded-[12px] bg-white/8 p-4 transition hover:bg-white/12">
                            <span class="flex items-center justify-between gap-4">
                                <span class="text-base font-black text-white">{{ $link['title'] }}</span>
                                <span class="text-brand-amber transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
                            </span>
                            <span class="mt-1 block text-sm leading-6 text-white/65">{{ $link['description'] }}</span>
                        </a>
                    @endforeach
                </div>

                <div class="mt-4 flex flex-col gap-2 border-t border-white/12 pt-4 text-sm text-white/70 sm:flex-row sm:items-center sm:justify-between">
                    <span>Masih membutuhkan bantuan?</span>
                    <a href="{{ route('contact.index') }}" class="font-black text-white transition hover:text-brand-amber">Hubungi Edulaw →</a>
                </div>
            </nav>
        </div>
    </section>
</main>
@endsection
