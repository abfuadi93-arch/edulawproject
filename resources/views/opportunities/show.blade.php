@extends('layouts.app')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $detailUrl = route('opportunities.show', $opportunity->slug);
    $posterImages = collect($opportunity->poster_urls)->filter()->values();
    $posterImage = $posterImages->first();
    $heroImage = $posterImage ?: 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1800&q=85';
    $ogImage = edulaw_file_url($opportunity->og_image ?: ($opportunity->poster_paths[0] ?? null), 'images/hero/hero-edulaw.jpg');
    $summary = $opportunity->excerpt ?: Str::limit(strip_tags($opportunity->description ?? ''), 180);
    $description = $opportunity->getRawOriginal('description');
    $descriptionIsHtml = Str::contains((string) $description, ['<p', '<br', '<ul', '<ol', '<div']);
    $descriptionParagraphs = collect(preg_split('/\R{2,}/', trim((string) $description)) ?: [])
        ->map(fn ($paragraph) => trim($paragraph))
        ->filter()
        ->values();

    $formatLabels = [
        'online' => 'Online',
        'offline' => 'Offline',
        'hybrid' => 'Hybrid',
    ];

    $formatLabel = filled($opportunity->format)
        ? ($formatLabels[$opportunity->format] ?? Str::headline(str_replace('_', ' ', (string) $opportunity->format)))
        : 'Fleksibel';

    $deadlineLabel = 'Tidak dibatasi';
    $deadlineIso = null;

    if ($opportunity->deadline) {
        try {
            $deadline = $opportunity->deadline instanceof Carbon
                ? $opportunity->deadline
                : Carbon::parse($opportunity->deadline);

            $deadlineLabel = $deadline->translatedFormat('d F Y');
            $deadlineIso = $deadline->toDateString();
        } catch (Throwable $e) {
            $deadlineLabel = (string) $opportunity->deadline;
        }
    }

    $applicationUrl = filled($opportunity->application_link)
        ? $opportunity->application_link
        : route('contact.index');
    $applicationIsExternal = Str::startsWith($applicationUrl, ['http://', 'https://']);
    $applicationLabel = filled($opportunity->application_link) ? 'Buka Pendaftaran' : 'Hubungi Edulaw';

    $eligibilityItems = collect($opportunity->eligibility ?? [])
        ->map(fn ($item) => is_array($item) ? ($item['item'] ?? $item['text'] ?? $item['value'] ?? null) : $item)
        ->map(fn ($item) => trim((string) $item))
        ->filter()
        ->values();

    $benefitItems = collect($opportunity->benefits ?? [])
        ->map(fn ($item) => is_array($item) ? ($item['item'] ?? $item['text'] ?? $item['value'] ?? null) : $item)
        ->map(fn ($item) => trim((string) $item))
        ->filter()
        ->values();

    $detailRows = collect([
        ['label' => 'Status', 'value' => $opportunity->display_status],
        ['label' => 'Deadline', 'value' => $deadlineLabel],
        ['label' => 'Format', 'value' => $formatLabel],
        ['label' => 'Lokasi', 'value' => $opportunity->location ?: 'Fleksibel'],
        ['label' => 'Jenis', 'value' => $opportunity->display_type],
    ])->filter(fn ($row) => filled($row['value']))->values();

    $relatedOpportunities = collect($relatedOpportunities ?? []);
@endphp

@section('title', $opportunity->seo_title ?: $opportunity->title)
@section('meta_description', $opportunity->seo_description ?: $summary)
@section('canonical_url', $detailUrl)
@section('og_type', 'article')
@section('og_image', $ogImage)
@section('og_image_alt', $opportunity->title)

@push('head')
    <x-structured-data :data="\App\Support\StructuredData::breadcrumbs([
        ['name' => 'Beranda', 'url' => route('home')],
        ['name' => 'Peluang', 'url' => route('opportunities.index')],
        ['name' => $opportunity->title, 'url' => $detailUrl],
    ])" />
@endpush

