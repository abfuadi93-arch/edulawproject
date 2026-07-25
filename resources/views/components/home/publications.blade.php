@props([
    'publications' => collect(),
])

@php
    $hasPublicationIndex = \Illuminate\Support\Facades\Route::has('publications.index');
    $hasPublicationShow = \Illuminate\Support\Facades\Route::has('publications.show');

    $publicationTypes = collect($publications)
        ->map(fn ($publication) => $publication->type)
        ->filter()
        ->unique('slug')
        ->map(fn ($type) => [
            'label' => $type->name,
            'slug' => $type->slug,
        ])
        ->prepend([
            'label' => 'Semua',
            'slug' => null,
        ])
        ->values();

    $coverPalettes = [
        [
            'name' => 'Jelly Mint',
            'bg' => 'bg-[#C7EFE4]',
            'soft' => 'bg-[#DDF7F0]',
            'accent' => 'bg-[#16A085]',
            'accentText' => 'text-[#0D6F60]',
            'text' => 'text-[#123B36]',
            'muted' => 'text-[#245D55]',
            'border' => 'border-[#8DD8C9]',
            'ring' => 'ring-[#8DD8C9]/60',
        ],
        [
            'name' => 'Cloud Dancer',
            'bg' => 'bg-[#F4F1EA]',
            'soft' => 'bg-[#FBF8F1]',
            'accent' => 'bg-[#C9A45C]',
            'accentText' => 'text-[#8A6B2F]',
            'text' => 'text-[#1E293B]',
            'muted' => 'text-[#64748B]',
            'border' => 'border-[#DED7C8]',
            'ring' => 'ring-[#DED7C8]/70',
        ],
        [
            'name' => 'Blue Aura',
            'bg' => 'bg-[#D8E6F3]',
            'soft' => 'bg-[#EDF5FB]',
            'accent' => 'bg-[#315D7C]',
            'accentText' => 'text-[#315D7C]',
            'text' => 'text-[#102A43]',
            'muted' => 'text-[#49667D]',
            'border' => 'border-[#AFC8DC]',
            'ring' => 'ring-[#AFC8DC]/70',
        ],
        [
            'name' => 'Digital Lavender',
            'bg' => 'bg-[#E7DDF7]',
            'soft' => 'bg-[#F4EEFF]',
            'accent' => 'bg-[#7B61A8]',
            'accentText' => 'text-[#674D93]',
            'text' => 'text-[#2D2142]',
            'muted' => 'text-[#63517D]',
            'border' => 'border-[#C9B6EA]',
            'ring' => 'ring-[#C9B6EA]/70',
        ],
    ];

    $authorInitials = function (string $name): string {
        return \Illuminate\Support\Str::of($name)
            ->explode(' ')
            ->filter()
            ->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))
            ->take(2)
            ->implode('') ?: 'E';
    };
@endphp

