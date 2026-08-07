@extends('layouts.app')

@section('title', $publication->share_preview_title)
@section('meta_description', $publication->share_preview_description)
@section('canonical_url', route('publications.show', $publication->slug))
@section('og_type', 'article')
@section('og_image', $publication->share_preview_image_url ?: asset('images/hero/hero-edulaw.jpg'))
@section('og_image_alt', $publication->title)

@push('head')
    <x-structured-data :data="\App\Support\StructuredData::publication($publication)" />
    <x-structured-data :data="\App\Support\StructuredData::breadcrumbs([
        ['name' => 'Beranda', 'url' => route('home')],
        ['name' => 'Riset dan Publikasi', 'url' => route('publications.index')],
        ['name' => $publication->title, 'url' => route('publications.show', $publication->slug)],
    ])" />
@endpush

@section('content')
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $indexUrl = Route::has('publications.index') ? route('publications.index') : url('/riset-publikasi');
    $collaborationUrl = Route::has('collaboration.index') ? route('collaboration.index') : url('/kolaborasi');

    $coverImage = edulaw_file_url($publication->cover_image ?? null);
    $pdfUrl = $hasPdfFile ? route('publications.download', $publication->slug) : null;
    $pdfPreviewUrl = $hasPdfFile ? route('publications.preview', $publication->slug).'#page=1&toolbar=0&navpanes=0&scrollbar=0&view=FitH' : null;
    $externalUrl = \App\Support\EdulawSite::resolveUrl($publication->external_url);

    $typeName = $publication?->type?->name
        ?? $publication?->publicationType?->name
        ?? $publication?->publication_type
        ?? $publication?->type
        ?? 'Publikasi';

    $authorCollection = isset($publication->authors)
        ? $publication->authors
            ->filter(fn ($author) => $author->is_active !== false)
            ->sortBy(fn ($author) => $author->pivot?->author_order ?? 999)
            ->values()
        : collect();

    $authorNames = $authorCollection->pluck('name')->filter()->join(', ');
    $sourceName = trim((string) ($publication->source_name ?: 'Edulaw Project'));
    $creatorLabel = $authorNames ? 'Penulis' : 'Penerbit';
    $creatorValue = $authorNames ?: $sourceName;

    $publicationDate = $publication->publication_date_display;

    $documentFormat = $pdfUrl ? 'PDF digital' : 'Dokumen digital';
    $pageOrFormatLabel = $publication->page_count ? 'Jumlah Halaman' : 'Format';
    $pageOrFormatValue = $publication->page_count ? $publication->page_count.' halaman' : $documentFormat;
    $languageLabel = match ($publication->language) {
        'en' => 'English',
        default => 'Indonesia',
    };
    $statusLabel = match ($publication->status) {
        'published' => 'Terbit',
        'draft' => 'Draf',
        'archived' => 'Arsip',
        default => Str::headline((string) ($publication->status ?: 'Dokumen')),
    };

    $summarySource = filled($publication->description)
        ? $publication->description
        : $publication->excerpt;
    $summaryText = trim(strip_tags((string) $summarySource));
    $genericSummaryNeedles = [
        'publikasi edulaw project untuk mendukung literasi hukum',
        'riset kebijakan, dan penguatan pengetahuan publik',
    ];
    $summaryIsGeneric = blank($summaryText)
        || Str::of($summaryText)->lower()->contains($genericSummaryNeedles);
    $summaryIsHtml = Str::contains((string) $summarySource, ['<p', '<br', '<ul', '<ol', '<div']);
    $summaryParagraphs = collect(preg_split('/\R{2,}/', $summaryText) ?: [])
        ->map(fn ($paragraph) => trim($paragraph))
        ->filter()
        ->values();

    $tags = collect($publication->tags ?? []);
    $relatedCollection = collect($relatedPublications ?? $related ?? collect());
    $citationFormats = $publication->citationFormats();
    $citationText = $citationFormats['apa'];

    $metadataRows = collect([
        ['label' => $creatorLabel, 'value' => $creatorValue],
        ['label' => 'Tahun / Tanggal Publikasi', 'value' => $publicationDate],
        ['label' => 'Tipe Publikasi', 'value' => $typeName],
        ['label' => $pageOrFormatLabel, 'value' => $pageOrFormatValue],
        ['label' => 'Bahasa', 'value' => $languageLabel],
        ['label' => 'Status Dokumen', 'value' => $statusLabel],
    ])->filter(fn ($row) => filled($row['value']))->values();

    $relatedTypeName = fn ($item): string => $item?->type?->name
        ?? $item?->publicationType?->name
        ?? $item?->publication_type
        ?? $item?->type
        ?? 'Publikasi';

    $relatedYear = fn ($item): string => $item->publication_year ?: 'Dokumen digital';