@section('content')
<main class="bg-[#f7f8fa]">
    <section class="relative isolate overflow-hidden bg-brand-navy text-white">
        <img
            src="{{ $heroImage }}"
            alt="{{ $opportunity->title }}"
            class="absolute inset-0 z-0 h-full w-full object-cover"
        >
        <div class="absolute inset-0 z-0 bg-linear-to-r from-[#06132a]/96 via-[#06132a]/76 to-[#06132a]/32"></div>
        <div class="absolute inset-0 z-0 bg-linear-to-t from-[#06132a]/82 via-transparent to-[#06132a]/25"></div>

        <div class="relative z-10 mx-auto max-w-7xl px-5 py-12 sm:px-6 lg:px-8 lg:py-16">
            <nav class="flex flex-wrap items-center gap-2 text-xs font-bold text-white/70 sm:text-sm" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="transition hover:text-white">Beranda</a>
                <span class="text-white/40">/</span>
                <a href="{{ route('opportunities.index') }}" class="transition hover:text-white">Opportunities</a>
                <span class="text-white/40">/</span>
                <span class="text-white">Detail Peluang</span>
            </nav>

            <div class="mt-7 max-w-5xl">
                <p class="edulaw-badge edulaw-badge-md edulaw-badge-dark">
                    {{ $opportunity->display_type }}
                </p>

                <h1 class="mt-4 max-w-5xl text-4xl font-black leading-[1.04] tracking-tight text-white sm:text-5xl lg:text-[3.6rem]">
                    {{ $opportunity->title }}
                </h1>

                @if ($summary)
                    <p class="mt-5 max-w-3xl text-base font-medium leading-8 text-white/84 sm:text-lg">
                        {{ $summary }}
                    </p>
                @endif
            </div>

            <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="flex items-center gap-3 rounded-2xl border border-white/70 bg-white px-4 py-4 shadow-xl shadow-black/10">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-amber-soft text-brand-navy">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7 3v4M17 3v4M4 9h16M5 5h14v16H5V5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-teal">Deadline</p>
                        <p class="mt-1 text-sm font-black leading-snug text-brand-navy">
                            @if ($deadlineIso)
                                <time datetime="{{ $deadlineIso }}">{{ $deadlineLabel }}</time>
                            @else
                                {{ $deadlineLabel }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-2xl border border-white/70 bg-white px-4 py-4 shadow-xl shadow-black/10">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-sky-soft text-brand-navy">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 5h16v14H4V5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                            <path d="M4 10h16M9 10v9" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-teal">Format</p>
                        <p class="mt-1 text-sm font-black leading-snug text-brand-navy">{{ $formatLabel }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-2xl border border-white/70 bg-white px-4 py-4 shadow-xl shadow-black/10">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-teal-soft text-brand-navy">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 21s7-4.44 7-11a7 7 0 1 0-14 0c0 6.56 7 11 7 11Z" stroke="currentColor" stroke-width="2" />
                            <path d="M12 10.5h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-teal">Lokasi</p>
                        <p class="mt-1 text-sm font-black leading-snug text-brand-navy">{{ $opportunity->location ?: 'Fleksibel' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-2xl border border-white/70 bg-white px-4 py-4 shadow-xl shadow-black/10">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-amber-soft text-brand-navy">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M10 6V5a2 2 0 0 1 4 0v1m-9 0h14v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                            <path d="M9 11h6M9 15h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-teal">Status</p>
                        <p class="mt-1 text-sm font-black leading-snug text-brand-navy">{{ $opportunity->display_status }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="mx-auto grid max-w-7xl gap-8 px-5 py-10 sm:px-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:px-8 lg:py-14">
        <div class="space-y-7">
            @if ($posterImages->isNotEmpty())
                <section
                    x-data="{
                        active: 0,
                        total: {{ $posterImages->count() }},
                        next() { this.active = (this.active + 1) % this.total },
                        previous() { this.active = (this.active - 1 + this.total) % this.total },
                        goTo(index) { this.active = index },
                    }"
                    @keydown.left.stop.prevent="previous()"
                    @keydown.right.stop.prevent="next()"
                    class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
                    aria-roledescription="carousel"
                    aria-label="Poster {{ $opportunity->title }}"
                    tabindex="0"
                    data-opportunity-poster-slider
                >
                    <div class="relative overflow-hidden bg-slate-100">
                        @foreach ($posterImages as $index => $image)
                            <figure
                                x-show="active === {{ $index }}"
                                x-transition.opacity.duration.300ms
                                @if ($index !== 0) x-cloak @endif
                                class="relative h-[min(78vh,820px)] min-h-[360px]"
                                role="group"
                                aria-roledescription="slide"
                                aria-label="Poster {{ $index + 1 }} dari {{ $posterImages->count() }}"
                            >
                                <img
                                    src="{{ $image }}"
                                    alt="Poster {{ $index + 1 }} — {{ $opportunity->title }}"
                                    class="h-full w-full object-contain"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </figure>
                        @endforeach

                        @if ($posterImages->count() > 1)
                            <div class="pointer-events-none absolute inset-x-0 top-1/2 flex -translate-y-1/2 justify-between px-3 sm:px-5">
                                <button
                                    type="button"
                                    @click="previous()"
                                    class="pointer-events-auto grid size-11 place-items-center rounded-full border border-white/35 bg-[#102b50]/85 text-2xl font-bold text-white shadow-lg backdrop-blur transition hover:bg-[#102b50] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber"
                                    aria-label="Poster sebelumnya"
                                >
                                    <span aria-hidden="true">‹</span>
                                </button>
                                <button
                                    type="button"
                                    @click="next()"
                                    class="pointer-events-auto grid size-11 place-items-center rounded-full border border-white/35 bg-[#102b50]/85 text-2xl font-bold text-white shadow-lg backdrop-blur transition hover:bg-[#102b50] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber"
                                    aria-label="Poster berikutnya"
                                >
                                    <span aria-hidden="true">›</span>
                                </button>
                            </div>

                            <div class="absolute bottom-4 right-4 rounded-full bg-[#102b50]/85 px-3 py-1.5 text-xs font-black text-white backdrop-blur" aria-live="polite">
                                <span x-text="String(active + 1).padStart(2, '0')">01</span>
                                <span class="text-white/60"> / {{ str_pad((string) $posterImages->count(), 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                        @endif
                    </div>

                    @if ($posterImages->count() > 1)
                        <div class="flex items-center justify-center gap-2 border-t border-slate-200 px-4 py-4" aria-label="Pilih poster">
                            @foreach ($posterImages as $index => $image)
                                <button
                                    type="button"
                                    @click="goTo({{ $index }})"
                                    :aria-current="active === {{ $index }} ? 'true' : null"
                                    :class="active === {{ $index }} ? 'w-8 bg-brand-navy' : 'w-2.5 bg-slate-300 hover:bg-slate-400'"
                                    class="h-2.5 rounded-full transition-all"
                                    aria-label="Tampilkan poster {{ $index + 1 }}"
                                ></button>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            @if ($description)
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <p class="text-xs font-black uppercase tracking-[0.26em] text-brand-teal">
                        Tentang Peluang
                    </p>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-brand-navy sm:text-3xl">
                        Informasi utama
                    </h2>

                    <div class="edulaw-readable mt-6 max-w-3xl text-base text-slate-700">
                        @if ($descriptionIsHtml)
                            {!! $description !!}
                        @else
                            @foreach ($descriptionParagraphs as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        @endif
                    </div>
                </section>
            @endif

            @if ($eligibilityItems->isNotEmpty() || $benefitItems->isNotEmpty())
                <section class="grid gap-5 lg:grid-cols-2">
                    @if ($eligibilityItems->isNotEmpty())
                        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
                            <p class="text-xs font-black uppercase tracking-[0.24em] text-brand-teal">
                                Syarat
                            </p>
                            <h2 class="mt-3 text-xl font-black tracking-tight text-brand-navy">
                                Kriteria peserta
                            </h2>

                            <ul class="mt-5 space-y-3">
                                @foreach ($eligibilityItems as $item)
                                    <li class="flex gap-3">
                                        <span class="mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-brand-teal-soft text-brand-teal">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                                <path d="m5 10 3 3 7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <p class="text-sm font-semibold leading-7 text-slate-600">{{ $item }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        </article>
                    @endif

                    @if ($benefitItems->isNotEmpty())
                        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
                            <p class="text-xs font-black uppercase tracking-[0.24em] text-brand-teal">
                                Manfaat
                            </p>
                            <h2 class="mt-3 text-xl font-black tracking-tight text-brand-navy">
                                Yang diperoleh
                            </h2>

                            <ul class="mt-5 space-y-3">
                                @foreach ($benefitItems as $item)
                                    <li class="flex gap-3">
                                        <span class="mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-brand-amber-soft text-brand-navy">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                                <path d="m5 10 3 3 7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <p class="text-sm font-semibold leading-7 text-slate-600">{{ $item }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        </article>
                    @endif
                </section>
            @endif

            @if ($relatedOpportunities->isNotEmpty())
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.26em] text-brand-teal">
                                Peluang Terkait
                            </p>
                            <h2 class="mt-3 text-2xl font-black tracking-tight text-brand-navy">
                                Kesempatan lain yang relevan
                            </h2>
                        </div>

                        <a href="{{ route('opportunities.index') }}" class="inline-flex items-center gap-2 text-sm font-black text-brand-navy transition hover:text-brand-teal">
                            Lihat semua
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        @foreach ($relatedOpportunities as $related)
                            <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-teal">
                                    {{ $related->display_type }}
                                </p>
                                <h3 class="mt-3 line-clamp-2 text-base font-black leading-snug text-brand-navy">
                                    <a href="{{ route('opportunities.show', $related->slug) }}" class="transition hover:text-brand-teal">
                                        {{ $related->title }}
                                    </a>
                                </h3>
                                <p class="mt-3 text-sm font-semibold text-slate-500">
                                    {{ $related->deadline ? $related->deadline->translatedFormat('d F Y') : 'Tidak dibatasi' }}
                                </p>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <aside class="space-y-5 lg:sticky lg:top-24 lg:self-start">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-brand-teal">
                    Detail Peluang
                </p>

                <div class="mt-5 divide-y divide-slate-200">
                    @foreach ($detailRows as $row)
                        <div class="flex items-start justify-between gap-4 py-3 first:pt-0 last:pb-0">
                            <dt class="text-sm font-semibold text-slate-500">{{ $row['label'] }}</dt>
                            <dd class="max-w-[11rem] text-right text-sm font-black leading-6 text-brand-navy">{{ $row['value'] }}</dd>
                        </div>
                    @endforeach
                </div>

                <a
                    href="{{ $applicationUrl }}"
                    @if ($applicationIsExternal) target="_blank" rel="noopener" @endif
                    class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-full bg-brand-amber px-5 py-3 text-sm font-black text-brand-ink shadow-sm shadow-brand-ink/10 transition hover:-translate-y-0.5 hover:bg-brand-amber/90"
                >
                    {{ $applicationLabel }}
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>
            </section>

        </aside>
    </div>

    <x-shared.cta-section
        eyebrow="Kolaborasi Peluang"
        title="Punya program, peluang, atau call for papers?"
        body="Edulaw membuka ruang kolaborasi untuk kampus, komunitas, lembaga riset, dan organisasi yang ingin memperluas akses literasi hukum."
        :primary-url="route('collaboration.index')"
        primary-label="Ajukan Kerja Sama"
        :secondary-url="route('opportunities.index')"
        secondary-label="Lihat Opportunities"
        background-image="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1800&q=85"
        background-alt="Kolaborasi program literasi hukum"
    />
</main>
@endsection
