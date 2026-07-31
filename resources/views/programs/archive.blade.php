@extends('layouts.app')

@section('title', 'Arsip Program Hukum | Edulaw Project')
@section('meta_description', 'Telusuri dokumentasi program, diskusi, pelatihan, dan kegiatan hukum yang telah diselenggarakan Edulaw Project untuk publik.')

@push('head')
    @php
        $archiveListSchemaItems = collect($archivePrograms->items())
            ->map(fn ($item): array => [
                'name' => $item->display_title,
                'url' => route('programs.show', $item->slug),
                'image' => $item->hero_image_url,
            ])
            ->all();
    @endphp
    @if ($archiveListSchemaItems !== [])
        <x-structured-data :data="\App\Support\StructuredData::itemList($archiveListSchemaItems, 'Arsip Program Hukum')" />
    @endif
@endpush

@section('content')
@php
    $programUrl = \Illuminate\Support\Facades\Route::has('programs.index') ? route('programs.index') : url('/program');
    $archiveUrl = \Illuminate\Support\Facades\Route::has('programs.archive') ? route('programs.archive') : url('/program/archive');
    $items = $archivePrograms instanceof \Illuminate\Pagination\AbstractPaginator ? $archivePrograms->getCollection() : collect($archivePrograms);
    $heroImage = 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=1800&q=85';
@endphp

<main class="bg-[#F8FAFC] text-brand-ink">
    <x-shared.page-header
        title="Arsip Program Edulaw"
        eyebrow="Program Arsip"
        description="Dokumentasi seluruh program yang telah selesai."
        :background-image="$heroImage"
        background-alt="Arsip program Edulaw Project"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Program', 'url' => '/program'],
            ['label' => 'Arsip Program'],
        ]"
    />

    <section class="bg-[#F8FAFC] pt-8 sm:pt-10">
        <div class="mx-auto max-w-[1320px] px-5 sm:px-6 lg:px-8">
            <div class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_18px_45px_rgba(15,23,42,0.06)] sm:p-5">
                <form action="{{ $archiveUrl }}" method="GET" class="grid gap-3 lg:grid-cols-[minmax(260px,1fr)_220px_180px]">
                    <label class="relative block">
                        <span class="sr-only">Search Arsip</span>
                        <input
                            type="search"
                            name="archive_q"
                            value="{{ $archiveSearch }}"
                            placeholder="Search Arsip"
                            oninput="clearTimeout(this._programArchiveTimer); this._programArchiveTimer = setTimeout(() => this.form.requestSubmit(), 450)"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-4 pr-10 text-sm font-semibold text-brand-ink shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-navy focus:ring-4 focus:ring-brand-navy/10"
                        >
                        <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </label>

                    <label>
                        <span class="sr-only">Dropdown kategori</span>
                        <select
                            name="archive_category"
                            onchange="this.form.requestSubmit()"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-black text-brand-navy shadow-sm outline-none transition focus:border-brand-navy focus:ring-4 focus:ring-brand-navy/10"
                        >
                            <option value="">Semua Kategori</option>
                            @foreach ($programCategories as $category)
                                <option value="{{ $category->slug }}" @selected($archiveCategory === $category->slug)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span class="sr-only">Dropdown tahun</span>
                        <select
                            name="archive_year"
                            onchange="this.form.requestSubmit()"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-black text-brand-navy shadow-sm outline-none transition focus:border-brand-navy focus:ring-4 focus:ring-brand-navy/10"
                        >
                            <option value="">Semua Tahun</option>
                            @foreach ($archiveYears as $year)
                                <option value="{{ $year }}" @selected((string) $archiveYear === (string) $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </label>
                </form>
            </div>
        </div>
    </section>

    <section class="py-10 sm:py-12 lg:py-14">
        <div class="mx-auto max-w-[1320px] px-5 sm:px-6 lg:px-8">
            @if ($items->isNotEmpty())
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($items as $program)
                        <x-program.archive-card :program="$program" />
                    @endforeach
                </div>
            @else
                <div class="rounded-[24px] border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-brand-navy">Arsip belum ditemukan</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Coba gunakan kata kunci, kategori, atau tahun yang berbeda.</p>
                </div>
            @endif

            @if ($archivePrograms instanceof \Illuminate\Pagination\AbstractPaginator && $archivePrograms->hasPages())
                <nav class="mt-8 flex flex-wrap items-center justify-center gap-2" aria-label="Pagination Arsip Program">
                    @if ($archivePrograms->onFirstPage())
                        <span class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-400">Previous</span>
                    @else
                        <a href="{{ $archivePrograms->previousPageUrl() }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-brand-navy transition hover:border-brand-navy hover:bg-brand-navy hover:text-white">Previous</a>
                    @endif

                    @foreach ($archivePrograms->getUrlRange(1, $archivePrograms->lastPage()) as $page => $url)
                        @if ($page === $archivePrograms->currentPage())
                            <span class="grid h-10 min-w-10 place-items-center rounded-xl bg-brand-navy px-3 text-sm font-black text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="grid h-10 min-w-10 place-items-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-black text-brand-navy transition hover:border-brand-navy hover:bg-brand-navy hover:text-white">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if ($archivePrograms->hasMorePages())
                        <a href="{{ $archivePrograms->nextPageUrl() }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-brand-navy transition hover:border-brand-navy hover:bg-brand-navy hover:text-white">Next</a>
                    @else
                        <span class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-400">Next</span>
                    @endif
                </nav>
            @endif
        </div>
    </section>
</main>
@endsection
