@extends('layouts.app')

@php
    use Illuminate\Support\Str;

    $detailUrl = route('opportunities.show', $opportunity->slug);
    $posterImages = collect($opportunity->poster_urls)->filter()->values();
    $summary = (Str::squish(strip_tags($opportunity->seo_description ?: $opportunity->excerpt ?: $opportunity->description ?: '')));
    $excerpt = Str::squish(strip_tags((string) $opportunity->excerpt));
    $description = trim((string) $opportunity->getRawOriginal('description'));
    $descriptionIsHtml = Str::contains($description, ['<p', '<br', '<ul', '<ol', '<div', '<h2', '<h3']);
    $descriptionParagraphs = collect(preg_split('/\R{2,}/', $description) ?: [])->map(fn ($paragraph) => trim($paragraph))->filter();
    $officialUrl = $opportunity->external_url;
    $optionalLinks = collect($opportunity->optional_links);
    $ogImage = edulaw_file_url($opportunity->og_image ?: ($opportunity->poster_paths[0] ?? null), 'images/hero/hero-edulaw.jpg');
    $detailRows = collect([
        ['label' => 'Status', 'value' => $opportunity->display_status],
        ['label' => 'Deadline', 'value' => $opportunity->deadline_display],
        ['label' => 'Format', 'value' => $opportunity->display_format],
        ['label' => 'Lokasi', 'value' => $opportunity->location ?: 'Fleksibel'],
        ['label' => 'Penyelenggara', 'value' => $opportunity->organizer],
        ['label' => 'Jenis', 'value' => $opportunity->display_type],
        ['label' => 'Kurasi', 'value' => $opportunity->featured ? 'Pilihan Edulaw' : null],
    ])->filter(fn (array $row): bool => filled($row['value']));
    $eligibilityItems = collect($opportunity->eligibility ?? [])
        ->map(fn ($item) => is_array($item) ? ($item['item'] ?? $item['text'] ?? $item['value'] ?? null) : $item)
        ->map(fn ($item) => trim(strip_tags((string) $item)))->filter()->values();
    $benefitItems = collect($opportunity->benefits ?? [])
        ->map(fn ($item) => is_array($item) ? ($item['item'] ?? $item['text'] ?? $item['value'] ?? null) : $item)
        ->map(fn ($item) => trim(strip_tags((string) $item)))->filter()->values();
@endphp

@section('title', $opportunity->seo_title ?: $opportunity->title)
@section('meta_description', $summary ?: 'Informasi peluang pengembangan hukum dari Edulaw Project.')
@section('canonical_url', $detailUrl)
@section('og_type', 'article')
@section('og_image', $ogImage)
@section('og_image_alt', $opportunity->title)

@push('head')
    <x-structured-data :data="\App\Support\StructuredData::breadcrumbs([
        ['name' => 'Beranda', 'url' => route('home')],
        ['name' => 'Opportunities', 'url' => route('opportunities.index')],
        ['name' => $opportunity->title, 'url' => $detailUrl],
    ])" />
@endpush