<section class="bg-[#F4F7F9] py-8 lg:py-10">
    <div class="section-shell">
        {{-- Header --}}
        <div>
            <div class="flex items-center justify-between gap-4">
                <p class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.18em] text-brand-amber shadow-sm ring-1 ring-slate-200">
                    <span class="h-2 w-2 rounded-full bg-brand-amber"></span>
                    Publikasi Edulaw
                </p>

                @if ($hasPublicationIndex)
                    <a
                        href="{{ route('publications.index') }}"
                        class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-ink transition hover:text-brand-navy"
                    >
                        Lihat Semua Publikasi
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                @endif
            </div>

            <div class="mt-1.5 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="min-w-0">
                    <h2 class="text-2xl font-extrabold tracking-tight text-brand-ink sm:text-3xl lg:text-[2rem]">
                        Riset &amp; Publikasi Pilihan
                    </h2>

                    <p class="mt-2 max-w-none text-sm leading-6 text-slate-600 lg:whitespace-nowrap">
                        Repositori kajian, policy brief, naskah akademik, dan buku digital.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                    @if ($hasPublicationIndex)
                    @foreach ($publicationTypes as $type)
                        <a
                            href="{{ $type['slug'] ? route('publications.index', ['type' => $type['slug']]) : route('publications.index') }}"
                            class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-extrabold text-slate-600 shadow-sm transition hover:border-brand-black hover:bg-brand-black hover:text-white"
                        >
                            {{ $type['label'] }}
                        </a>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-5 grid auto-rows-fr gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($hasPublicationShow ? $publications : collect() as $publication)
                @php
                    $typeName = $publication->type?->name;
                    $palette = $coverPalettes[$loop->index % count($coverPalettes)];
                    $publishedAt = optional($publication->published_at)->translatedFormat('M Y');
                    $downloadUrl = $publication->download_url;

                    $authorName = $publication->display_author
                        ?? $publication->display_authors
                        ?? $publication->authors?->pluck('name')->filter()->join(', ')
                        ?? null;
                    $primaryAuthor = $publication->relationLoaded('authors')
                        ? $publication->authors->sortBy(fn ($author) => $author->pivot?->author_order ?? 999)->first()
                        : null;
                @endphp

                <article class="group h-full">
                    <div class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-sm transition duration-300 group-hover:-translate-y-0.5 group-hover:shadow-xl group-hover:shadow-brand-ink/10">
                        <a href="{{ route('publications.show', $publication->slug) }}" class="flex flex-1 flex-col">
                            {{-- Cover A4 --}}
                            <div class="relative overflow-hidden rounded-lg bg-slate-100 shadow-sm">
                                <div class="relative mx-auto aspect-210/297 w-full overflow-hidden border {{ $palette['bg'] }} {{ $palette['border'] }}">
                                    @if ($publication->cover_image_url)
                                        <img
                                            src="{{ $publication->cover_image_url }}"
                                            alt="Sampul {{ $publication->title }}"
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.025]"
                                        >

                                        <div class="absolute inset-0 bg-brand-navy/5"></div>

                                        @if ($typeName)
                                        <div class="absolute left-3 top-3">
                                            <span class="inline-flex items-center rounded-md bg-white/85 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.13em] text-brand-ink shadow-sm backdrop-blur">
                                                {{ $typeName }}
                                            </span>
                                        </div>
                                        @endif

                                        @if ($authorName)
                                            <div class="absolute bottom-3 left-3 right-3">
                                                <p class="line-clamp-2 rounded-md bg-white/85 px-2.5 py-1.5 text-[10px] font-bold leading-4 text-brand-ink shadow-sm backdrop-blur">
                                                    {{ $authorName }}
                                                </p>
                                            </div>
                                        @endif
                                    @else
                                        <div class="relative flex h-full w-full flex-col p-4">
                                            {{-- Decorative layer --}}
                                            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                                                <div class="absolute -right-12 -top-12 h-36 w-36 rounded-full border border-white/70"></div>
                                                <div class="absolute right-6 top-20 h-12 w-12 rounded-full border border-white/70"></div>
                                                <div class="absolute -bottom-10 -left-10 h-32 w-32 rounded-full border border-white/80"></div>
                                                <div class="absolute bottom-16 left-5 h-10 w-10 rounded-full border border-white/60"></div>

                                                <svg class="absolute bottom-6 right-5 h-20 w-20 {{ $palette['muted'] }} opacity-25" viewBox="0 0 64 64" fill="none" aria-hidden="true">
                                                    <path d="M32 8v48M16 18h32M20 18 12 38h16L20 18Zm24 0-8 20h16l-8-20Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </div>

                                            <div class="relative flex h-full flex-col">
                                                <div class="flex items-start justify-between gap-3">
                                                    <p class="text-[9px] font-black uppercase tracking-[0.18em] {{ $palette['muted'] }}">
                                                        Edulaw Project
                                                    </p>

                                                    <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $palette['accent'] }}"></span>
                                                </div>

                                                @if ($typeName)
                                                <div class="mt-7">
                                                    <span class="inline-flex items-center rounded-md bg-white/60 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.13em] {{ $palette['text'] }} shadow-sm ring-1 ring-white/70">
                                                        {{ $typeName }}
                                                    </span>
                                                </div>
                                                @endif

                                                <h3 class="mt-4 line-clamp-6 text-[1.05rem] font-black leading-tight tracking-tight {{ $palette['text'] }}">
                                                    {{ $publication->title }}
                                                </h3>

                                                @if ($authorName)
                                                    <p class="mt-3 line-clamp-2 text-[10px] font-bold leading-4 {{ $palette['muted'] }}">
                                                        {{ $authorName }}
                                                    </p>
                                                @endif

                                                <div class="mt-auto">
                                                    <span class="block h-1 w-12 rounded-full {{ $palette['accent'] }}"></span>

                                                    @if ($publishedAt)
                                                        <p class="mt-2 text-[11px] font-bold {{ $palette['muted'] }}">
                                                            {{ $publishedAt }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Info bawah tanpa repetisi judul --}}
                            <div class="flex flex-1 flex-col pt-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        @if ($typeName)
                                            <p class="text-[11px] font-black uppercase tracking-[0.14em] text-brand-navy">
                                                {{ $typeName }}
                                            </p>
                                        @endif

                                        @if ($publication->published_at || ! empty($publication->page_count) || $authorName)
                                            <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold text-slate-500">
                                            @if ($publication->published_at)
                                                <span>{{ $publication->published_at->translatedFormat('M Y') }}</span>
                                            @endif

                                            @if (! empty($publication->page_count))
                                                @if ($publication->published_at)
                                                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                                @endif
                                                <span>{{ $publication->page_count }} hlm</span>
                                            @endif

                                            @if ($authorName)
                                                @if ($publication->published_at || ! empty($publication->page_count))
                                                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                                @endif
                                                <span class="inline-flex max-w-full items-center gap-1.5">
                                                    @if ($primaryAuthor?->photo_url)
                                                        <img
                                                            src="{{ $primaryAuthor->photo_url }}"
                                                            alt="Foto profil {{ $primaryAuthor->name }}"
                                                            class="h-5 w-5 shrink-0 rounded-full object-cover ring-1 ring-slate-200"
                                                            loading="lazy"
                                                        >
                                                    @else
                                                        <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full bg-brand-navy text-[8px] font-black text-white">
                                                            {{ $authorInitials($primaryAuthor?->name ?: $authorName) }}
                                                        </span>
                                                    @endif

                                                    <span class="line-clamp-1 min-w-0">{{ $authorName }}</span>
                                                </span>
                                            @endif
                                        </div>
                                        @endif
                                    </div>

                                    <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-brand-navy ring-1 ring-slate-200 transition group-hover:bg-brand-navy group-hover:text-white">
                                        <svg class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </a>

                        {{-- Actions --}}
                        <div class="mt-3 grid {{ $downloadUrl ? 'grid-cols-[1fr_2.75rem]' : 'grid-cols-1' }} gap-2">
                            <a
                                href="{{ route('publications.show', $publication->slug) }}"
                                class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand-black px-3 py-2 text-xs font-bold text-white transition hover:bg-brand-navy"
                            >
                                Baca Ringkasan
                            </a>

                            @if ($downloadUrl)
                                <a
                                    href="{{ $downloadUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-slate-200 bg-white text-brand-ink transition hover:border-brand-amber hover:bg-brand-amber-soft hover:text-brand-black"
                                    aria-label="Unduh atau buka publikasi"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M12 4v11m0 0 4-4m-4 4-4-4M5 20h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-5 text-center shadow-sm">
                    <p class="text-sm leading-6 text-slate-600">
                        Belum ada publikasi yang tersedia. Nantikan riset dan publikasi terbaru dari Edulaw Project.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</section>
