@extends('layouts.app')

@section('title', $program->title . ' - Program Edulaw')

@section('content')
@php
    $programTitle = $program->display_title ?: $program->title;
    $statusLabel = $program->display_status ?: 'Terjadwal';
    $durationLabel = '1 Pertemuan';
    $formatLabel = $program->display_format ?: '-';
    $levelLabel = $program->level ?: '-';
    $eventDateLabel = $program->started_at ? $program->started_at->translatedFormat('d F Y') : '-';
    $languageLabel = $program->language ?: 'Indonesia';

    $statusClass = function ($status) {
        return match ($status) {
            'Berjalan' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'Segera Dibuka' => 'bg-sky-100 text-sky-700 border-sky-200',
            'Terjadwal' => 'bg-amber-100 text-amber-700 border-amber-200',
            'Selesai' => 'bg-slate-100 text-slate-600 border-slate-200',
            'Arsip' => 'bg-slate-100 text-slate-600 border-slate-200',
            default => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    };

    $resolveImageUrl = function ($path): ?string {
        if (blank($path) || ! is_string($path)) {
            return null;
        }

        $path = trim($path);

        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (\Illuminate\Support\Str::startsWith($path, ['/storage/', 'storage/', '/images/', 'images/'])) {
            return asset(ltrim($path, '/'));
        }

        if (\Illuminate\Support\Str::startsWith($path, '/')) {
            return asset(ltrim($path, '/'));
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
    };

    $programPoster = $program->image_url ?: $resolveImageUrl($program->image ?? null);
    $programImage = $programPoster;

    $learningItems = collect($program->learning_points ?? [])
        ->map(function ($item) {
            if (is_array($item)) {
                return $item['point'] ?? $item['text'] ?? null;
            }

            return $item;
        })
        ->filter()
        ->values()
        ->all();

    if (empty($learningItems)) {
        $learningItems = [
            'Memahami isu hukum dan kebijakan publik secara kontekstual.',
            'Membaca perkembangan hukum dengan pendekatan yang lebih kritis.',
            'Menghubungkan teori hukum dengan persoalan sosial yang aktual.',
            'Membangun literasi hukum yang aplikatif dan mudah diakses.',
        ];
    }

    $description = $program->short_description ?: $program->description;

    if (blank($description) && ! empty($learningItems)) {
        $description = implode(' ', array_slice($learningItems, 0, 2));
    }

    if (blank($description)) {
        $description = 'Program ini dirancang sebagai ruang pembelajaran dan diskusi hukum yang mempertemukan isu konstitusi, kebijakan publik, dan kebutuhan literasi masyarakat. Informasi lengkap program akan diperbarui secara berkala melalui kanal resmi Edulaw Project.';
    }

    $descriptionIsHtml = \Illuminate\Support\Str::contains((string) $description, ['<p', '<br', '<ul', '<ol', '<div']);

    $speakers = collect($program->speakers ?? [])
        ->map(function ($speaker) use ($resolveImageUrl) {
            if (is_string($speaker)) {
                return [
                    'name' => $speaker,
                    'title' => null,
                    'image' => null,
                ];
            }

            if (! is_array($speaker)) {
                return null;
            }

            $image = $speaker['image_url']
                ?? $speaker['image']
                ?? $speaker['photo']
                ?? $speaker['avatar']
                ?? null;

            return [
                'name' => $speaker['name'] ?? null,
                'title' => $speaker['title'] ?? $speaker['role'] ?? $speaker['position'] ?? null,
                'image' => $resolveImageUrl($image),
            ];
        })
        ->filter(fn ($speaker) => is_array($speaker) && filled($speaker['name']))
        ->values()
        ->all();

    if (empty($speakers)) {
        $speakers = [
            ['name' => 'Tim Edulaw Project', 'title' => 'Fasilitator Program', 'image' => null],
            ['name' => 'Narasumber Akademisi / Praktisi', 'title' => 'Pemantik Diskusi', 'image' => null],
        ];
    }

    $archiveItems = [
        [
            'title' => 'Materi kegiatan',
            'desc' => 'Akan tersedia setelah kegiatan selesai dan materi final siap dibagikan.',
        ],
        [
            'title' => 'Rekaman diskusi',
            'desc' => 'Dokumentasi rekaman akan ditambahkan jika sesi dipublikasikan ulang.',
        ],
        [
            'title' => 'Galeri kegiatan',
            'desc' => 'Foto dan catatan kegiatan akan muncul setelah arsip program dikurasi.',
        ],
    ];

    $summaryCards = [
        ['label' => 'Durasi', 'value' => $durationLabel, 'icon' => 'clock'],
        ['label' => 'Format', 'value' => $formatLabel, 'icon' => 'layout'],
        ['label' => 'Level', 'value' => $levelLabel, 'icon' => 'level'],
    ];

    $heroBackground = $programImage ?: 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1800&q=85';

    $relatedPrograms = $relatedPrograms ?? collect();
@endphp

<main class="bg-slate-50/70">
    <x-shared.page-header
        :title="$programTitle"
        :compact="true"
        eyebrow="Program Edulaw"
        :background-image="$heroBackground"
        :background-alt="$programTitle"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Program', 'url' => route('programs.index')],
            ['label' => 'Detail Program'],
        ]"
    >
        <div class="rounded-3xl border border-white/14 bg-white/10 p-4 shadow-2xl shadow-brand-navy/20 backdrop-blur-md sm:p-5">
            <div class="flex flex-wrap gap-2">
                <span class="rounded-full border border-white/18 bg-white/12 px-3.5 py-2 text-[10px] font-black uppercase tracking-[0.16em] text-white shadow-sm backdrop-blur">
                    {{ $statusLabel }}
                </span>
                <span class="rounded-full border border-white/18 bg-white/12 px-3.5 py-2 text-[10px] font-black uppercase tracking-[0.16em] text-white shadow-sm backdrop-blur">
                    {{ $eventDateLabel }}
                </span>
            </div>

            <div class="mt-4 grid gap-3">
                @foreach ($summaryCards as $card)
                    <div class="flex items-center gap-4 rounded-2xl border border-white/12 bg-white/9 p-4 shadow-sm">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/12 text-brand-amber">
                            @if ($card['icon'] === 'clock')
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 7v5l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="2" />
                                </svg>
                            @elseif ($card['icon'] === 'layout')
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M4 5h16v14H4V5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                                    <path d="M4 10h16M9 10v9" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            @else
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="m4 18 6-6 4 4 6-8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M15 8h5v5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            @endif
                        </span>

                        <div class="min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-white/54">
                                {{ $card['label'] }}
                            </p>
                            <p class="mt-1 text-base font-black text-white">
                                {{ $card['value'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1 rounded-2xl border border-white/10 bg-[#06132a]/20 px-4 py-3 text-sm font-semibold text-white/72">
                <span>Bahasa {{ $languageLabel }}</span>
                <span class="hidden h-1 w-1 rounded-full bg-white/36 sm:block"></span>
                <span>{{ $statusLabel === 'Selesai' ? 'Arsip program' : 'Informasi program' }}</span>
            </div>
        </div>
    </x-shared.page-header>

    <div class="mx-auto grid max-w-7xl gap-8 px-6 py-10 lg:grid-cols-[minmax(0,1fr)_360px] lg:px-8 lg:py-14">
        <div class="order-2 space-y-6 lg:order-1">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-brand-teal/10 text-brand-teal">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 17v-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            <path d="M12 8h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                            <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="2" />
                        </svg>
                    </span>

                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.32em] text-brand-teal">
                            Tentang Program
                        </p>
                        <h2 class="mt-3 text-2xl font-black tracking-tight text-brand-navy">
                            Gambaran umum program
                        </h2>
                    </div>
                </div>

                <div class="prose prose-slate mt-6 max-w-none text-slate-600 prose-p:leading-8 prose-p:text-slate-600">
                    @if ($descriptionIsHtml)
                        {!! $description !!}
                    @else
                        <p>{{ $description }}</p>
                    @endif
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <p class="text-xs font-black uppercase tracking-[0.32em] text-brand-teal">
                    Yang Dipelajari
                </p>

                <h2 class="mt-3 text-2xl font-black tracking-tight text-brand-navy">
                    Kompetensi dan pemahaman yang dibangun
                </h2>

                <div class="mt-6 grid gap-3">
                    @foreach ($learningItems as $item)
                        <div class="flex gap-3 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm leading-7 text-slate-600">
                            <span class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-teal text-white">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="m5 10 3 3 7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>

                            <span>{{ $item }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <p class="text-xs font-black uppercase tracking-[0.32em] text-brand-teal">
                    Narasumber
                </p>

                <h2 class="mt-3 text-2xl font-black tracking-tight text-brand-navy">
                    Fasilitator dan pemantik diskusi
                </h2>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    @foreach ($speakers as $speaker)
                        <article class="flex gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl bg-brand-navy text-lg font-black text-white">
                                @if ($speaker['image'])
                                    <img
                                        src="{{ $speaker['image'] }}"
                                        alt="{{ $speaker['name'] }}"
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="flex h-full w-full items-center justify-center">
                                        {{ \Illuminate\Support\Str::of($speaker['name'])->explode(' ')->filter()->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->take(2)->implode('') }}
                                    </div>
                                @endif
                            </div>

                            <div class="min-w-0 pt-1">
                                <p class="text-base font-black leading-snug text-brand-navy">
                                    {{ $speaker['name'] }}
                                </p>
                                <p class="mt-1 text-sm leading-6 text-slate-500">
                                    {{ $speaker['title'] ?: 'Narasumber' }}
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <p class="text-xs font-black uppercase tracking-[0.32em] text-brand-teal">
                    Dokumentasi / Arsip
                </p>

                <h2 class="mt-3 text-2xl font-black tracking-tight text-brand-navy">
                    Arsip kegiatan program
                </h2>

                <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5">
                    <p class="text-sm font-semibold leading-7 text-slate-600">
                        Dokumentasi resmi akan ditampilkan setelah kegiatan selesai dan arsip program sudah dikurasi.
                    </p>

                    <div class="mt-5 grid gap-3 sm:grid-cols-3">
                        @foreach ($archiveItems as $archive)
                            <div class="rounded-xl bg-white p-4 ring-1 ring-slate-200">
                                <h3 class="text-sm font-black text-brand-navy">
                                    {{ $archive['title'] }}
                                </h3>

                                <p class="mt-2 text-xs leading-5 text-slate-500">
                                    {{ $archive['desc'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>

        <aside class="contents lg:order-2 lg:block lg:sticky lg:top-28 lg:self-start lg:space-y-6">
            <section class="order-1 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-brand-teal">
                        Poster Program
                    </p>
                    <h2 class="mt-2 text-lg font-black tracking-tight text-brand-navy">
                        Visual kegiatan
                    </h2>
                </div>

                <div class="p-4">
                    @if ($programPoster)
                        <a href="{{ $programPoster }}" target="_blank" rel="noopener" class="group block overflow-hidden rounded-[1.25rem] bg-slate-100">
                            <img
                                src="{{ $programPoster }}"
                                alt="Poster {{ $programTitle }}"
                                class="aspect-4/5 w-full object-cover transition duration-500 group-hover:scale-105"
                                loading="lazy"
                            >
                        </a>

                        <a
                            href="{{ $programPoster }}"
                            target="_blank"
                            rel="noopener"
                            class="mt-4 inline-flex w-full items-center justify-center rounded-xl border border-brand-navy/20 bg-white px-4 py-3 text-sm font-bold text-brand-navy transition hover:border-brand-navy hover:bg-brand-navy hover:text-white"
                        >
                            Lihat Poster Penuh
                        </a>
                    @else
                        <div class="flex aspect-4/5 items-center justify-center rounded-[1.25rem] bg-linear-to-br from-brand-navy via-brand-navy/90 to-slate-800 p-6 text-center">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-brand-amber">
                                    Edulaw Program
                                </p>
                                <h3 class="mt-3 text-xl font-black leading-tight text-white">
                                    {{ $programTitle }}
                                </h3>
                                <p class="mt-3 text-sm leading-relaxed text-white/70">
                                    Poster program belum tersedia.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <section class="order-3 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="border-b border-slate-100 pb-5">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-brand-teal">
                        Detail Program
                    </p>
                    <h2 class="mt-2 text-lg font-black text-brand-navy">
                        Informasi Program
                    </h2>
                </div>

                <div class="divide-y divide-slate-200 text-sm">
                    <div class="flex justify-between gap-4 py-3.5">
                        <span class="font-bold text-slate-500">Kategori</span>
                        <span class="text-right font-black text-brand-navy">{{ $program->display_category }}</span>
                    </div>

                    <div class="flex justify-between gap-4 py-3.5">
                        <span class="font-bold text-slate-500">Durasi</span>
                        <span class="text-right font-black text-brand-navy">{{ $durationLabel }}</span>
                    </div>

                    <div class="flex justify-between gap-4 py-3.5">
                        <span class="font-bold text-slate-500">Format</span>
                        <span class="text-right font-black text-brand-navy">{{ $formatLabel }}</span>
                    </div>

                    <div class="flex justify-between gap-4 py-3.5">
                        <span class="font-bold text-slate-500">Level</span>
                        <span class="text-right font-black text-brand-navy">{{ $levelLabel }}</span>
                    </div>

                    <div class="flex justify-between gap-4 py-3.5">
                        <span class="font-bold text-slate-500">Status</span>
                        <span class="text-right font-black text-brand-navy">{{ $statusLabel }}</span>
                    </div>

                    <div class="flex justify-between gap-4 py-3.5">
                        <span class="font-bold text-slate-500">Tanggal Kegiatan</span>
                        <span class="text-right font-black text-brand-navy">{{ $eventDateLabel }}</span>
                    </div>

                    <div class="flex justify-between gap-4 py-3.5">
                        <span class="font-bold text-slate-500">Bahasa</span>
                        <span class="text-right font-black text-brand-navy">{{ $languageLabel }}</span>
                    </div>
                </div>

                @if ($program->registration_url)
                    <a
                        href="{{ $program->registration_url }}"
                        target="_blank"
                        rel="noopener"
                        class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-brand-navy px-5 py-3 text-sm font-black text-white transition hover:-translate-y-0.5 hover:bg-brand-ink"
                    >
                        Daftar / Lihat Agenda
                    </a>
                @else
                    <a
                        href="{{ route('programs.index') }}"
                        class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-brand-navy px-5 py-3 text-sm font-black text-white transition hover:-translate-y-0.5 hover:bg-brand-ink"
                    >
                        Daftar / Lihat Agenda
                    </a>
                @endif
            </section>
        </aside>
    </div>

    @if ($relatedPrograms->isNotEmpty())
        <section class="mx-auto max-w-7xl px-6 pb-14 lg:px-8">
            <div class="mb-6">
                <p class="text-xs font-black uppercase tracking-[0.32em] text-brand-teal">
                    Program Terkait
                </p>

                <h2 class="mt-3 text-2xl font-black tracking-tight text-brand-navy">
                    Program lain dalam kategori {{ $program->display_category }}
                </h2>
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                @foreach ($relatedPrograms as $related)
                    <a
                        href="{{ route('programs.show', $related->slug) }}"
                        class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200/70"
                    >
                        <span class="rounded-full border px-3 py-1 text-xs font-black {{ $statusClass($related->display_status) }}">
                            {{ $related->display_status }}
                        </span>

                        <h3 class="mt-4 text-lg font-black leading-snug text-brand-navy group-hover:text-brand-teal">
                            {{ $related->title }}
                        </h3>

                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            {{ $related->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($related->description), 120) }}
                        </p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <x-shared.cta-collaboration
        eyebrow="Kolaborasi Program"
        title="Ingin menghadirkan program serupa bersama Edulaw?"
        body="Kami terbuka untuk kelas, diskusi, pelatihan, dan forum pengembangan kapasitas hukum yang disesuaikan dengan kebutuhan komunitas atau institusi Anda."
        primary-label="Ajukan Kolaborasi"
        :secondary-url="route('programs.index')"
        secondary-label="Lihat Program Lainnya"
    />
</main>
@endsection