@section('content')
<main class="bg-[#f7f8fa] text-brand-ink">
    <section class="relative isolate overflow-hidden bg-brand-navy py-3 text-white">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_80%_15%,rgba(60,181,165,.28),transparent_34%),linear-gradient(135deg,#07162d_0%,#173b67_62%,#205f73_100%)]"></div>
        <div class="relative mx-auto grid h-[440px] max-w-7xl content-center gap-6 px-5 py-7 sm:h-[400px] sm:px-6 sm:py-8 lg:h-[240px] lg:grid-cols-[minmax(0,3fr)_minmax(300px,2fr)] lg:items-center lg:gap-10 lg:px-8 lg:py-4">
            <div class="min-w-0">
                <nav class="flex flex-wrap items-center gap-1.5 text-xs font-medium text-white/60" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="transition hover:text-white">Beranda</a>
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('opportunities.index') }}" class="transition hover:text-white">Opportunities</a>
                    <span aria-hidden="true">/</span>
                    <span class="text-white">Detail Peluang</span>
                </nav>

                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="edulaw-badge edulaw-badge-md edulaw-badge-dark">{{ $opportunity->display_type }}</span>
                    @if ($opportunity->featured)
                        <span class="edulaw-badge edulaw-badge-md bg-brand-amber text-brand-ink">Pilihan Edulaw</span>
                    @endif
                </div>
                <h1 class="mt-1.5 max-w-4xl text-balance font-display text-3xl font-bold leading-tight text-white sm:text-4xl ">{{ $opportunity->title }}</h1>
            </div>

            <dl class="grid grid-cols-2 overflow-hidden rounded-[14px] border border-white/15 bg-white/10 backdrop-blur-sm" aria-label="Ringkasan peluang">
                @foreach ($detailRows->take(4) as $row)
                    <div @class([
                        'min-w-0 border-white/15 p-3',
                        'border-l' => $loop->iteration % 2 === 0,
                        'border-t' => $loop->iteration > 2,
                    ])>
                        <dt class="text-[10px] font-bold uppercase leading-4 tracking-[0.1em] text-white/70">{{ $row['label'] }}</dt>
                        <dd class="mt-1  text-sm font-bold leading-snug text-brand-amber">{{ $row['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <div class="mx-auto grid max-w-7xl gap-8 px-5 py-10 sm:px-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:px-8 lg:py-14">
        <div class="min-w-0 space-y-7">
            @if ($posterImages->isNotEmpty())
                <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm" aria-label="Poster {{ $opportunity->title }}" aria-roledescription="carousel" tabindex="0" data-opportunity-poster-slider>
                    <div class="relative bg-slate-100">
                        @foreach ($posterImages as $index => $image)
                            <figure data-poster-slide @if ($index !== 0) hidden @endif class="relative h-[min(76vh,800px)] min-h-[360px]" aria-label="Poster {{ $index + 1 }} dari {{ $posterImages->count() }}">
                                <img src="{{ $image }}" alt="Poster {{ $index + 1 }} — {{ $opportunity->title }}" class="h-full w-full object-contain" @if ($index === 0) fetchpriority="high" @else loading="lazy" @endif decoding="async">
                            </figure>
                        @endforeach

                        @if ($posterImages->count() > 1)
                            <div class="pointer-events-none absolute inset-x-0 top-1/2 flex -translate-y-1/2 justify-between px-3 sm:px-5">
                                <button type="button" data-poster-previous class="pointer-events-auto grid size-11 place-items-center rounded-full bg-brand-navy/90 text-2xl font-bold text-white shadow-lg" aria-label="Poster sebelumnya">‹</button>
                                <button type="button" data-poster-next class="pointer-events-auto grid size-11 place-items-center rounded-full bg-brand-navy/90 text-2xl font-bold text-white shadow-lg" aria-label="Poster berikutnya">›</button>
                            </div>
                            <div class="absolute bottom-4 right-4 rounded-full bg-brand-navy/90 px-3 py-1.5 text-xs font-black text-white">
                                <span data-poster-counter>01</span> / {{ str_pad((string) $posterImages->count(), 2, '0', STR_PAD_LEFT) }}
                            </div>
                        @endif
                    </div>

                    @if ($posterImages->count() > 1)
                        <div class="flex justify-center gap-2 border-t border-slate-200 p-4" aria-label="Pilih poster">
                            @foreach ($posterImages as $index => $image)
                                <button type="button" data-poster-dot="{{ $index }}" @if ($index === 0) aria-current="true" @endif class="h-2.5 {{ $index === 0 ? 'w-8 bg-brand-navy' : 'w-2.5 bg-slate-300 hover:bg-slate-400' }} rounded-full transition-all" aria-label="Tampilkan poster {{ $index + 1 }}"></button>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            @if ($excerpt !== '')
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="opportunity-summary-heading">
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-brand-teal">Ringkasan Peluang</p>
                    <h2 id="opportunity-summary-heading" class="mt-3 text-2xl font-black tracking-tight text-brand-navy sm:text-3xl">Informasi singkat</h2>
                    <p class="mt-5 max-w-3xl text-base leading-8 text-slate-700">{{ $excerpt }}</p>
                </section>
            @endif

            @if ($description !== '')
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-brand-teal">Tentang Peluang</p>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-brand-navy sm:text-3xl">Informasi utama</h2>
                    <div class="edulaw-readable mt-6 max-w-none text-base text-slate-700">
                        @if ($descriptionIsHtml)
                            {!! $description !!}
                        @else
                            @foreach ($descriptionParagraphs as $paragraph)<p>{{ $paragraph }}</p>@endforeach
                        @endif
                    </div>
                </section>
            @endif

            @if ($eligibilityItems->isNotEmpty() || $benefitItems->isNotEmpty())
                <section class="grid gap-5 md:grid-cols-2">
                    @foreach ([['Syarat', 'Kriteria peserta', $eligibilityItems], ['Manfaat', 'Yang diperoleh', $benefitItems]] as [$eyebrow, $heading, $items])
                        @if ($items->isNotEmpty())
                            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                                <p class="text-xs font-black uppercase tracking-[0.24em] text-brand-teal">{{ $eyebrow }}</p>
                                <h2 class="mt-3 text-xl font-black text-brand-navy">{{ $heading }}</h2>
                                <ul class="mt-5 space-y-3">
                                    @foreach ($items as $item)
                                        <li class="flex gap-3 text-sm font-semibold leading-7 text-slate-600"><span class="mt-1 grid size-6 shrink-0 place-items-center rounded-lg bg-brand-teal-soft text-brand-teal">✓</span><span>{{ $item }}</span></li>
                                    @endforeach
                                </ul>
                            </article>
                        @endif
                    @endforeach
                </section>
            @endif

            @if ($relatedOpportunities->isNotEmpty())
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-brand-teal">Peluang Terkait</p>
                    <h2 class="mt-3 text-2xl font-black text-brand-navy">Kesempatan lain yang relevan</h2>
                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        @foreach ($relatedOpportunities as $related)
                            <a href="{{ route('opportunities.show', $related->slug) }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:border-brand-amber">
                                <span class="text-[10px] font-black uppercase tracking-[0.16em] text-brand-teal">{{ $related->display_type }}</span>
                                <span class="mt-3 block  text-base font-black leading-snug text-brand-navy">{{ $related->title }}</span>
                                <span class="mt-3 block text-xs font-semibold text-slate-500">{{ $related->deadline_display }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <aside class="space-y-5 lg:sticky lg:top-24 lg:self-start" aria-label="Detail dan tautan peluang">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-brand-teal">Detail Peluang</p>
                <dl class="mt-5 divide-y divide-slate-200">
                    @foreach ($detailRows as $row)
                        <div class="flex items-start justify-between gap-4 py-3 first:pt-0 last:pb-0">
                            <dt class="text-sm font-semibold text-slate-500">{{ $row['label'] }}</dt>
                            <dd class="max-w-[11rem] text-right text-sm font-black leading-6 text-brand-navy">{{ $row['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>

                @if ($officialUrl)
                    <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="mt-6 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand-amber px-5 py-3 text-center text-sm font-black text-brand-ink transition hover:bg-brand-amber/85">Informasi Resmi <span aria-hidden="true">↗</span></a>
                @endif
                @foreach ($optionalLinks as $link)
                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-brand-navy px-4 py-3 text-center text-sm font-black text-white transition hover:bg-brand-ink">{{ $link['label'] }}</a>
                @endforeach
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-black text-brand-navy">Bagikan Peluang</h2>
                <x-share-buttons :title="$opportunity->title" :url="$detailUrl" :description="$summary" label="" class="mt-4 justify-start" />
            </section>
        </aside>
    </div>
</main>
@endsection
