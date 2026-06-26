@extends('layouts.app')

@section('title', 'Program - Edulaw Project')

@section('content')
@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $programCollection = $programs ?? collect();

    $programItems = $programCollection instanceof \Illuminate\Pagination\AbstractPaginator
        ? $programCollection->getCollection()
        : collect($programCollection);

    $categoryCollection = $categories ?? $programCategories ?? collect();

    $selectedStatuses = Arr::wrap(request('status', []));
    $selectedCategories = Arr::wrap(request('category', []));
    $selectedFormats = Arr::wrap(request('format', []));
    $selectedLevels = Arr::wrap(request('level', []));

    $search = request('q');
    $sort = request('sort', 'latest');

    $homeUrl = Route::has('home') ? route('home') : url('/');
    $indexUrl = Route::has('programs.index') ? route('programs.index') : url('/program');
    $collaborationUrl = Route::has('collaboration.index') ? route('collaboration.index') : url('/kolaborasi');
    $opportunitiesUrl = Route::has('opportunities.index') ? route('opportunities.index') : url('/opportunities');

    $detailUrl = function ($program) {
        return Route::has('programs.show')
            ? route('programs.show', $program->slug ?? $program)
            : url('/program/' . ($program->slug ?? ''));
    };

    $imageUrl = function ($path) {
        return edulaw_file_url($path);
    };

    $programImage = function ($program) use ($imageUrl) {
        return $imageUrl(
            $program->poster_image
                ?? $program->cover_image
                ?? $program->image
                ?? $program->thumbnail
                ?? null
        );
    };

    $programTitle = function ($program) {
        return $program->title ?? $program->name ?? 'Program Edulaw';
    };

    $programExcerpt = function ($program, $limit = 150) {
        return $program->excerpt
            ?? $program->short_description
            ?? Str::limit(strip_tags($program->description ?? ''), $limit);
    };

    $programDate = function ($program) {
        $date = $program->starts_at
            ?? $program->event_date
            ?? $program->started_at
            ?? $program->published_at
            ?? null;

        if (! $date) {
            return null;
        }

        try {
            return $date instanceof Carbon
                ? $date->translatedFormat('d M Y')
                : Carbon::parse($date)->translatedFormat('d M Y');
        } catch (\Throwable $e) {
            return null;
        }
    };

    $rawDate = function ($program) {
        $date = $program->starts_at
            ?? $program->event_date
            ?? $program->started_at
            ?? $program->published_at
            ?? null;

        if (! $date) {
            return null;
        }

        try {
            return $date instanceof Carbon ? $date : Carbon::parse($date);
        } catch (\Throwable $e) {
            return null;
        }
    };

    $categoryName = function ($program) {
        return $program->category->name
            ?? $program->programCategory->name
            ?? $program->display_category
            ?? 'Program Edulaw';
    };

    $categorySlug = function ($category) {
        return is_string($category)
            ? Str::slug($category)
            : ($category->slug ?? Str::slug($category->name ?? ''));
    };

    $categoryLabel = function ($category) {
        return is_string($category)
            ? Str::headline($category)
            : ($category->name ?? 'Kategori');
    };

    $formatLabel = function ($format) {
        return match ($format) {
            'online' => 'Online',
            'offline' => 'Offline',
            'hybrid' => 'Hybrid',
            default => $format ? Str::headline($format) : 'Fleksibel',
        };
    };

    $levelLabel = function ($level) {
        return match ($level) {
            'beginner', 'dasar' => 'Dasar',
            'intermediate', 'menengah' => 'Menengah',
            'advanced', 'lanjut' => 'Lanjutan',
            default => $level ? Str::headline($level) : 'Umum',
        };
    };

    $statusLabel = function ($status) {
        return match ($status) {
            'upcoming' => 'Akan Datang',
            'ongoing' => 'Program Aktif',
            'archived' => 'Arsip',
            default => $status ? Str::headline($status) : 'Arsip',
        };
    };

    $statusBadgeClass = function ($status) {
        return match ($status) {
            'upcoming' => 'bg-brand-amber/15 text-brand-navy border-brand-amber/30',
            'ongoing' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            'archived' => 'bg-slate-100 text-slate-600 border-slate-200',
            default => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    };

    $featuredProgram = $featuredProgram
        ?? $programItems->firstWhere('is_featured', true)
        ?? $programItems->firstWhere('featured', true)
        ?? $programItems->firstWhere('status', 'upcoming')
        ?? $programItems->firstWhere('status', 'ongoing')
        ?? $programItems->first();

    $activePrograms = $programItems
        ->filter(fn ($program) => in_array($program->status, ['upcoming', 'ongoing'], true))
        ->values();

    $portfolioPrograms = $programItems
        ->filter(fn ($program) => ! $featuredProgram || $program->id !== $featuredProgram->id)
        ->values();

    if ($portfolioPrograms->isEmpty()) {
        $portfolioPrograms = $programItems;
    }

    $availableCategories = collect($categoryCollection)->isNotEmpty()
        ? collect($categoryCollection)
        : $programItems
            ->map(fn ($program) => $categoryName($program))
            ->filter()
            ->unique()
            ->values();

    $availableFormats = collect(['online' => 'Online', 'offline' => 'Offline', 'hybrid' => 'Hybrid']);

    $availableLevels = collect([
        'beginner' => 'Dasar',
        'intermediate' => 'Menengah',
        'advanced' => 'Lanjutan',
    ]);

    $audiences = collect([
        'Mahasiswa',
        'Akademisi',
        'Peneliti',
        'Praktisi Hukum',
        'Komunitas',
        'Masyarakat Umum',
    ]);
@endphp

<main class="bg-[#f6f8fb] text-brand-ink">
    <x-shared.page-header
        title="Program"
        :compact="true"
        eyebrow="Katalog Program"
        description="Temukan kelas, diskusi, workshop, pelatihan, webinar, dan program pengembangan kapasitas bersama para ahli dan praktisi di bidang hukum dan kebijakan publik."
        background-image="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1800&q=85"
        background-alt="Program pembelajaran dan diskusi hukum Edulaw"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => $homeUrl],
            ['label' => 'Program'],
        ]"
    />

    {{-- FEATURED PROGRAM --}}
    @if ($featuredProgram)
        @php
            $featuredImage = $programImage($featuredProgram);
        @endphp

        <section class="py-8 lg:py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <article class="overflow-hidden rounded-4xl border border-slate-200 bg-white p-5 shadow-lg shadow-slate-900/6 sm:p-6">
                    <p class="mb-4 inline-flex items-center gap-2 text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                        <span class="text-brand-amber">★</span>
                        Program Unggulan
                    </p>

                    <div class="grid gap-7 lg:grid-cols-[1fr_1.25fr] lg:items-center">
                        <a
                            href="{{ $detailUrl($featuredProgram) }}"
                            class="group relative min-h-70 overflow-hidden rounded-3xl bg-linear-to-br from-brand-navy via-[#123d68] to-teal-500"
                        >
                            @if ($featuredImage)
                                <img
                                    src="{{ $featuredImage }}"
                                    alt="{{ $programTitle($featuredProgram) }}"
                                    class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                >
                                <div class="absolute inset-0 bg-linear-to-t from-brand-navy/75 via-brand-navy/15 to-transparent"></div>
                            @else
                                <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_right,rgba(20,184,166,0.8),transparent_38%)]"></div>
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-white">
                                    <span class="h-16 w-16 rounded-2xl bg-brand-amber shadow-xl shadow-black/10"></span>
                                    <p class="mt-8 text-xs font-black uppercase tracking-[0.45em] text-white/80">
                                        Edulaw Program
                                    </p>
                                </div>
                            @endif
                        </a>

                        <div class="py-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full border border-brand-amber/30 bg-brand-amber/15 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.14em] text-brand-navy">
                                    ★ Program Unggulan
                                </span>

                                <span class="inline-flex rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.14em] text-blue-700">
                                    {{ $categoryName($featuredProgram) }}
                                </span>

                                <span class="inline-flex rounded-full border px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.14em] {{ $statusBadgeClass($featuredProgram->status ?? null) }}">
                                    {{ $statusLabel($featuredProgram->status ?? null) }}
                                </span>
                            </div>

                            <h2 class="mt-5 max-w-3xl text-3xl font-black leading-tight tracking-tight text-brand-ink sm:text-4xl">
                                <a href="{{ $detailUrl($featuredProgram) }}" class="transition hover:text-brand-navy">
                                    {{ $programTitle($featuredProgram) }}
                                </a>
                            </h2>

                            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                                {{ $programExcerpt($featuredProgram, 190) }}
                            </p>

                            <div class="mt-6 flex flex-wrap gap-4 text-xs font-bold text-slate-600">
                                @if ($programDate($featuredProgram))
                                    <span class="inline-flex items-center gap-2">
                                        <svg class="h-4 w-4 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M8 2v4M16 2v4M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        {{ $programDate($featuredProgram) }}
                                    </span>
                                @endif

                                <span class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                    {{ $formatLabel($featuredProgram->format ?? null) }}
                                </span>

                                <span class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M12 3v18M5 8h14M7 8l-3 6h6L7 8Zm10 0-3 6h6l-3-6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    {{ $levelLabel($featuredProgram->level ?? null) }}
                                </span>

                                <span class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4 19h16M7 19V9l5-4 5 4v10M10 19v-5h4v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Edulaw Project
                                </span>
                            </div>

                            <div class="mt-7">
                                <a
                                    href="{{ $detailUrl($featuredProgram) }}"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-navy px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-brand-ink"
                                >
                                    Lihat Detail Program
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    @endif

    {{-- MAIN CONTENT --}}
    <section class="pb-14 lg:pb-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[250px_1fr_230px]">
                {{-- LEFT FILTER --}}
                <aside class="h-fit rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-900/5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <h2 class="text-sm font-black uppercase tracking-[0.14em] text-brand-navy">
                            Filter Program
                        </h2>

                        <a href="{{ $indexUrl }}" class="text-xs font-bold text-slate-400 transition hover:text-brand-navy">
                            Reset
                        </a>
                    </div>

                    <form method="GET" action="{{ $indexUrl }}" class="mt-5 space-y-6">
                        @if ($search)
                            <input type="hidden" name="q" value="{{ $search }}">
                        @endif

                        <div>
                            <p class="text-xs font-black text-brand-ink">Status Program</p>

                            <div class="mt-3 space-y-3">
                                @foreach (['upcoming' => 'Akan Datang', 'ongoing' => 'Program Aktif', 'archived' => 'Arsip / Portofolio'] as $value => $label)
                                    <label class="flex cursor-pointer items-center gap-3 text-sm font-semibold text-slate-600">
                                        <input
                                            type="checkbox"
                                            name="status[]"
                                            value="{{ $value }}"
                                            @checked(in_array($value, $selectedStatuses, true))
                                            class="h-4 w-4 rounded border-slate-300 text-brand-amber focus:ring-brand-amber"
                                        >
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-5">
                            <p class="text-xs font-black text-brand-ink">Kategori</p>

                            <div class="mt-3 max-h-60 space-y-3 overflow-y-auto pr-1">
                                @foreach ($availableCategories as $category)
                                    @php
                                        $slug = $categorySlug($category);
                                    @endphp

                                    <label class="flex cursor-pointer items-center gap-3 text-sm font-semibold text-slate-600">
                                        <input
                                            type="checkbox"
                                            name="category[]"
                                            value="{{ $slug }}"
                                            @checked(in_array($slug, $selectedCategories, true))
                                            class="h-4 w-4 rounded border-slate-300 text-brand-amber focus:ring-brand-amber"
                                        >
                                        <span>{{ $categoryLabel($category) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-5">
                            <p class="text-xs font-black text-brand-ink">Format</p>

                            <div class="mt-3 space-y-3">
                                @foreach ($availableFormats as $value => $label)
                                    <label class="flex cursor-pointer items-center gap-3 text-sm font-semibold text-slate-600">
                                        <input
                                            type="checkbox"
                                            name="format[]"
                                            value="{{ $value }}"
                                            @checked(in_array($value, $selectedFormats, true))
                                            class="h-4 w-4 rounded border-slate-300 text-brand-amber focus:ring-brand-amber"
                                        >
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-5">
                            <p class="text-xs font-black text-brand-ink">Level</p>

                            <div class="mt-3 space-y-3">
                                @foreach ($availableLevels as $value => $label)
                                    <label class="flex cursor-pointer items-center gap-3 text-sm font-semibold text-slate-600">
                                        <input
                                            type="checkbox"
                                            name="level[]"
                                            value="{{ $value }}"
                                            @checked(in_array($value, $selectedLevels, true))
                                            class="h-4 w-4 rounded border-slate-300 text-brand-amber focus:ring-brand-amber"
                                        >
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid gap-3 border-t border-slate-100 pt-5">
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-brand-navy px-5 py-3 text-sm font-black text-white transition hover:bg-brand-ink"
                            >
                                Terapkan Filter
                            </button>

                            <a
                                href="{{ $indexUrl }}"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-5 py-3 text-sm font-black text-brand-ink transition hover:border-brand-navy hover:text-brand-navy"
                            >
                                Reset Filter
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M4 4v6h6M20 20v-6h-6M5 15a7 7 0 0 0 12 3M19 9A7 7 0 0 0 7 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </form>
                </aside>

                {{-- CENTER CONTENT --}}
                <div>
                    {{-- ACTIVE PROGRAM --}}
                    <section>
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                                    Program Aktif
                                </p>

                                <h2 class="mt-2 text-2xl font-black text-brand-ink">
                                    Program Aktif ({{ $activePrograms->count() }})
                                </h2>
                            </div>
                        </div>

@if ($activePrograms->isEmpty())
    <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 p-5 text-sm font-semibold text-brand-navy">
        Saat ini belum ada program aktif.
    </div>
@else
    <div class="mt-4 grid gap-5 md:grid-cols-2">
        @foreach ($activePrograms->take(2) as $program)
            @php
                $image = $programImage($program);
            @endphp

            <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                <a href="{{ $detailUrl($program) }}" class="block">
                    <div class="relative h-48 overflow-hidden bg-linear-to-br from-brand-navy via-[#123d68] to-teal-500">
                        @if ($image)
                            <img
                                src="{{ $image }}"
                                alt="{{ $programTitle($program) }}"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                            >
                            <div class="absolute inset-0 bg-linear-to-t from-brand-navy/75 via-brand-navy/10 to-transparent"></div>
                        @else
                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_right,rgba(20,184,166,0.8),transparent_38%)]"></div>
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-white">
                                <span class="h-12 w-12 rounded-2xl bg-brand-amber shadow-xl shadow-black/10"></span>
                                <p class="mt-6 text-[10px] font-black uppercase tracking-[0.42em] text-white/80">
                                    Edulaw Program
                                </p>
                            </div>
                        @endif

                        <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                            <span class="rounded-full border border-white/20 bg-white/90 px-3 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-brand-ink">
                                {{ $categoryName($program) }}
                            </span>

                            <span class="rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[0.12em] {{ $statusBadgeClass($program->status ?? null) }}">
                                {{ $statusLabel($program->status ?? null) }}
                            </span>
                        </div>
                    </div>

                    <div class="p-5">
                        <h3 class="line-clamp-3 text-lg font-black leading-snug text-brand-ink transition group-hover:text-brand-navy">
                            {{ $programTitle($program) }}
                        </h3>

                        <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">
                            {{ $programExcerpt($program, 145) }}
                        </p>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-600">
                                {{ $formatLabel($program->format ?? null) }}
                            </span>

                            <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-600">
                                {{ $levelLabel($program->level ?? null) }}
                            </span>

                            @if ($programDate($program))
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-600">
                                    {{ $programDate($program) }}
                                </span>
                            @endif
                        </div>

                        <div class="mt-6 inline-flex items-center gap-2 rounded-xl border border-brand-navy px-4 py-2 text-sm font-black text-brand-navy transition group-hover:bg-brand-navy group-hover:text-white">
                            Lihat Detail
                            <svg class="h-4 w-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                </a>
            </article>
        @endforeach
    </div>
