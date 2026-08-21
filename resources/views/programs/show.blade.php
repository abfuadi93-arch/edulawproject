@extends('layouts.app')

@section('title', $program->seo_title ?: $program->display_title)
@section('meta_description', $program->seo_description ?: \Illuminate\Support\Str::limit(strip_tags($program->short_description ?: ($program->description ?: 'Program Edulaw Project.')), 160))
@section('canonical_url', route('programs.show', $program->slug))
@section('og_type', 'article')
@section('og_image', edulaw_file_url($program->og_image ?: ($program->hero_image ?: $program->image), 'images/hero/hero-edulaw.jpg'))
@section('og_image_alt', $program->display_title)

@push('head')
    @if ($eventSchema = \App\Support\StructuredData::event($program))
        <x-structured-data :data="$eventSchema" />
    @endif
    <x-structured-data :data="\App\Support\StructuredData::breadcrumbs([
        ['name' => 'Beranda', 'url' => route('home')],
        ['name' => 'Program', 'url' => route('programs.index')],
        ['name' => $program->display_title, 'url' => route('programs.show', $program->slug)],
    ])" />
@endpush

@section('content')
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $programTitle = $program->display_title ?: $program->name;
    $categoryLabel = $program->display_category ?: 'Program Edulaw';
    $statusLabel = $program->display_status ?: 'Terjadwal';
    $durationLabel = $program->duration ?: null;
    $formatLabel = $program->display_format;
    $levelLabel = $program->display_level ?: $program->level;
    $eventDateLabel = $program->started_at ? $program->started_at->translatedFormat('d F Y') : null;
    $languageLabel = $program->language ?: 'Indonesia';
    $subtitle = $program->subtitle;
    $shortDescription = Str::limit(strip_tags((string) $program->short_description), 240);
    $detailDescription = $program->getRawOriginal('description');
    $descriptionIsHtml = Str::contains((string) $detailDescription, ['<p', '<br', '<ul', '<ol', '<div']);
    $descriptionParagraphs = collect(preg_split('/\R{2,}/', trim((string) $detailDescription)) ?: [])
        ->map(fn ($paragraph) => trim($paragraph))
        ->filter()
        ->values();

    $resolveImageUrl = fn ($path): ?string => is_string($path) ? edulaw_file_url($path) : null;
    $programPoster = $program->image_url ?: edulaw_file_url($program->image ?? null);
    $programImage = $program->hero_image_url ?: $programPoster;
    $heroBackground = $programImage ?: 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1800&q=85';

    $collaborationUrl = Route::has('collaboration.index') ? route('collaboration.index') : url('/kolaborasi');
    $registrationUrl = $program->registration_url ?: null;
    $primaryButtonLabel = $program->primary_button_text ?: ($registrationUrl ? 'Daftar Program' : 'Diskusikan Kolaborasi');
    $primaryButtonUrl = $program->primary_button_url ?: ($registrationUrl ?: $collaborationUrl);
    $secondaryButtonLabel = $program->secondary_button_text ?: 'Diskusikan Kolaborasi';
    $secondaryButtonUrl = $program->secondary_button_url ?: $collaborationUrl;
    $showSecondaryButton = $secondaryButtonUrl !== $primaryButtonUrl || $secondaryButtonLabel !== $primaryButtonLabel;

    $statusClass = function ($status) {
        return match ($status) {
            'Berjalan' => 'edulaw-badge-teal',
            'Segera Dibuka' => 'edulaw-badge-sky',
            'Terjadwal' => 'edulaw-badge-amber',
            'Selesai' => 'edulaw-badge-muted',
            'Portofolio' => 'edulaw-badge-amber',
            'Arsip' => 'edulaw-badge-muted',
            default => 'edulaw-badge-muted',
        };
    };

    $heroAudienceLabel = $levelLabel ?: $program->audience;
    $heroInfoItems = collect([
        ['label' => 'Tanggal', 'value' => $eventDateLabel, 'icon' => 'calendar'],
        ['label' => 'Durasi', 'value' => $durationLabel, 'icon' => 'clock'],
        ['label' => 'Format', 'value' => $formatLabel, 'icon' => 'format'],
        ['label' => $levelLabel ? 'Level' : 'Peserta', 'value' => $heroAudienceLabel, 'icon' => 'users'],
    ])->filter(fn ($item) => filled($item['value']))->values();

    $learningItems = collect($program->learning_points ?? [])
        ->map(fn ($item) => is_array($item) ? ($item['point'] ?? $item['text'] ?? null) : $item)
        ->map(fn ($item) => trim((string) $item))
        ->filter()
        ->values();

    $speakers = collect($program->speakers ?? [])
        ->map(function ($speaker) use ($resolveImageUrl) {
            if (is_string($speaker)) {
                return [
                    'name' => $speaker,
                    'title' => null,
                    'image' => null,
                    'bio' => null,
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
                'bio' => filled($speaker['bio'] ?? null) ? trim(strip_tags((string) $speaker['bio'])) : null,
            ];
        })
        ->filter(fn ($speaker) => is_array($speaker) && filled($speaker['name']))
        ->values();

    $moderator = filled($program->moderator_name)
        ? [
            'name' => $program->moderator_name,
            'title' => $program->moderator_affiliation,
        ]
        : null;

    $programPlanCards = collect([
        ['label' => 'Orientasi', 'body' => $program->orientation, 'icon' => 'compass'],
        ['label' => 'Metode', 'body' => $program->method, 'icon' => 'method'],
        ['label' => 'Output', 'body' => $program->output, 'icon' => 'output'],
    ])->filter(fn ($card) => filled($card['body']))->values();

    $sidebarRows = collect([
        ['label' => 'Kategori', 'value' => $categoryLabel],
        ['label' => 'Tanggal Mulai', 'value' => $eventDateLabel],
        ['label' => 'Format', 'value' => $formatLabel],
        ['label' => 'Lokasi', 'value' => $program->location],
        ['label' => 'Target Peserta', 'value' => $program->audience !== $heroAudienceLabel ? $program->audience : null],
        ['label' => 'Biaya', 'value' => $program->price_type],
        ['label' => 'Sertifikat', 'value' => $program->certificate_available ? 'Tersedia' : null],
        ['label' => 'Bahasa', 'value' => $languageLabel],
    ])->filter(fn ($row) => filled($row['value']))->values();

    $supportLinks = collect([
        ['label' => 'Dokumentasi YouTube', 'url' => $program->youtube_url],
        ['label' => 'Materi Program', 'url' => $program->material_link],
    ])->filter(fn ($link) => filled($link['url']))->values();

    $initials = fn (string $name): string => Str::of($name)
        ->explode(' ')
        ->filter()
        ->map(fn ($part) => Str::substr($part, 0, 1))
        ->take(2)
        ->implode('') ?: 'EP';

    $relatedPrograms = collect($relatedPrograms ?? []);
@endphp

<main class="bg-[#f7f8fa]">
    <section class="relative isolate overflow-hidden bg-brand-navy text-white">
        <img
            src="{{ $heroBackground }}"
            alt="{{ $programTitle }}"
            class="absolute inset-0 z-0 h-full w-full object-cover"
        >
        <div class="absolute inset-0 z-0 bg-linear-to-r from-[#06132a]/96 via-[#06132a]/78 to-[#06132a]/24"></div>
        <div class="absolute inset-0 z-0 bg-linear-to-t from-[#06132a]/78 via-transparent to-[#06132a]/24"></div>

        <div class="relative z-10 mx-auto max-w-7xl px-5 py-11 sm:px-6 lg:px-8 lg:py-16">
            <nav class="flex flex-wrap items-center gap-2 text-xs font-bold text-white/70 sm:text-sm" aria-label="Breadcrumb">
                <a href="{{ url('/') }}" class="transition hover:text-white">Beranda</a>
                <span class="text-white/40">/</span>
                <a href="{{ route('programs.index') }}" class="transition hover:text-white">Program</a>
                <span class="text-white/40">/</span>
                <span class="text-white">Detail Program</span>
            </nav>

            <div class="mt-6 max-w-5xl">
                <p class="edulaw-badge edulaw-badge-md edulaw-badge-dark">
                    {{ $categoryLabel }}
                </p>

                <h1 class="mt-4 max-w-5xl text-4xl font-black leading-[1.04] tracking-tight text-white sm:text-5xl lg:text-[3.6rem]">
                    {{ $programTitle }}
                </h1>

                @if ($subtitle)
                    <p class="mt-4 max-w-3xl text-xl font-black leading-snug text-brand-amber sm:text-2xl">
                        {{ $subtitle }}
                    </p>
                @endif

                @if ($shortDescription)
                    <p class="mt-5 max-w-3xl text-base font-medium leading-8 text-white/82 sm:text-lg">
                        {{ $shortDescription }}
                    </p>
                @endif
            </div>

            @if ($heroInfoItems->isNotEmpty())
                <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($heroInfoItems as $item)
                        <div class="flex items-center gap-3 rounded-2xl border border-white/70 bg-white px-4 py-4 shadow-xl shadow-black/10">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-amber-soft text-brand-navy">
                                @if ($item['icon'] === 'calendar')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M7 3v4M17 3v4M4 9h16M5 5h14v16H5V5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                    </svg>
                                @elseif ($item['icon'] === 'clock')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M12 7v5l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="2" />
                                    </svg>
                                @elseif ($item['icon'] === 'format')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4 5h16v14H4V5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                                        <path d="M4 10h16M9 10v9" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                    </svg>
                                @else
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M16 11a4 4 0 1 0-8 0M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                    </svg>
                                @endif
                            </span>

                            <div class="min-w-0">
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-teal">
                                    {{ $item['label'] }}
                                </p>
                                <p class="mt-1 text-sm font-black leading-snug text-brand-navy">
                                    {{ $item['value'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <div class="mx-auto grid max-w-7xl gap-8 px-5 py-10 sm:px-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:px-8 lg:py-14">
        <div class="space-y-7">
            @if ($detailDescription)
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <p class="text-xs font-black uppercase tracking-[0.26em] text-brand-teal">
                        Tentang Program
                    </p>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-brand-navy sm:text-3xl">
                        Gambaran umum program
                    </h2>

                    <div class="edulaw-readable mt-6 max-w-3xl text-base text-slate-700">
                        @if ($descriptionIsHtml)
                            {!! $detailDescription !!}
                        @else
                            @foreach ($descriptionParagraphs as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        @endif
                    </div>
                </section>
            @endif

            @if ($learningItems->isNotEmpty())
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <p class="text-xs font-black uppercase tracking-[0.26em] text-brand-teal">
                        Yang Dipelajari
                    </p>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-brand-navy">
                        Kompetensi dan pemahaman yang dibangun
                    </h2>

                    <ul class="mt-6 space-y-3">
                        @foreach ($learningItems as $item)
                            <li class="flex gap-3">
                                <span class="mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-brand-teal-soft text-brand-teal">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="m5 10 3 3 7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                                <p class="text-sm font-semibold leading-7 text-slate-600">
                                    {{ $item }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($speakers->isNotEmpty() || $moderator)
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <p class="text-xs font-black uppercase tracking-[0.26em] text-brand-teal">
                        Narasumber
                    </p>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-brand-navy">
                        Fasilitator dan pemantik diskusi
                    </h2>

                    @if ($speakers->isNotEmpty())
                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            @foreach ($speakers as $speaker)
                                <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                    <div class="flex gap-4">
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
                                                    {{ $initials($speaker['name']) }}
                                                </div>
                                            @endif
                                        </div>

                                        <div class="min-w-0 pt-1">
                                            <h3 class="text-base font-black leading-snug text-brand-navy">
                                                {{ $speaker['name'] }}
                                            </h3>
                                            <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                                                {{ $speaker['title'] ?: 'Narasumber' }}
                                            </p>
                                        </div>
                                    </div>

                                    @if ($speaker['bio'])
                                        <p class="mt-4 text-sm leading-7 text-slate-600">
                                            {{ $speaker['bio'] }}
                                        </p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @endif

                    @if ($moderator)
                        <div class="mt-5 flex flex-col gap-2 rounded-2xl border border-brand-teal/15 bg-brand-teal/5 p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-brand-teal">
                                    Moderator
                                </p>
                                <p class="mt-2 text-base font-black text-brand-navy">
                                    {{ $moderator['name'] }}
                                </p>
                            </div>

                            @if ($moderator['title'])
                                <p class="text-sm font-semibold leading-6 text-slate-600 sm:max-w-xs sm:text-right">
                                    {{ $moderator['title'] }}
                                </p>
                            @endif
                        </div>
                    @endif
                </section>
            @endif

            @if ($programPlanCards->isNotEmpty())
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <p class="text-xs font-black uppercase tracking-[0.26em] text-brand-teal">
                        Rancangan Program
                    </p>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-brand-navy">
                        Orientasi, metode, dan output
                    </h2>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        @foreach ($programPlanCards as $card)
                            <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-amber-soft text-brand-navy">
                                        @if ($card['icon'] === 'compass')
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="m15 9-2.5 5.5L7 17l2.5-5.5L15 9Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                                                <path d="M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="2" />
                                            </svg>
                                        @elseif ($card['icon'] === 'method')
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M5 7h14M5 12h10M5 17h7" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M5 12 10 17 20 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        @endif
                                    </span>

                                    <h3 class="min-w-0 text-sm font-black text-brand-navy">
                                        {{ $card['label'] }}
                                    </h3>
                                </div>

                                <p class="mt-4 text-sm leading-7 text-slate-600">
                                    {{ $card['body'] }}
                                </p>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($program->notes)
                <aside class="rounded-2xl border border-brand-amber/25 bg-brand-amber-soft/70 p-5">
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-brand-navy">
                        Catatan
                    </p>
                    <p class="mt-2 text-sm font-semibold leading-7 text-slate-700">
                        {{ $program->notes }}
                    </p>
                </aside>
            @endif
        </div>

        <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-brand-teal">
                        Poster Kegiatan
                    </p>
                    <h2 class="mt-2 text-lg font-black tracking-tight text-brand-navy">
                        Visual program
                    </h2>
                </div>

                <div class="p-4">
                    @if ($programPoster)
                        <a href="{{ $programPoster }}" target="_blank" rel="noopener" class="group block overflow-hidden rounded-2xl bg-slate-100">
                            <img
                                src="{{ $programPoster }}"
                                alt="Poster {{ $programTitle }}"
                                class="aspect-[4/5] w-full object-cover transition duration-500 group-hover:scale-105"
                                loading="lazy"
                            >
                        </a>

                        <a
                            href="{{ $programPoster }}"
                            target="_blank"
                            rel="noopener"
                            class="mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-brand-navy/20 bg-white px-4 py-2.5 text-sm font-black text-brand-navy transition hover:border-brand-navy hover:bg-brand-navy hover:text-white"
                        >
                            Lihat Poster Penuh
                        </a>
                    @else
                        <div class="flex aspect-[4/5] items-center justify-center rounded-2xl bg-linear-to-br from-brand-navy via-[#102f55] to-brand-teal/80 p-6 text-center">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-brand-amber">
                                    Edulaw Program
                                </p>
                                <h3 class="mt-3 text-xl font-black leading-tight text-white">
                                    {{ $programTitle }}
                                </h3>
                                <p class="mt-3 text-sm leading-relaxed text-white/72">
                                    Poster program belum tersedia.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-5">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-brand-teal">
                            Informasi Program
                        </p>
                        <h2 class="mt-2 text-lg font-black text-brand-navy">
                            Detail singkat
                        </h2>
                    </div>

                    <span class="edulaw-badge edulaw-badge-md {{ $statusClass($statusLabel) }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                @if ($sidebarRows->isNotEmpty())
                    <dl class="divide-y divide-slate-100 text-sm">
                        @foreach ($sidebarRows as $row)
                            <div class="flex justify-between gap-4 py-3.5">
                                <dt class="font-bold text-slate-500">{{ $row['label'] }}</dt>
                                <dd class="text-right font-black text-brand-navy">{{ $row['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                <div class="mt-6 grid gap-3">
                    <a
                        href="{{ $primaryButtonUrl }}"
                        @if (Str::startsWith($primaryButtonUrl, ['http://', 'https://'])) target="_blank" rel="noopener" @endif
                        class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-brand-amber px-5 py-3 text-sm font-black text-brand-black shadow-sm transition hover:-translate-y-0.5 hover:bg-[#e7a72d]"
                    >
                        {{ $primaryButtonLabel }}
                    </a>

                    @if ($showSecondaryButton)
                        <a
                            href="{{ $secondaryButtonUrl }}"
                            @if (Str::startsWith($secondaryButtonUrl, ['http://', 'https://'])) target="_blank" rel="noopener" @endif
                            class="inline-flex min-h-12 w-full items-center justify-center rounded-xl border border-brand-navy/20 bg-white px-5 py-3 text-sm font-black text-brand-navy transition hover:border-brand-navy hover:bg-brand-navy hover:text-white"
                        >
                            {{ $secondaryButtonLabel }}
                        </a>
                    @endif
                </div>

                <x-share-buttons
                    :title="$programTitle"
                    :url="route('programs.show', $program->slug)"
                    :description="$shortDescription"
                    label="Bagikan Program"
                    class="mt-6 border-t border-slate-100 pt-5"
                />

                @if ($supportLinks->isNotEmpty())
                    <div class="mt-6 border-t border-slate-100 pt-5">
                        <p class="text-[10px] font-black uppercase tracking-[0.20em] text-slate-400">
                            Tautan Tambahan
                        </p>
                        <div class="mt-3 grid gap-2">
                            @foreach ($supportLinks as $link)
                                <a
                                    href="{{ $link['url'] }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-bold text-brand-navy transition hover:border-brand-teal hover:text-brand-teal"
                                >
                                    <span>{{ $link['label'] }}</span>
                                    <span aria-hidden="true">→</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        </aside>
    </div>

    @if ($relatedPrograms->isNotEmpty())
        <section class="mx-auto max-w-7xl px-5 pb-14 sm:px-6 lg:px-8">
            <div class="mb-6">
                <p class="text-xs font-black uppercase tracking-[0.26em] text-brand-teal">
                    Program Terkait
                </p>
                <h2 class="mt-3 text-2xl font-black tracking-tight text-brand-navy">
                    Program lain dalam kategori {{ $categoryLabel }}
                </h2>
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                @foreach ($relatedPrograms as $related)
                    @php
                        $relatedImage = $related->image_url ?: $related->hero_image_url;
                        $relatedMeta = $related->event_date
                            ? $related->event_date->translatedFormat('d M Y')
                            : ($related->display_format ?: 'Program Edulaw');
                    @endphp

                    <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200/70">
                        @if ($relatedImage)
                            <a href="{{ route('programs.show', $related->slug) }}" class="block aspect-[16/9] overflow-hidden bg-slate-100">
                                <img
                                    src="{{ $relatedImage }}"
                                    alt="{{ $related->display_title }}"
                                    class="h-full w-full object-cover transition duration-500 hover:scale-105"
                                    loading="lazy"
                                >
                            </a>
                        @endif

                        <div class="p-5">
                            <span class="edulaw-badge edulaw-badge-md {{ $statusClass($related->display_status) }}">
                                {{ $related->display_status }}
                            </span>

                            <h3 class="mt-4 line-clamp-2 text-lg font-black leading-snug text-brand-navy">
                                <a href="{{ route('programs.show', $related->slug) }}" class="transition hover:text-brand-teal">
                                    {{ $related->display_title }}
                                </a>
                            </h3>

                            <p class="mt-3 text-sm font-bold text-slate-500">
                                {{ $relatedMeta }}
                            </p>

                            <a href="{{ route('programs.show', $related->slug) }}" class="mt-4 inline-flex text-sm font-black text-brand-navy transition hover:text-brand-teal">
                                Lihat Detail →
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <x-shared.cta-collaboration
        eyebrow="Kolaborasi"
        title="Bangun Literasi Hukum Bersama Edulaw Project"
        body="Edulaw Project terbuka untuk kolaborasi program, diskusi, pelatihan, riset, dan pengembangan kapasitas hukum yang relevan dengan kebutuhan komunitas atau institusi Anda."
        :primary-url="$collaborationUrl"
        primary-label="Ajukan Kolaborasi"
        :secondary-url="$collaborationUrl"
        secondary-label="Diskusikan Kolaborasi"
    />
</main>
@endsection
