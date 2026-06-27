@props([
    'publications' => collect(),
])

@php
    $publicationTypes = [
        'Semua',
        'Jurnal',
        'Policy Brief',
        'Kajian Hukum',
        'Working Paper',
        'Buku Digital',
    ];

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

                <a
                    href="{{ route('publications.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-ink transition hover:text-brand-navy"
                >
                    Lihat Semua Publikasi
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
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
                    @foreach ($publicationTypes as $type)
                        <a
                            href="{{ route('publications.index', ['type' => \Illuminate\Support\Str::slug($type)]) }}"
                            class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-extrabold text-slate-600 shadow-sm transition hover:border-brand-black hover:bg-brand-black hover:text-white"
                        >
                            {{ $type }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-5 grid auto-rows-fr gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($publications as $publication)
                @php
                    $typeName = $publication->type?->name ?? 'Publikasi';
                    $palette = $coverPalettes[$loop->index % count($coverPalettes)];
                    $publishedAt = optional($publication->published_at)->translatedFormat('M Y') ?: 'Edulaw Project';

                    $authorName = $publication->display_author
                        ?? $publication->display_authors
                        ?? $publication->authors?->pluck('name')->filter()->join(', ')
                        ?? null;
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

                                        <div class="absolute left-3 top-3">
                                            <span class="inline-flex items-center rounded-md bg-white/85 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.13em] text-brand-ink shadow-sm backdrop-blur">
                                                {{ $typeName }}
                                            </span>
                                        </div>

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

                                                <div class="mt-7">
                                                    <span class="inline-flex items-center rounded-md bg-white/60 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.13em] {{ $palette['text'] }} shadow-sm ring-1 ring-white/70">
                                                        {{ $typeName }}
                                                    </span>
                                                </div>

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

                                                    <p class="mt-2 text-[11px] font-bold {{ $palette['muted'] }}">
                                                        {{ $publishedAt }}
                                                    </p>
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
                                        <p class="text-[11px] font-black uppercase tracking-[0.14em] text-brand-navy">
                                            {{ $typeName }}
                                        </p>

                                        <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold text-slate-500">
                                            <span>{{ optional($publication->published_at)->translatedFormat('M Y') ?: '-' }}</span>

                                            @if (! empty($publication->page_count))
                                                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                                <span>{{ $publication->page_count }} hlm</span>
                                            @endif
                                        </div>
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
                        <div class="mt-3 grid grid-cols-[1fr_2.75rem] gap-2">
                            <a
                                href="{{ route('publications.show', $publication->slug) }}"
                                class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand-black px-3 py-2 text-xs font-bold text-white transition hover:bg-brand-navy"
                            >
                                Baca Ringkasan
                            </a>

                            <a
                                href="{{ $publication->download_url ?: route('publications.show', $publication->slug) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-slate-200 bg-white text-brand-ink transition hover:border-brand-amber hover:bg-brand-amber-soft hover:text-brand-black"
                                aria-label="Unduh atau buka publikasi"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 4v11m0 0 4-4m-4 4-4-4M5 20h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full flex min-h-55 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                    <div>
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-brand-navy text-brand-amber">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M7 3h7l4 4v14H7V3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <path d="M14 3v5h5M10 13h6M10 17h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </div>

                        <h3 class="mt-4 text-lg font-black text-brand-ink">
                            Belum ada publikasi dipublikasikan.
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Publikasi yang sudah berstatus published akan tampil di sini.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>