@endphp

<main class="publication-show">
    <section class="relative isolate overflow-hidden bg-brand-navy text-white">
        @if ($coverImage)
            <img
                src="{{ $coverImage }}"
                alt="{{ $publication->title }}"
                class="absolute inset-0 z-0 h-full w-full object-cover"
            >
        @else
            <div class="absolute inset-0 z-0 bg-[radial-gradient(circle_at_18%_24%,rgba(245,185,67,0.22),transparent_30%),radial-gradient(circle_at_82%_22%,rgba(37,183,160,0.18),transparent_28%),linear-gradient(135deg,#071427,#1f3c69_54%,#10243f)]"></div>
        @endif

        <div class="absolute inset-0 z-0 bg-linear-to-r from-[#06132a]/96 via-[#06132a]/78 to-[#06132a]/42"></div>
        <div class="absolute inset-0 z-0 bg-linear-to-t from-[#06132a]/82 via-transparent to-[#06132a]/18"></div>

        <div class="relative z-10 mx-auto max-w-7xl px-5 py-11 sm:px-6 lg:px-8 lg:py-16">
            <nav class="flex flex-wrap items-center gap-2 text-xs font-bold text-white/72 sm:text-sm" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="transition hover:text-white">Beranda</a>
                <span class="text-white/42">/</span>
                <a href="{{ $indexUrl }}" class="transition hover:text-white">Publikasi &amp; Riset</a>
                <span class="text-white/42">/</span>
                <span class="text-white">Detail Publikasi</span>
            </nav>

            <div class="mt-7 max-w-5xl">
                <span class="edulaw-badge edulaw-badge-md edulaw-badge-dark">
                    {{ $typeName }}
                </span>

                <h1 class="mt-5 max-w-5xl text-4xl font-black leading-[1.04] tracking-tight text-white sm:text-5xl lg:text-[3.5rem]">
                    {{ $publication->title }}
                </h1>

                <dl class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['label' => 'Tipe', 'value' => $typeName],
                        ['label' => $creatorLabel, 'value' => $creatorValue],
                        ['label' => 'Tahun / Tanggal', 'value' => $publicationDate],
                        ['label' => 'Format', 'value' => $documentFormat],
                    ] as $item)
                        <div class="rounded-2xl border border-white/40 bg-white px-4 py-3 shadow-sm shadow-slate-950/10">
                            <dt class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-teal">
                                {{ $item['label'] }}
                            </dt>
                            <dd class="mt-1 line-clamp-2 text-sm font-black leading-snug text-brand-navy">
                                {{ $item['value'] }}
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </section>

    <section class="publication-body">
        <div class="bg-[#f8fafc] py-10 lg:py-14">
            <div class="mx-auto grid max-w-7xl gap-8 px-5 sm:px-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:px-8">
                <div class="space-y-7">
                    <article id="preview-pdf" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 lg:p-8">
                        <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.24em] text-brand-teal">
                                    Preview PDF
                                </p>
                                <h2 class="mt-3 text-2xl font-black tracking-tight text-brand-navy sm:text-3xl">
                                    Baca dokumen langsung dari halaman ini.
                                </h2>
                            </div>

                            @if ($pdfUrl)
                                <a
                                    href="{{ $pdfUrl }}"
                                    class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-xl bg-brand-amber px-4 py-2.5 text-sm font-black text-brand-black shadow-sm transition hover:-translate-y-0.5 hover:bg-[#e7a72d]"
                                >
                                    Unduh Publikasi
                                </a>
                            @elseif ($externalUrl)
                                <a
                                    href="{{ $externalUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-xl bg-brand-navy px-4 py-2.5 text-sm font-black text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-brand-black"
                                >
                                    Buka Sumber Publikasi
                                </a>
                            @endif
                        </div>

                        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100">
                            @if ($pdfPreviewUrl)
                                <iframe
                                    src="{{ $pdfPreviewUrl }}"
                                    title="Preview dokumen {{ $publication->title }}"
                                    loading="lazy"
                                    class="h-[520px] w-full bg-white lg:h-[720px]"
                                ></iframe>
                            @else
                                <div class="flex min-h-[320px] items-center justify-center bg-linear-to-br from-brand-navy via-[#102f55] to-brand-teal/80 p-8 text-center text-white">
                                    <div class="max-w-md">
                                        <p class="text-xs font-black uppercase tracking-[0.24em] text-brand-amber">
                                            Dokumen PDF belum tersedia.
                                        </p>
                                        <p class="mt-4 text-base font-semibold leading-7 text-white/78">
                                            Gunakan sumber publikasi jika tersedia, atau kembali ke katalog untuk membaca publikasi lain.
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </article>

                    @if (! $summaryIsGeneric)
                        <article id="ringkasan" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            <p class="text-xs font-black uppercase tracking-[0.24em] text-brand-teal">
                                Ringkasan Publikasi
                            </p>

                            <div class="edulaw-readable mt-5 max-w-3xl text-slate-700">
                                @if ($summaryIsHtml)
                                    {!! $summarySource !!}
                                @else
                                    @foreach ($summaryParagraphs as $paragraph)
                                        <p>{{ $paragraph }}</p>
                                    @endforeach
                                @endif
                            </div>
                        </article>
                    @endif

                    @if ($relatedCollection->isNotEmpty())
                        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.24em] text-brand-teal">
                                        Publikasi Terkait
                                    </p>
                                    <h2 class="mt-3 text-2xl font-black tracking-tight text-brand-navy">
                                        Baca publikasi lainnya.
                                    </h2>
                                </div>

                                <a href="{{ $indexUrl }}" class="text-sm font-black text-brand-navy underline-offset-4 transition hover:text-brand-teal hover:underline">
                                    Semua Publikasi
                                </a>
                            </div>

                            <div class="mt-6 grid gap-4 md:grid-cols-3">
                                @foreach ($relatedCollection->take(3) as $relatedPublication)
                                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-teal">
                                            {{ $relatedTypeName($relatedPublication) }}
                                        </p>
                                        <h3 class="mt-3 line-clamp-2 text-base font-black leading-snug text-brand-navy">
                                            {{ $relatedPublication->title }}
                                        </h3>
                                        <p class="mt-3 text-sm font-bold text-slate-500">
                                            {{ $relatedYear($relatedPublication) }} · {{ $relatedPublication->pdf_file ? 'PDF digital' : 'Dokumen digital' }}
                                        </p>
                                        <a href="{{ route('publications.show', $relatedPublication->slug) }}" class="mt-4 inline-flex text-sm font-black text-brand-navy transition hover:text-brand-teal">
                                            Lihat Detail →
                                        </a>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>

                <aside class="space-y-6 self-start lg:sticky lg:top-24">
                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-brand-teal">
                            Detail Publikasi
                        </p>

                        <dl class="mt-5 divide-y divide-slate-100 text-sm">
                            @foreach ($metadataRows as $row)
                                <div class="flex justify-between gap-5 py-3.5 first:pt-0">
                                    <dt class="font-bold text-slate-500">{{ $row['label'] }}</dt>
                                    <dd class="max-w-[58%] text-right font-black text-brand-navy">{{ $row['value'] }}</dd>
                                </div>
                            @endforeach
                        </dl>

                        @if ($tags->isNotEmpty())
                            <div class="mt-5 border-t border-slate-100 pt-5">
                                <p class="text-[10px] font-black uppercase tracking-[0.20em] text-slate-400">
                                    Kata Kunci
                                </p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($tags as $tag)
                                        <span class="rounded-full bg-brand-teal-soft px-3 py-1.5 text-xs font-black text-brand-navy">
                                            #{{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </section>

                    <section
                        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
                        data-publication-citation
                    >
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-brand-teal">
                            Referensi Akademik
                        </p>
                        <h2 class="mt-2 text-xl font-black tracking-tight text-brand-navy">
                            Cara Mengutip
                        </h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Pilih format sitasi yang sesuai, lalu salin untuk digunakan dalam tulisan Anda.
                        </p>

                        <label
                            for="citation-style-{{ $publication->getKey() }}"
                            class="mt-5 block text-xs font-black uppercase tracking-[0.16em] text-slate-500"
                        >
                            Format Sitasi
                        </label>
                        <select
                            id="citation-style-{{ $publication->getKey() }}"
                            data-citation-style
                            class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-extrabold text-brand-navy shadow-sm outline-none transition focus:border-brand-amber focus:ring-4 focus:ring-brand-amber/15"
                        >
                            <option value="apa">APA</option>
                            <option value="chicago">Chicago</option>
                            <option value="mla">MLA</option>
                            <option value="ieee">IEEE</option>
                            <option value="harvard">Harvard</option>
                        </select>

                        <div
                            data-citation-text
                            aria-live="polite"
                            class="mt-4 break-words rounded-2xl border border-brand-amber/25 bg-[#f8f5ee] p-4 text-sm font-semibold leading-7 text-slate-700"
                        >
                            {{ $citationText }}
                        </div>

                        <script type="application/json" data-citation-formats>{!! json_encode($citationFormats, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

                        <button
                            type="button"
                            data-copy-citation
                            class="mt-4 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-brand-amber px-4 py-2.5 text-sm font-black text-brand-ink shadow-sm transition hover:-translate-y-0.5 hover:bg-brand-amber-dark focus:outline-none focus:ring-4 focus:ring-brand-amber/25"
                        >
                            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path>
                            </svg>
                            <span data-copy-citation-label>Salin Sitasi</span>
                        </button>

                        <div class="mt-6 border-t border-slate-100 pt-5">
                            <x-share-buttons
                                :title="$publication->share_preview_title"
                                :url="$publication->public_url"
                                :description="$publication->share_preview_description"
                                label="Bagikan Publikasi"
                            />
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </section>

    <x-shared.cta-collaboration
        eyebrow="Kolaborasi Riset"
        title="Kembangkan riset dan literasi hukum tanpa batas bersama Edulaw Project."
        body="Kami membuka ruang kolaborasi untuk riset, publikasi, diskusi, dan penguatan kebijakan hukum yang berdampak bagi masyarakat."
        :primary-url="$collaborationUrl"
        primary-label="Ajukan Kolaborasi"
        :secondary-url="$indexUrl"
        secondary-label="Lihat Publikasi Lainnya"
    />
</main>
@endsection

@once
    @push('scripts')
        <script>
            (() => {
                if (window.__edulawPublicationCitationReady) {
                    return;
                }

                window.__edulawPublicationCitationReady = true;

                const copyText = async (text) => {
                    if (navigator.clipboard?.writeText && window.isSecureContext) {
                        await navigator.clipboard.writeText(text);

                        return;
                    }

                    const input = document.createElement('textarea');
                    input.value = text;
                    input.setAttribute('readonly', '');
                    input.style.position = 'fixed';
                    input.style.opacity = '0';
                    document.body.appendChild(input);
                    input.select();

                    const copied = document.execCommand('copy');
                    input.remove();

                    if (! copied) {
                        throw new Error('Clipboard tidak tersedia.');
                    }
                };

                const citationData = (root) => {
                    try {
                        return JSON.parse(root.querySelector('[data-citation-formats]')?.textContent || '{}');
                    } catch (error) {
                        return {};
                    }
                };

                document.addEventListener('change', (event) => {
                    const select = event.target.closest('[data-citation-style]');

                    if (! select) {
                        return;
                    }

                    const root = select.closest('[data-publication-citation]');
                    const output = root?.querySelector('[data-citation-text]');
                    const citation = root ? citationData(root)[select.value] : null;

                    if (output && citation) {
                        output.textContent = citation;
                    }
                });

                document.addEventListener('click', async (event) => {
                    const button = event.target.closest('[data-copy-citation]');

                    if (! button) {
                        return;
                    }

                    const root = button.closest('[data-publication-citation]');
                    const citation = root?.querySelector('[data-citation-text]')?.textContent?.trim();
                    const label = button.querySelector('[data-copy-citation-label]');

                    if (! citation || ! label) {
                        return;
                    }

                    try {
                        await copyText(citation);
                    } catch (error) {
                        window.prompt('Salin sitasi berikut:', citation);

                        return;
                    }

                    label.textContent = 'Sitasi Disalin';
                    window.clearTimeout(button.__citationResetTimer);
                    button.__citationResetTimer = window.setTimeout(() => {
                        label.textContent = 'Salin Sitasi';
                    }, 2000);
                });
            })();
        </script>
    @endpush
@endonce
