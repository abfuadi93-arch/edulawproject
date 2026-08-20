@props(['publications' => collect()])

@php
    $publicationCollection = collect($publications)->take(4)->values();
    $coverPalettes = [
        ['from' => '#001b36', 'to' => '#28557a', 'overlay' => 'rgba(0, 27, 54, .84)'],
        ['from' => '#164b3a', 'to' => '#397563', 'overlay' => 'rgba(22, 75, 58, .84)'],
        ['from' => '#765b32', 'to' => '#bd9660', 'overlay' => 'rgba(102, 76, 38, .78)'],
        ['from' => '#102f56', 'to' => '#486d91', 'overlay' => 'rgba(16, 47, 86, .85)'],
    ];
@endphp

<section id="riset-publikasi" class="scroll-mt-20 bg-white py-12 lg:py-14" aria-labelledby="home-publications-title">
    <div class="section-shell">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-3xl">
                <h2 id="home-publications-title" class="home-section-title mt-0">Riset &amp; Publikasi Pilihan</h2>
                <p class="home-section-description mt-4">Repositori kajian, policy brief, naskah akademik, dan buku digital.</p>
            </div>

            <a href="{{ route('publications.index') }}" class="inline-flex w-fit shrink-0 items-center gap-3 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-extrabold text-[#061b3a] shadow-sm transition hover:border-[#d9a24c] hover:bg-[#fff8e8]">
                Lihat Semua Publikasi
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M5 12h14" />
                    <path d="m13 6 6 6-6 6" />
                </svg>
            </a>
        </div>

        @if ($publicationCollection->isNotEmpty())
            <div class="mt-9 grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($publicationCollection as $index => $publication)
                    @php
                        $palette = $coverPalettes[$index % count($coverPalettes)];
                        $typeName = $publication->type?->name ?? 'Publikasi';
                        $documentUrl = $publication->download_url;
                    @endphp

                    <article data-home-publication class="group relative mx-auto w-full max-w-sm pt-0">
                        <a href="{{ route('publications.show', $publication->slug) }}" class="relative z-10 mx-auto flex aspect-[1/1.34] w-[82%] max-w-64 flex-col justify-between overflow-hidden rounded-md p-5 text-white shadow-xl shadow-slate-900/15 transition duration-300 group-hover:-translate-y-1 group-hover:shadow-2xl">
                            @if ($publication->cover_image_url)
                                <img src="{{ $publication->cover_image_url }}" alt="" class="absolute inset-0 size-full object-cover" loading="lazy" decoding="async" onerror="this.remove()">
                            @endif
                            <span class="absolute inset-0" style="background: linear-gradient(155deg, {{ $palette['overlay'] }}, {{ $palette['from'] }} 70%, {{ $palette['to'] }});"></span>
                            <span class="absolute -right-10 -top-10 size-36 rounded-full border border-white/10"></span>
                            <span class="absolute -bottom-16 -left-12 size-44 rounded-full border border-white/10"></span>

                            <div class="relative">
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#efc66b]">{{ $typeName }}</p>
                                <h3 class="mt-7 line-clamp-5 text-lg font-extrabold leading-snug tracking-[-0.012em] text-white">{{ $publication->title }}</h3>
                            </div>

                            <svg class="relative h-20 w-20 self-end text-white/15" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                                <path d="M16 4v23M9 8h14M12 27h8M7 12l-4 7h8l-4-7Zm18 0-4 7h8l-4-7Z" />
                                <path d="M7 12h18" />
                            </svg>
                        </a>

                        <div class="-mt-4 rounded-xl border border-slate-200/80 bg-white px-5 pb-5 pt-8 shadow-[0_14px_34px_-28px_rgba(15,23,42,.7)] transition group-hover:border-[#d9a24c]/50 group-hover:shadow-lg sm:-mt-10 sm:pt-14">
                            <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-[#b77928]">{{ $typeName }}</p>

                            <div class="mt-4 flex min-h-6 items-center justify-between gap-3">
                                <p class="line-clamp-1 text-sm text-slate-500">{{ $publication->publication_date_display }}</p>
                                @if ($documentUrl)
                                    <a href="{{ $documentUrl }}" target="_blank" rel="noopener noreferrer" class="grid size-8 shrink-0 place-items-center rounded-full text-[#061b3a] transition hover:bg-[#fff3cf]" aria-label="Buka dokumen {{ $publication->title }}">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M12 3v12" />
                                            <path d="m7 10 5 5 5-5" />
                                            <path d="M5 21h14" />
                                        </svg>
                                    </a>
                                @endif
                            </div>

                            <a href="{{ route('publications.show', $publication->slug) }}" class="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-md bg-[#001b36] px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-[#0b4166]">
                                Baca Ringkasan
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="mt-9 overflow-hidden rounded-2xl border border-slate-200 bg-[#f8fafc] p-7 text-center sm:p-10">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-[#b77928]">Repositori Edulaw</p>
                <h3 class="mx-auto mt-3 max-w-xl text-2xl font-extrabold leading-tight tracking-[-0.015em] text-[#061b3a]">Publikasi sedang disiapkan.</h3>
                <p class="mx-auto mt-3 max-w-2xl text-[15px] leading-7 text-slate-600">Kajian, policy brief, naskah akademik, dan buku digital akan tampil setelah diterbitkan melalui sistem editorial.</p>
                <a href="{{ route('publications.index') }}" class="mt-6 inline-flex min-h-10 items-center justify-center rounded-md bg-[#001b36] px-5 py-2.5 text-sm font-extrabold text-white transition hover:bg-[#0b4166]">Buka Repositori →</a>
            </div>
        @endif
    </div>
</section>
