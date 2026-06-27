@extends('layouts.app')

@section('title', 'Opportunities - Edulaw Project')

@section('content')
@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $opportunityCollection = $opportunities ?? collect();

    $opportunityItems = $opportunityCollection instanceof \Illuminate\Pagination\AbstractPaginator
        ? $opportunityCollection->getCollection()
        : collect($opportunityCollection);

    $typeCollection = $opportunityTypes ?? $types ?? collect();

    $search = request('q');
    $sort = request('sort', 'deadline');

    $selectedStatuses = Arr::wrap(request('status', []));
    $selectedTypes = Arr::wrap(request('type', []));
    $selectedFormats = Arr::wrap(request('format', []));
    $selectedDeadline = request('deadline');

    $typeLabels = [
        'scholarship' => 'Beasiswa',
        'internship' => 'Magang',
        'volunteer' => 'Volunteer',
        'fellowship' => 'Fellowship',
        'call_for_paper' => 'Call for Papers',
        'competition' => 'Kompetisi',
        'open_collaboration' => 'Kolaborasi Terbuka',
    ];

    $statusLabels = [
        'open' => 'Masih Dibuka',
        'closed' => 'Ditutup',
        'archived' => 'Diarsipkan',
    ];

    $formatLabels = [
        'online' => 'Online',
        'offline' => 'Offline',
        'hybrid' => 'Hybrid',
    ];

    $categoryStyles = [
        'scholarship' => [
            'poster' => 'from-[#211b17] via-[#4f3822] to-[#b77a16]',
            'badge' => 'edulaw-badge-amber',
            'button' => 'border-brand-amber/60 text-brand-navy hover:bg-brand-amber hover:text-brand-ink',
            'dot' => 'bg-brand-amber',
        ],
        'internship' => [
            'poster' => 'from-brand-navy via-[#123d68] to-[#28659d]',
            'badge' => 'edulaw-badge-sky',
            'button' => 'border-brand-sky/40 text-brand-navy hover:bg-brand-sky hover:text-white',
            'dot' => 'bg-brand-sky',
        ],
        'volunteer' => [
            'poster' => 'from-emerald-950 via-emerald-800 to-emerald-500',
            'badge' => 'edulaw-badge-teal',
            'button' => 'border-brand-teal/40 text-brand-navy hover:bg-brand-teal hover:text-white',
            'dot' => 'bg-brand-teal',
        ],
        'fellowship' => [
            'poster' => 'from-indigo-950 via-indigo-800 to-indigo-500',
            'badge' => 'edulaw-badge-navy',
            'button' => 'border-brand-navy/30 text-brand-navy hover:bg-brand-navy hover:text-white',
            'dot' => 'bg-brand-navy',
        ],
        'call_for_paper' => [
            'poster' => 'from-teal-950 via-teal-800 to-teal-500',
            'badge' => 'edulaw-badge-teal',
            'button' => 'border-brand-teal/40 text-brand-navy hover:bg-brand-teal hover:text-white',
            'dot' => 'bg-brand-teal',
        ],
        'competition' => [
            'poster' => 'from-orange-950 via-orange-700 to-orange-400',
            'badge' => 'edulaw-badge-amber',
            'button' => 'border-brand-amber/60 text-brand-navy hover:bg-brand-amber hover:text-brand-ink',
            'dot' => 'bg-brand-amber',
        ],
        'open_collaboration' => [
            'poster' => 'from-rose-950 via-rose-700 to-coral-500',
            'badge' => 'edulaw-badge-coral',
            'button' => 'border-brand-coral/40 text-brand-ink hover:bg-brand-coral hover:text-white',
            'dot' => 'bg-brand-coral',
        ],
    ];

    $indexUrl = route('opportunities.index');

    $contactUrl = url('/kontak');

    $posterUrl = function ($path) {
        return edulaw_file_url($path);
    };

    $externalUrl = function ($opportunity) {
        return $opportunity->application_link
            ?? $opportunity->external_url
            ?? $opportunity->url
            ?? null;
    };

    $opportunityUrl = function ($opportunity) use ($externalUrl, $contactUrl) {
        return $externalUrl($opportunity) ?: $contactUrl;
    };

    $isExternalOpportunity = function ($opportunity) use ($externalUrl) {
        $url = $externalUrl($opportunity);

        return $url && Str::startsWith($url, ['http://', 'https://']);
    };

    $buttonLabel = function ($opportunity) use ($externalUrl) {
        return $externalUrl($opportunity) ? 'Buka Peluang' : 'Tanya Informasi';
    };

    $opportunityTypeName = function ($opportunity) use ($typeLabels) {
        $type = $opportunity?->type ?? 'open_collaboration';

        return $typeLabels[$type] ?? Str::headline(str_replace('_', ' ', $type));
    };

    $opportunityStatusName = function ($opportunity) use ($statusLabels) {
        $status = $opportunity?->status ?? 'open';

        return $statusLabels[$status] ?? Str::headline(str_replace('_', ' ', $status));
    };

    $formatName = function ($format) use ($formatLabels) {
        if (! $format) {
            return 'Fleksibel';
        }

        return $formatLabels[$format] ?? Str::headline(str_replace('_', ' ', $format));
    };

    $deadlineText = function ($date) {
        if (! $date) {
            return 'Tidak dibatasi';
        }

        try {
            return $date instanceof Carbon
                ? $date->translatedFormat('d F Y')
                : Carbon::parse($date)->translatedFormat('d F Y');
        } catch (\Throwable $e) {
            return $date;
        }
    };

    $deadlineCarbon = function ($date) {
        if (! $date) {
            return null;
        }

        try {
            return $date instanceof Carbon ? $date : Carbon::parse($date);
        } catch (\Throwable $e) {
            return null;
        }
    };

    $isDeadlineSoon = function ($date) use ($deadlineCarbon) {
        $deadline = $deadlineCarbon($date);

        if (! $deadline) {
            return false;
        }

        $days = now()->diffInDays($deadline, false);

        return $days >= 0 && $days <= 14;
    };

    $summaryText = function ($opportunity, $limit = 155) {
        return $opportunity->excerpt
            ?: Str::limit(strip_tags($opportunity->description ?? ''), $limit);
    };

    $typeSlug = function ($type) {
        return is_string($type)
            ? $type
            : ($type->slug ?? ($type->type ?? Str::slug($type->name ?? '')));
    };

    $typeName = function ($type) use ($typeLabels) {
        if (is_string($type)) {
            return $typeLabels[$type] ?? Str::headline(str_replace('_', ' ', $type));
        }

        $slug = $type->slug ?? $type->type ?? '';

        return $type->name ?? $typeLabels[$slug] ?? Str::headline(str_replace('_', ' ', $slug));
    };

    $availableTypes = collect($typeCollection)->isNotEmpty()
        ? collect($typeCollection)
        : collect(array_keys($typeLabels));

    $featured = $featuredOpportunity
        ?? $opportunityItems->firstWhere('featured', true)
        ?? $opportunityItems->firstWhere('status', 'open')
        ?? $opportunityItems->first();

