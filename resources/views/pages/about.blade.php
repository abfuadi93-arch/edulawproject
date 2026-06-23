@extends('layouts.app')

@section('title', 'Tentang - Edulaw Project')

@section('content')
@php
    $stats = [
        ['value' => '21+', 'label' => 'Program', 'icon' => 'calendar'],
        ['value' => '300+', 'label' => 'Publikasi', 'icon' => 'book'],
        ['value' => '3.800+', 'label' => 'Peserta', 'icon' => 'users'],
        ['value' => '23', 'label' => 'Kolaborasi', 'icon' => 'handshake'],
        ['value' => '10+', 'label' => 'Diskusi Publik', 'icon' => 'chat'],
        ['value' => '1.200+', 'label' => 'Konten Edukasi', 'icon' => 'play'],
    ];

    $leaders = [
        [
            'name' => 'Abdul Basid Fuadi',
            'role' => 'Founder',
            'image' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=500&q=85',
        ],
        [
            'name' => 'Azmi Fathu Rohman',
            'role' => 'Co-Founder',
            'image' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=500&q=85',
        ],
        [
            'name' => 'Faraz Almira Areila',
            'role' => 'Co-Founder',
            'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=500&q=85',
        ],
        [
            'name' => 'Umi Zakia Azzahro',
            'role' => 'Co-Founder',
            'image' => 'https://images.unsplash.com/photo-1531123897727-8f129e1688ce?auto=format&fit=crop&w=500&q=85',
        ],
    ];

    $managers = [
        [
            'name' => 'Nabila Rahma',
            'role' => 'Manager Program',
            'description' => 'Mengelola perencanaan, pelaksanaan, dan evaluasi program edukasi hukum.',
            'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=500&q=85',
        ],
        [
            'name' => 'Ricky Pratama',
            'role' => 'Manager Riset & Publikasi',
            'description' => 'Memimpin proses riset, kajian kebijakan, dan publikasi berbasis data.',
            'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=500&q=85',
        ],
        [
            'name' => 'Dewi Safitri',
            'role' => 'Manager Insight Editorial',
            'description' => 'Mengelola konten editorial, analisis hukum, dan pengembangan penulis.',
            'image' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=500&q=85',
        ],
        [
            'name' => 'Fauzan Aditya',
            'role' => 'Manager Multimedia',
            'description' => 'Mengelola produksi audiovisual, media sosial, dokumentasi, dan distribusi konten.',
            'image' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=500&q=85',
        ],
    ];

    $teamMembers = [
        ['name' => 'Raihan Malik', 'role' => 'Officer Program', 'description' => 'Mendukung operasional program dan koordinasi peserta kegiatan.', 'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=500&q=85'],
        ['name' => 'Salsabila H.', 'role' => 'Officer Riset', 'description' => 'Mendukung pengumpulan data, analisis awal, dan penyusunan kajian.', 'image' => 'https://images.unsplash.com/photo-1548142813-c348350df52b?auto=format&fit=crop&w=500&q=85'],
        ['name' => 'Hafizh Acmi', 'role' => 'Officer Publikasi', 'description' => 'Mengelola proses penyuntingan naskah dan layout publikasi.', 'image' => 'https://images.unsplash.com/photo-1507591064344-4c6ce005b128?auto=format&fit=crop&w=500&q=85'],
        ['name' => 'Putri Aulia', 'role' => 'Officer Insight', 'description' => 'Mendukung produksi konten artikel dan analisis hukum.', 'image' => 'https://images.unsplash.com/photo-1534751516642-a1af1ef26a56?auto=format&fit=crop&w=500&q=85'],
        ['name' => 'Daniel Hermawan', 'role' => 'Officer Multimedia', 'description' => 'Mendukung produksi video, desain grafis, dan sosial media harian.', 'image' => 'https://images.unsplash.com/photo-1519345182560-3f2917c472ef?auto=format&fit=crop&w=500&q=85'],
        ['name' => 'Nadira Putri', 'role' => 'Writer', 'description' => 'Menulis artikel edukatif, insight hukum, dan feature tematik.', 'image' => 'https://images.unsplash.com/photo-1544725176-7c40e5a71c5e?auto=format&fit=crop&w=500&q=85'],
        ['name' => 'Firmansyah', 'role' => 'Writer', 'description' => 'Menulis konten riset, kebijakan publik, dan literasi hukum.', 'image' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=500&q=85'],
        ['name' => 'Zahra Putri', 'role' => 'Writer', 'description' => 'Menulis konten populer, infografis, dan serial edukasi hukum.', 'image' => 'https://images.unsplash.com/photo-1531123897727-8f129e1688ce?auto=format&fit=crop&w=500&q=85'],
        ['name' => 'Ricky Maulana', 'role' => 'Designer', 'description' => 'Merancang visual konten, infografis, dan layout publikasi digital.', 'image' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=500&q=85'],
        ['name' => 'Alya Humaira', 'role' => 'Designer', 'description' => 'Mendukung desain grafis, branding, dan kebutuhan visual.', 'image' => 'https://images.unsplash.com/photo-1499952127939-9bbf5af6c51c?auto=format&fit=crop&w=500&q=85'],
        ['name' => 'Ikhsan Pradipta', 'role' => 'Content Support', 'description' => 'Mendukung riset konten, fact-checking, dan manajemen data.', 'image' => 'https://images.unsplash.com/photo-1531891437562-4301cf35b7e4?auto=format&fit=crop&w=500&q=85'],
        ['name' => 'Vina Oktaviani', 'role' => 'Content Support', 'description' => 'Membantu pengelolaan konten dan koordinasi dengan kontributor.', 'image' => 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=500&q=85'],
    ];

    $focusAreas = [
        ['title' => 'Literasi Hukum', 'description' => 'Materi belajar yang ringkas, kontekstual, dan mudah digunakan.', 'icon' => 'book'],
        ['title' => 'Riset Kebijakan', 'description' => 'Kajian berbasis regulasi, putusan, data, dan kebutuhan publik.', 'icon' => 'chart'],
        ['title' => 'Insight Editorial', 'description' => 'Esai dan analisis hukum dengan gaya akademik yang tetap terbaca.', 'icon' => 'pen'],
        ['title' => 'Kolaborasi Publik', 'description' => 'Ruang kerja bersama untuk diskusi, advokasi, dan penguatan komunitas.', 'icon' => 'users'],
    ];

    $timeline = [
        ['year' => '2021', 'title' => 'Gagasan Awal', 'description' => 'Forum virtual dan ruang diskusi kecil mulai dijalankan sebagai ruang membaca dan berdiskusi.'],
        ['year' => '2022', 'title' => 'Pengembangan Komunitas', 'description' => 'Penguatan forum dan pengembangan pembelajaran hukum kolaboratif mulai tertata.'],
        ['year' => '2023', 'title' => 'Edulaw Project Didirikan', 'description' => 'Pada 23 Juni 2023, Edulaw Project resmi hadir sebagai platform edukasi hukum independen.'],
        ['year' => '2024', 'title' => 'Ekspansi Program', 'description' => 'Diskusi Literasi Konstitusi, editorial insight, dan kolaborasi publik mulai berkembang.'],
        ['year' => '2025', 'title' => 'Transformasi Digital', 'description' => 'Pengembangan website dan ekosistem publikasi digital dilakukan untuk memperluas akses pengetahuan hukum.'],
    ];

    $timelineMeta = [
        ['title' => 'Didirikan', 'subtitle' => '23 Juni 2023', 'icon' => 'calendar'],
        ['title' => 'Karakter', 'subtitle' => 'Independen, edukatif, dan kolaboratif.', 'icon' => 'users'],
        ['title' => 'Fokus', 'subtitle' => 'Literasi hukum dan kebijakan publik', 'icon' => 'focus'],
    ];

    $paragraphs = fn (?string $body) => collect(preg_split("/\R{2,}/", trim((string) $body)))
        ->map(fn ($paragraph) => trim($paragraph))
        ->filter()
        ->values();

    $statsBlocks = collect($aboutStats ?? []);
    $leaderBlocks = collect($aboutLeaders ?? []);
    $managerBlocks = collect($aboutManagers ?? []);
    $teamBlocks = collect($aboutTeamMembers ?? []);
    $focusBlocks = collect($aboutFocusAreas ?? []);
    $timelineBlocks = collect($aboutTimeline ?? []);
    $timelineMetaBlocks = collect($aboutTimelineMeta ?? []);

    if ($statsBlocks->isNotEmpty()) {
        $stats = $statsBlocks->map(fn ($block) => [
            'value' => $block->subtitle,
            'label' => $block->title,
            'icon' => $block->icon ?: 'book',
        ])->all();
    }

    if ($leaderBlocks->isNotEmpty()) {
        $leaders = $leaderBlocks->map(fn ($block) => [
            'name' => $block->title,
            'role' => $block->subtitle,
            'image' => $block->image_url,
        ])->all();
    }

    if ($managerBlocks->isNotEmpty()) {
        $managers = $managerBlocks->map(fn ($block) => [
            'name' => $block->title,
            'role' => $block->subtitle,
            'description' => $block->body,
            'image' => $block->image_url,
        ])->all();
    }

    if ($teamBlocks->isNotEmpty()) {
        $teamMembers = $teamBlocks->map(fn ($block) => [
            'name' => $block->title,
            'role' => $block->subtitle,
            'description' => $block->body,
            'image' => $block->image_url,
        ])->all();
    }

    if ($focusBlocks->isNotEmpty()) {
        $focusAreas = $focusBlocks->map(fn ($block) => [
            'title' => $block->title,
            'description' => $block->body,
            'icon' => $block->icon ?: 'book',
        ])->all();
    }

    if ($timelineBlocks->isNotEmpty()) {
        $timeline = $timelineBlocks->map(fn ($block) => [
            'year' => $block->eyebrow,
            'title' => $block->title,
            'description' => $block->body,
        ])->all();
    }

    if ($timelineMetaBlocks->isNotEmpty()) {
        $timelineMeta = $timelineMetaBlocks->map(fn ($block) => [
            'title' => $block->title,
            'subtitle' => $block->subtitle,
            'icon' => $block->icon ?: 'focus',
        ])->all();
    }

    $aboutHeroParagraphs = $paragraphs($aboutHero?->body);
    $aboutWhyParagraphs = $paragraphs($aboutWhy?->body);
@endphp

<main class="bg-white text-slate-950">
    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-slate-200 bg-white">
        <div class="absolute inset-y-0 left-0 hidden w-[32%] overflow-hidden lg:block">
            <img
                src="{{ $aboutHero?->image_url ?? 'https://images.unsplash.com/photo-1505664194779-8beaceb93744?auto=format&fit=crop&w=900&q=85' }}"
                alt="{{ $aboutHero?->image_alt ?? 'Perpustakaan hukum Edulaw Project' }}"
                class="h-full w-full object-cover"
            >
            <div class="absolute inset-0 bg-linear-to-r from-white/20 via-white/80 to-white"></div>
        </div>

        <div class="relative mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[0.95fr_1.05fr] lg:px-8 lg:py-10">
            <div class="lg:pl-24">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-amber-600">
                    {{ $aboutHero?->eyebrow ?? 'Tentang Kami' }}
                </p>

                <h1 class="mt-2 text-4xl font-black leading-tight tracking-tight text-slate-950 sm:text-5xl lg:text-[3.6rem]">
                    {{ $aboutHero?->title ?? 'Edulaw Project' }}
                </h1>

                <div class="mt-4 max-w-xl space-y-4 text-sm leading-7 text-slate-700">
                    @forelse ($aboutHeroParagraphs as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @empty
                        <p>
                            Edulaw Project adalah platform literasi hukum digital yang berfokus pada penyajian edukasi hukum, riset kebijakan, publikasi, program pengembangan kapasitas, dan kanal pengembangan hukum yang aplikatif.
                        </p>

                        <p>
                            Melalui pendekatan kolaboratif dan berbasis data, kami membangun ekosistem pengetahuan hukum yang inklusif, kritis, dan berdampak.
                        </p>
                    @endforelse
                </div>

                <div class="mt-5 space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-7-4.35-9.33-9.1C.8 8.05 3.42 4 7.5 4A5.3 5.3 0 0 1 12 6.35 5.3 5.3 0 0 1 16.5 4c4.08 0 6.7 4.05 4.83 7.9C19 16.65 12 21 12 21Z"/></svg>
                        </span>
                        <p class="text-sm font-semibold text-slate-800">
                            Nilai inti: <span class="font-medium italic text-slate-600">Equal, Educative, Embrace.</span>
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M8 3h8l3 3v15H5V3h3Z" stroke="currentColor" stroke-width="2"/><path d="M9 13h6M9 17h4M15 3v4h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </span>
                        <p class="text-sm font-semibold text-slate-800">
                            Berbasis Bukti: <span class="font-medium text-slate-600">rujukan, data, dan integritas.</span>
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M12 21a9 9 0 1 0-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 7v5l3 2M3 21l4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </span>
                        <p class="text-sm font-semibold text-slate-800">
                            Orientasi solusi: <span class="font-medium text-slate-600">rekomendasi yang dapat dieksekusi.</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-xl shadow-slate-900/5">
                <h2 class="text-center text-lg font-black text-slate-950">
                    Edulaw dalam Angka
                </h2>

                <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-3">
                    @foreach ($stats as $stat)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                                    @if ($stat['icon'] === 'calendar')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M7 3v4M17 3v4M4 9h16M5 5h14v16H5V5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    @elseif ($stat['icon'] === 'book')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" stroke="currentColor" stroke-width="2"/></svg>
                                    @elseif ($stat['icon'] === 'users')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M16 11a4 4 0 1 0-8 0M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    @elseif ($stat['icon'] === 'handshake')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M8 12l3 3a2 2 0 0 0 3 0l4-4M3 12l5-5 4 4M21 12l-5-5-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    @elseif ($stat['icon'] === 'chat')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M21 12a8 8 0 0 1-8 8H7l-4 3 1.5-5A8 8 0 1 1 21 12Z" stroke="currentColor" stroke-width="2"/></svg>
                                    @else
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M8 5v14l11-7-11-7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                                    @endif
                                </span>

                                <div>
                                    <p class="text-xs font-bold text-slate-600">
                                        {{ $stat['label'] }}
                                    </p>
                                    <p class="text-2xl font-black text-slate-950">
                                        {{ $stat['value'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5">
                    <h3 class="text-center text-base font-black text-slate-950">
                        Penggerak Edulaw Project
                    </h3>

                    <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
                        @foreach ($leaders as $leader)
                            <div class="text-center">
                                <div class="mx-auto h-24 w-full overflow-hidden rounded-2xl bg-slate-100">
                                    <img
                                        src="{{ $leader['image'] }}"
                                        alt="{{ $leader['name'] }}"
                                        class="h-full w-full object-cover object-top"
                                    >
                                </div>
                                <h4 class="mt-2 text-xs font-black leading-tight text-slate-950">
                                    {{ $leader['name'] }}
                                </h4>
                                <p class="text-[11px] font-bold text-slate-500">
                                    {{ $leader['role'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Team --}}
    <section id="tim" class="border-b border-slate-200 bg-white py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:gap-8">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-amber-600">
                        Tim Kami
                    </p>
                    <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">
                        Tim Kami
                    </h2>
                </div>
                <p class="max-w-3xl text-sm leading-6 text-slate-600">
                    Tim yang bekerja di balik pengelolaan program, riset, publikasi, konten, dan kolaborasi Edulaw Project.
                </p>
            </div>

            <h3 class="mt-6 text-lg font-black text-slate-950">
                Manager
            </h3>

            <div class="mt-3 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @foreach ($managers as $manager)
                    <article class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <img
                            src="{{ $manager['image'] }}"
                            alt="{{ $manager['name'] }}"
                            class="h-24 w-20 shrink-0 rounded-xl object-cover object-top"
                        >

                        <div>
                            <h4 class="text-sm font-black text-slate-950">
                                {{ $manager['name'] }}
                            </h4>
                            <p class="mt-1 text-xs font-bold text-slate-600">
                                {{ $manager['role'] }}
                            </p>
                            <p class="mt-2 text-xs leading-5 text-slate-600">
                                {{ $manager['description'] }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>

            <h3 class="mt-6 border-t border-slate-200 pt-5 text-lg font-black text-slate-950">
                Officer, Writer, &amp; Designer
            </h3>

            <div class="mt-3 grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
                @foreach ($teamMembers as $member)
                    <article class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                        <img
                            src="{{ $member['image'] }}"
                            alt="{{ $member['name'] }}"
                            class="mx-auto h-20 w-20 rounded-xl object-cover object-top"
                        >

                        <h4 class="mt-3 text-sm font-black leading-tight text-slate-950">
                            {{ $member['name'] }}
                        </h4>

                        <p class="mt-1 text-xs font-bold text-slate-600">
                            {{ $member['role'] }}
                        </p>

                        <p class="mt-2 text-xs leading-5 text-slate-600">
                            {{ $member['description'] }}
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Why --}}
    <section class="border-b border-slate-200 bg-white py-8">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[0.55fr_1.45fr] lg:px-8">
            <div class="border-r border-slate-200 pr-8">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-amber-600">
                    {{ $aboutWhy?->eyebrow ?? 'Mengapa' }}
                </p>

                <h2 class="mt-2 text-3xl font-black leading-tight tracking-tight text-slate-950">
                    {{ $aboutWhy?->title ?? 'Mengapa Edulaw Hadir?' }}
                </h2>

                <div class="mt-5 flex justify-center text-indigo-200">
                    <svg class="h-16 w-16" viewBox="0 0 64 64" fill="none">
                        <path d="M32 8v48M16 18h32M20 18 12 38h16L20 18Zm24 0-8 20h16l-8-20Z" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>

            <div class="self-center text-base leading-8 text-slate-700">
                @forelse ($aboutWhyParagraphs as $paragraph)
                    <p @class(['mt-4' => ! $loop->first])>{{ $paragraph }}</p>
                @empty
                    <p>
                        Hukum sering hadir dalam bahasa yang teknis, tertutup, dan sulit dijangkau publik. Padahal,
                        kualitas demokrasi dan kewargaan sangat bergantung pada kemampuan masyarakat memahami hak,
                        kewajiban, serta arah kebijakan negara.
                    </p>

                    <p class="mt-4">
                        Edulaw Project hadir untuk menjembatani pengetahuan hukum, riset kebijakan, dan kebutuhan
                        masyarakat atas informasi yang jernih, reflektif, serta dapat digunakan dalam pembelajaran,
                        diskusi publik, dan advokasi berbasis pengetahuan.
                    </p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Focus --}}
    <section class="border-b border-slate-200 bg-white py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-amber-600">
                {{ $aboutFocusIntro?->eyebrow ?? 'Fokus Kerja' }}
            </p>

            <h2 class="mt-2 text-3xl font-black leading-tight tracking-tight text-slate-950">
                {{ $aboutFocusIntro?->title ?? 'Dari Literasi Hukum Menuju Pengetahuan Publik' }}
            </h2>

            <div class="mt-5 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @foreach ($focusAreas as $item)
                    <article class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-700">
                            @if ($item['icon'] === 'book')
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" stroke="currentColor" stroke-width="2"/></svg>
                            @elseif ($item['icon'] === 'chart')
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none"><path d="M4 19V5m0 14h16M8 16v-4m5 4V8m5 8v-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            @elseif ($item['icon'] === 'pen')
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none"><path d="m4 20 4-1 11-11a2.8 2.8 0 0 0-4-4L4 15v5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                            @else
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none"><path d="M16 11a4 4 0 1 0-8 0M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="2"/></svg>
                            @endif
                        </span>

                        <div>
                            <h3 class="text-sm font-black text-slate-950">
                                {{ $item['title'] }}
                            </h3>
                            <p class="mt-1 text-xs leading-5 text-slate-600">
                                {{ $item['description'] }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Timeline --}}
    <section class="bg-white py-8">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1.3fr_0.7fr] lg:px-8">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-amber-600">
                    {{ $aboutTimelineIntro?->eyebrow ?? 'Perjalanan Edulaw' }}
                </p>

                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                    {{ $aboutTimelineIntro?->title ?? 'Dari forum kecil menuju ekosistem literasi hukum.' }}
                </h2>

                <div class="mt-5 space-y-3">
                    @foreach ($timeline as $item)
                        <div class="grid grid-cols-[80px_1fr] gap-4">
                            <div class="relative flex items-start gap-3">
                                <span class="mt-1 h-3 w-3 rounded-full bg-blue-600"></span>
                                <span class="text-sm font-black text-blue-700">
                                    {{ $item['year'] }}
                                </span>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-white px-5 py-3 shadow-sm">
                                <h3 class="text-sm font-black text-slate-950">
                                    {{ $item['title'] }}
                                </h3>
                                <p class="mt-1 text-xs leading-5 text-slate-600">
                                    {{ $item['description'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <aside class="self-end rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="space-y-6">
                    @foreach ($timelineMeta as $meta)
                        <div class="flex gap-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                                @if ($meta['icon'] === 'calendar')
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"><path d="M7 3v4M17 3v4M4 9h16M5 5h14v16H5V5Z" stroke="currentColor" stroke-width="2"/></svg>
                                @elseif ($meta['icon'] === 'users')
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"><path d="M16 11a4 4 0 1 0-8 0M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="2"/></svg>
                                @else
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"><path d="M12 3v18M3 12h18M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                @endif
                            </span>
                            <div>
                                <p class="text-xs text-slate-500">{{ $meta['title'] }}</p>
                                <p class="text-sm font-black text-slate-950">{{ $meta['subtitle'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </aside>
        </div>
    </section>

    {{-- CTA --}}
    <x-shared.cta-collaboration :block="$sharedCta" />
</main>
@endsection