@endif
                    </section>

                    {{-- PORTFOLIO --}}
                    <section class="mt-8">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                            Portofolio Program
                        </p>

                        <h2 class="mt-2 text-2xl font-black text-brand-ink">
                            Portofolio Program ({{ $portfolioPrograms->count() }})
                        </h2>

                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            @forelse ($portfolioPrograms as $program)
                                @php
                                    $image = $programImage($program);
                                @endphp

                                <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                                    <a href="{{ $detailUrl($program) }}" class="block">
                                        <div class="relative h-48 overflow-hidden bg-linear-to-br from-brand-navy via-[#123d68] to-teal-500">
                                            @if ($image)
                                                <img
                                                    src="{{ $image }}"
                                                    alt="{{ $programTitle($program) }}"
                                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                                >
                                                <div class="absolute inset-0 bg-linear-to-t from-brand-navy/75 via-brand-navy/10 to-transparent"></div>
                                            @else
                                                <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_right,rgba(20,184,166,0.8),transparent_38%)]"></div>
                                                <div class="absolute inset-0 flex flex-col items-center justify-center text-white">
                                                    <span class="h-12 w-12 rounded-2xl bg-brand-amber shadow-xl shadow-black/10"></span>
                                                    <p class="mt-6 text-[10px] font-black uppercase tracking-[0.42em] text-white/80">
                                                        Edulaw Program
                                                    </p>
                                                </div>
                                            @endif

                                            <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                                                <span class="rounded-full border border-white/20 bg-white/90 px-3 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-brand-ink">
                                                    {{ $categoryName($program) }}
                                                </span>

                                                <span class="rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[0.12em] {{ $statusBadgeClass($program->status ?? null) }}">
                                                    {{ $statusLabel($program->status ?? null) }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="p-5">
                                            <h3 class="line-clamp-3 text-lg font-black leading-snug text-brand-ink transition group-hover:text-brand-navy">
                                                {{ $programTitle($program) }}
                                            </h3>

                                            <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">
                                                {{ $programExcerpt($program, 145) }}
                                            </p>

                                            <div class="mt-5 flex flex-wrap gap-2">
                                                <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-600">
                                                    {{ $formatLabel($program->format ?? null) }}
                                                </span>

                                                <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-600">
                                                    {{ $levelLabel($program->level ?? null) }}
                                                </span>

                                                @if ($programDate($program))
                                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-600">
                                                        {{ $programDate($program) }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="mt-6 inline-flex items-center gap-2 rounded-xl border border-brand-navy px-4 py-2 text-sm font-black text-brand-navy transition group-hover:bg-brand-navy group-hover:text-white">
                                                Lihat Detail
                                                <svg class="h-4 w-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            @empty
                                <div class="md:col-span-2 rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center">
                                    <h3 class="text-xl font-black text-brand-ink">
                                        Belum ada program yang dipublikasikan
                                    </h3>

                                    <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-600">
                                        Tambahkan program melalui panel admin dan gunakan status Upcoming, Ongoing, atau Archived agar tampil di website.
                                    </p>
                                </div>
                            @endforelse
                        </div>

                        @if ($programCollection instanceof \Illuminate\Pagination\AbstractPaginator && $programCollection->hasPages())
                            <div class="mt-10">
                                {{ $programCollection->withQueryString()->links() }}
                            </div>
                        @endif
                    </section>
                </div>

                {{-- RIGHT SIDEBAR --}}
                <aside class="space-y-5">
                    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-900/5">
                        <h3 class="flex items-center gap-2 text-lg font-black text-brand-ink">
                            <svg class="h-5 w-5 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M16 11a4 4 0 1 0-8 0M4 21a8 8 0 0 1 16 0M19 8v6M16 11h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Untuk Siapa?
                        </h3>

                        <div class="mt-5 flex flex-wrap gap-2">
                            @foreach ($audiences as $audience)
                                <span class="rounded-full bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600">
                                    {{ $audience }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-900/5">
                        <h3 class="flex items-center gap-2 text-lg font-black text-brand-ink">
                            <svg class="h-5 w-5 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 4h14v16H5V4ZM8 8h8M8 12h8M8 16h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Temukan Program
                        </h3>

                        <div class="mt-5 space-y-4 text-sm leading-6 text-slate-600">
                            <div>
                                <p class="font-black text-brand-ink">Program Aktif</p>
                                <p class="mt-1">Program yang sedang berjalan atau akan datang.</p>
                            </div>

                            <div>
                                <p class="font-black text-brand-ink">Portofolio Program</p>
                                <p class="mt-1">Arsip kelas, diskusi, webinar, dan dokumentasi kegiatan.</p>
                            </div>

                            <div>
                                <p class="font-black text-brand-ink">Kelas Dasar</p>
                                <p class="mt-1">Materi pengantar dan pemahaman dasar hukum.</p>
                            </div>

                            <div>
                                <p class="font-black text-brand-ink">Program Lanjutan</p>
                                <p class="mt-1">Pembahasan mendalam untuk penguatan kapasitas.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-brand-amber/30 bg-brand-amber/10 p-5 shadow-sm shadow-slate-900/5">
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-brand-navy">
                            Punya Ide Program?
                        </p>

                        <h3 class="mt-2 text-lg font-black leading-snug text-brand-ink">
                            Ajukan kolaborasi program bersama Edulaw.
                        </h3>

                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            Jangkau lebih banyak pembelajar melalui kelas, diskusi, pelatihan, atau program riset.
                        </p>

                        <a
                            href="{{ $collaborationUrl }}"
                            class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-navy px-5 py-3 text-sm font-black text-white transition hover:bg-brand-ink"
                        >
                            Ajukan Program
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <x-shared.cta-section
        eyebrow="Kolaborasi Program"
        title="Bangun program literasi hukum bersama Edulaw Project."
        body="Kami membuka ruang kerja sama untuk kelas, diskusi publik, pelatihan, riset, dan pengembangan kapasitas hukum."
        :primary-url="$collaborationUrl"
        primary-label="Ajukan Kerja Sama"
        :secondary-url="$opportunitiesUrl"
        secondary-label="Lihat Opportunities"
        background-image="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1800&q=85"
        background-alt="Kolaborasi program literasi hukum"
    />
</main>
@endsection