@endphp

<main class="bg-[#f6f8fb] text-brand-ink">
    <x-shared.page-header
        title="Opportunities"
        :compact="true"
        eyebrow="Kanal Pengembangan"
        description="Temukan beasiswa, magang, fellowship, call for papers, kompetisi, dan peluang kolaborasi di bidang hukum. Bersama membuka akses dan memperluas dampak literasi hukum."
        background-image="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1800&q=85"
        background-alt="Kolaborasi dan pengembangan kapasitas hukum"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Opportunities'],
        ]"
    />

    {{-- FEATURED --}}
    @if ($featured)
        @php
            $featuredType = $featured->type ?? 'open_collaboration';
            $featuredStyle = $categoryStyles[$featuredType] ?? $categoryStyles['open_collaboration'];
            $featuredPoster = $posterUrl($featured->poster ?? $featured->cover_image ?? null);
        @endphp

        <section class="py-10 lg:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5">
                    <div class="grid gap-0 lg:grid-cols-[1fr_300px]">
                        <div class="grid gap-6 p-5 sm:p-7 lg:grid-cols-[220px_1fr] lg:p-7">
                            <div class="group relative min-h-56 overflow-hidden rounded-3xl bg-linear-to-br {{ $featuredStyle['poster'] }} p-5 text-white">
                                @if ($featuredPoster)
                                    <img
                                        src="{{ $featuredPoster }}"
                                        alt="{{ $featured->title }}"
                                        class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                    >
                                    <div class="absolute inset-0 bg-linear-to-t from-brand-navy/85 via-brand-navy/30 to-transparent"></div>
                                @else
                                    <div class="absolute inset-0 opacity-20">
                                        <div class="absolute -bottom-10 -right-8 h-40 w-40 rounded-full border border-white/40"></div>
                                        <div class="absolute bottom-8 left-8 h-12 w-12 rounded-2xl border border-white/20"></div>
                                        <div class="absolute bottom-8 left-24 h-12 w-12 rounded-2xl border border-white/20"></div>
                                        <div class="absolute bottom-8 left-40 h-12 w-12 rounded-2xl border border-white/20"></div>
                                    </div>
                                @endif

                                <div class="relative flex h-full flex-col justify-between">
                                    <span class="edulaw-badge edulaw-badge-on-image">
                                        {{ $opportunityTypeName($featured) }}
                                    </span>

                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-white/70">
                                            Featured
                                        </p>

                                        <h2 class="mt-2 text-2xl font-black leading-tight text-brand-amber">
                                            {{ $featured->title }}
                                        </h2>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col justify-center">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="edulaw-badge edulaw-badge-md {{ $featuredStyle['badge'] }}">
                                        {{ $opportunityTypeName($featured) }}
                                    </span>

                                    <span class="edulaw-badge edulaw-badge-md edulaw-badge-sky">
                                        {{ $opportunityStatusName($featured) }}
                                    </span>
                                </div>

                                <h2 class="mt-5 max-w-2xl text-2xl font-black leading-tight tracking-tight text-brand-ink sm:text-3xl">
                                    {{ $featured->title }}
                                </h2>

                                <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600">
                                    {{ $summaryText($featured, 180) }}
                                </p>

                                <div class="mt-7 grid gap-3 sm:grid-cols-3">
                                    <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-brand-navy shadow-sm">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M7 11h10M7 15h6M5 4h14v16H5V4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <span class="text-xs font-bold leading-5 text-slate-600">
                                            Dukungan<br>Pendanaan
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-brand-navy shadow-sm">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M4 19h16M7 19V9l5-4 5 4v10M10 19v-5h4v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <span class="text-xs font-bold leading-5 text-slate-600">
                                            Bidang Hukum &<br>Kebijakan Publik
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-brand-navy shadow-sm">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M12 3v18M5 8h14M7 8l-3 6h6L7 8Zm10 0-3 6h6l-3-6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <span class="text-xs font-bold leading-5 text-slate-600">
                                            Penguatan<br>Literasi Hukum
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <aside class="border-t border-slate-200 bg-slate-50 p-6 lg:border-l lg:border-t-0">
                            <div class="grid gap-4">
                                <div class="flex gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-brand-navy shadow-sm">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M8 2v4M16 2v4M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-400">
                                            Deadline
                                        </p>
                                        <p class="mt-1 text-sm font-black text-brand-ink">
                                            {{ $deadlineText($featured->deadline ?? null) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-brand-navy shadow-sm">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-400">
                                            Format
                                        </p>
                                        <p class="mt-1 text-sm font-black text-brand-ink">
                                            {{ $formatName($featured->format ?? null) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-brand-navy shadow-sm">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M12 21s7-4.4 7-11a7 7 0 1 0-14 0c0 6.6 7 11 7 11Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                            <path d="M12 10.5h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-400">
                                            Lokasi
                                        </p>
                                        <p class="mt-1 text-sm font-black text-brand-ink">
                                            {{ $featured->location ?: 'Menyesuaikan' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-brand-navy shadow-sm">
                                        <span class="h-2.5 w-2.5 rounded-full {{ $featuredStyle['dot'] }}"></span>
                                    </span>
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-400">
                                            Status
                                        </p>
                                        <p class="mt-1 text-sm font-black text-brand-ink">
                                            {{ $opportunityStatusName($featured) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-7">
                                <a
                                    href="{{ $opportunityUrl($featured) }}"
                                    @if ($isExternalOpportunity($featured)) target="_blank" rel="noopener noreferrer" @endif
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-brand-navy px-5 py-3 text-sm font-black text-brand-navy transition hover:bg-brand-navy hover:text-white"
                                >
                                    {{ $buttonLabel($featured) }}
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </a>
                            </div>
                        </aside>
                    </div>
                </article>
            </div>
        </section>
    @endif

    {{-- CONTENT --}}
    <section class="pb-14 lg:pb-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[280px_1fr]">
                {{-- FILTER --}}
                <aside class="h-fit rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-900/5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <h2 class="text-lg font-black text-brand-ink">
                            Filter Opportunities
                        </h2>

                        <a href="{{ $indexUrl }}" class="text-xs font-bold text-slate-400 transition hover:text-brand-navy">
                            Reset
                        </a>
                    </div>

                    <form method="GET" action="{{ $indexUrl }}" class="mt-6 space-y-7">
                        @if ($search)
                            <input type="hidden" name="q" value="{{ $search }}">
                        @endif

                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                                Status
                            </p>

                            <div class="mt-3 space-y-3">
                                @foreach (['open' => 'Masih Dibuka', 'closed' => 'Ditutup'] as $value => $label)
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

                        <div class="border-t border-slate-100 pt-6">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                                Jenis
                            </p>

                            <div class="mt-3 space-y-3">
                                @foreach ($availableTypes as $type)
                                    @php
                                        $slug = $typeSlug($type);
                                    @endphp

                                    <label class="flex cursor-pointer items-center gap-3 text-sm font-semibold text-slate-600">
                                        <input
                                            type="checkbox"
                                            name="type[]"
                                            value="{{ $slug }}"
                                            @checked(in_array($slug, $selectedTypes, true))
                                            class="h-4 w-4 rounded border-slate-300 text-brand-amber focus:ring-brand-amber"
                                        >
                                        <span>{{ $typeName($type) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-6">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                                Format
                            </p>

                            <div class="mt-3 space-y-3">
                                @foreach ($formatLabels as $value => $label)
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

                        <div class="border-t border-slate-100 pt-6">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                                Deadline
                            </p>

                            <div class="mt-3 space-y-3">
                                @foreach (['nearest' => 'Terdekat', 'month' => 'Bulan Ini', 'upcoming' => 'Akan Datang'] as $value => $label)
                                    <label class="flex cursor-pointer items-center gap-3 text-sm font-semibold text-slate-600">
                                        <input
                                            type="radio"
                                            name="deadline"
                                            value="{{ $value }}"
                                            @checked($selectedDeadline === $value)
                                            class="h-4 w-4 border-slate-300 text-brand-amber focus:ring-brand-amber"
                                        >
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid gap-3 border-t border-slate-100 pt-6">
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

                {{-- LIST --}}
                <div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-900/5">
                        <form method="GET" action="{{ $indexUrl }}" class="grid gap-4 lg:grid-cols-[1fr_auto_auto]">
                            @foreach ($selectedStatuses as $status)
                                <input type="hidden" name="status[]" value="{{ $status }}">
                            @endforeach

                            @foreach ($selectedTypes as $type)
                                <input type="hidden" name="type[]" value="{{ $type }}">
                            @endforeach

                            @foreach ($selectedFormats as $format)
                                <input type="hidden" name="format[]" value="{{ $format }}">
                            @endforeach

                            @if ($selectedDeadline)
                                <input type="hidden" name="deadline" value="{{ $selectedDeadline }}">
                            @endif

                            <label class="relative block">
                                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="m21 21-4.35-4.35M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </span>

                                <input
                                    type="search"
                                    name="q"
                                    value="{{ $search }}"
                                    placeholder="Cari beasiswa, magang, call for papers..."
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-12 pr-4 text-sm font-semibold text-brand-ink outline-none transition placeholder:text-slate-400 focus:border-brand-navy focus:bg-white focus:ring-4 focus:ring-brand-navy/10"
                                >
                            </label>

                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-slate-500">
                                    Urutkan:
                                </span>

                                <select
                                    name="sort"
                                    class="h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-brand-ink outline-none transition focus:border-brand-navy focus:ring-4 focus:ring-brand-navy/10"
                                >
                                    <option value="deadline" @selected($sort === 'deadline')>Deadline Terdekat</option>
                                    <option value="latest" @selected($sort === 'latest')>Terbaru</option>
                                    <option value="title" @selected($sort === 'title')>Judul A-Z</option>
                                </select>
                            </div>

                            <button
                                type="submit"
                                class="h-12 rounded-xl bg-brand-navy px-5 text-sm font-black text-white transition hover:bg-brand-ink"
                            >
                                Cari
                            </button>
                        </form>

                        @if ($opportunityItems->isNotEmpty())
                            <div class="mt-6 space-y-4">
                                @foreach ($opportunityItems as $opportunity)
                                    @php
                                        $type = $opportunity->type ?? 'open_collaboration';
                                        $style = $categoryStyles[$type] ?? $categoryStyles['open_collaboration'];
                                        $poster = $posterUrl($opportunity->poster ?? $opportunity->cover_image ?? null);
                                    @endphp

                                    <article class="group overflow-hidden rounded-[1.25rem] border border-slate-200 bg-white transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-slate-900/8">
                                        <div class="grid gap-0 lg:grid-cols-[180px_1fr_210px]">
                                            <div class="relative min-h-44 overflow-hidden bg-linear-to-br {{ $style['poster'] }} p-5 text-white">
                                                @if ($poster)
                                                    <img
                                                        src="{{ $poster }}"
                                                        alt="{{ $opportunity->title }}"
                                                        class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                                    >
                                                    <div class="absolute inset-0 bg-linear-to-t from-brand-navy/85 via-brand-navy/25 to-transparent"></div>
                                                @else
                                                    <div class="absolute inset-0 opacity-20">
                                                        <div class="absolute -bottom-12 -right-10 h-36 w-36 rounded-full border border-white/40"></div>
                                                        <div class="absolute bottom-6 left-5 h-11 w-11 rounded-2xl border border-white/20"></div>
                                                        <div class="absolute bottom-6 left-20 h-11 w-11 rounded-2xl border border-white/20"></div>
                                                    </div>
                                                @endif

                                                <div class="relative flex h-full flex-col justify-between">
                                                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-white/80">
                                                        {{ $opportunityTypeName($opportunity) }}
                                                    </p>

                                                    <h3 class="text-lg font-black leading-tight text-white">
                                                        {{ $opportunity->title }}
                                                    </h3>
                                                </div>
                                            </div>

                                            <div class="p-5">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="edulaw-badge {{ $style['badge'] }}">
                                                        {{ $opportunityTypeName($opportunity) }}
                                                    </span>

                                                    <span class="edulaw-badge edulaw-badge-sky">
                                                        {{ $opportunityStatusName($opportunity) }}
                                                    </span>

                                                    <span class="edulaw-badge edulaw-badge-muted">
                                                        {{ $formatName($opportunity->format ?? null) }}
                                                    </span>
                                                </div>

                                                <h3 class="mt-3 text-xl font-black leading-snug text-brand-ink transition group-hover:text-brand-navy">
                                                    {{ $opportunity->title }}
                                                </h3>

                                                <p class="mt-3 text-sm leading-6 text-slate-600">
                                                    {{ $summaryText($opportunity, 170) }}
                                                </p>
                                            </div>

                                            <aside class="border-t border-slate-100 bg-slate-50 p-5 lg:border-l lg:border-t-0">
                                                <div class="space-y-4">
                                                    <div class="flex gap-3">
                                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-brand-navy shadow-sm">
                                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                                <path d="M8 2v4M16 2v4M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </span>

                                                        <div>
                                                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                                                Deadline
                                                            </p>
                                                            <p class="mt-1 text-sm font-black text-brand-ink">
                                                                {{ $deadlineText($opportunity->deadline ?? null) }}
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <div class="flex gap-3">
                                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-brand-navy shadow-sm">
                                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                                <path d="M12 21s7-4.4 7-11a7 7 0 1 0-14 0c0 6.6 7 11 7 11Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                                                <path d="M12 10.5h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                                            </svg>
                                                        </span>

                                                        <div>
                                                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                                                Lokasi
                                                            </p>
                                                            <p class="mt-1 text-sm font-black text-brand-ink">
                                                                {{ $opportunity->location ?: 'Menyesuaikan' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <a
                                                    href="{{ $opportunityUrl($opportunity) }}"
                                                    @if ($isExternalOpportunity($opportunity)) target="_blank" rel="noopener noreferrer" @endif
                                                    class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl border px-4 py-3 text-sm font-black transition {{ $style['button'] }}"
                                                >
                                                    {{ $buttonLabel($opportunity) }}
                                                    <svg class="h-4 w-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </a>
                                            </aside>
                                        </div>
                                    </article>
                                @endforeach
                            </div>

                            <div class="mt-6 flex flex-col items-center justify-between gap-4 border-t border-slate-100 pt-5 text-sm text-slate-500 sm:flex-row">
                                <p class="font-semibold">
                                    @if ($opportunityCollection instanceof \Illuminate\Pagination\AbstractPaginator)
                                        Menampilkan {{ $opportunityCollection->firstItem() }}–{{ $opportunityCollection->lastItem() }} dari {{ $opportunityCollection->total() }} peluang
                                    @else
                                        Menampilkan {{ $opportunityItems->count() }} peluang
                                    @endif
                                </p>

                                @if (method_exists($opportunityCollection, 'links'))
                                    <div>
                                        {{ $opportunityCollection->withQueryString()->links() }}
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-brand-navy shadow-sm">
                                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M10 6V5a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1m-9 0h14v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        <path d="M9 10h6M9 14h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </div>

                                <h3 class="mt-5 text-xl font-black text-brand-ink">
                                    Belum ada opportunities
                                </h3>

                                <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-600">
                                    Belum ada peluang yang sesuai dengan pencarian atau filter saat ini.
                                    Silakan ubah kata kunci, pilih kategori lain, atau kembali ke seluruh daftar opportunities.
                                </p>

                                <a
                                    href="{{ $indexUrl }}"
                                    class="mt-6 inline-flex items-center justify-center rounded-xl bg-brand-navy px-5 py-3 text-sm font-black text-white transition hover:bg-brand-ink"
                                >
                                    Lihat Semua Opportunities
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- EMPTY / UPCOMING --}}
                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-7 shadow-sm shadow-slate-900/5">
                        <div class="grid items-center gap-6 md:grid-cols-[auto_1fr_auto]">
                            <div class="relative flex h-20 w-20 items-center justify-center">
                                <span class="absolute h-16 w-16 rounded-full bg-brand-amber/15"></span>
                                <svg class="relative h-14 w-14 text-slate-300" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M10 6V5a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1m-9 0h14v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="M9 10h6M9 14h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-2xl font-black text-brand-ink">
                                    Peluang Segera Hadir
                                </h2>

                                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                                    Belum ada peluang pada kategori tertentu. Coba gunakan filter lain atau kembali ke semua opportunities.
                                </p>
                            </div>

                            <a
                                href="{{ $indexUrl }}"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-brand-navy px-5 py-3 text-sm font-black text-brand-navy transition hover:bg-brand-navy hover:text-white"
                            >
                                Lihat Semua Opportunities
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <x-shared.cta-section
        eyebrow="Kolaborasi Peluang"
        title="Punya program, peluang, atau call for papers?"
        body="Edulaw membuka ruang kolaborasi untuk kampus, komunitas, lembaga riset, dan organisasi yang ingin memperluas akses literasi hukum."
        :primary-url="url('/kolaborasi')"
        primary-label="Ajukan Kerja Sama"
        :secondary-url="$contactUrl"
        secondary-label="Hubungi Edulaw"
        background-image="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1800&q=85"
        background-alt="Kolaborasi program literasi hukum"
    />
</main>
@endsection
