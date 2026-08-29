@props(['publications' => collect()])

@php
    $publicationCollection = collect($publications)->take(4)->values();
    $coverPalettes = [
        ['from' => '#001b36', 'to' => '#28557a', 'overlay' => 'rgba(0, 27, 54, .84)'],
        ['from' => '#155e53', 'to' => '#3b8275', 'overlay' => 'rgba(21, 94, 83, .84)'],
        ['from' => '#765b32', 'to' => '#bd9660', 'overlay' => 'rgba(102, 76, 38, .80)'],
        ['from' => '#5a1f35', 'to' => '#b4535f', 'overlay' => 'rgba(90, 31, 53, .84)'],
    ];
@endphp

<section id="riset-publikasi" class="home-surface-teal scroll-mt-20 py-7 sm:py-8 lg:py-9" aria-labelledby="home-publications-title">
    <div class="section-shell">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-3xl">
                <h2 id="home-publications-title" class="home-section-title mt-0">Riset &amp; Publikasi Pilihan</h2>
                <p class="home-section-description mt-4">Repositori kajian, policy brief, naskah akademik, dan buku digital.</p>
            </div>

            <a href="{{ route('publications.index') }}" class="inline-flex w-fit shrink-0 items-center gap-3 rounded-full border border-[#cddfd9] bg-white/90 px-5 py-2.5 text-sm font-extrabold text-[#061b3a] shadow-sm transition hover:border-[#d9a24c] hover:bg-[#fff8e8]">
                Lihat Semua Publikasi
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M5 12h14" />
                    <path d="m13 6 6 6-6 6" />
                </svg>
            </a>
        </div>

        @if ($publicationCollection->isNotEmpty())
            <div class="mt-7 grid gap-x-6 gap-y-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($publicationCollection as $index => $publication)
                    @php
                        $palette = $coverPalettes[$index % count($coverPalettes)];
                        $typeName = $publication->type?->name ?? 'Publikasi';
                        $publicationDate = $publication->publication_date_display;
                    @endphp

                    <article data-home-publication class="group relative mx-auto w-full max-w-sm">
                        <a href="{{ route('publications.show', $publication->slug) }}" aria-label="Lihat publikasi: {{ $publication->title }}" class="block rounded-xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b77928]">
                            <div class="relative z-10 mx-auto flex aspect-[1/1.34] w-[82%] max-w-64 flex-col justify-between overflow-hidden rounded-md p-5 text-white shadow-xl shadow-slate-900/15 transition duration-300 group-hover:-translate-y-1 group-hover:shadow-2xl">
                                @if ($publication->cover_image_url)
                                    <x-responsive-image :src="$publication->cover_image_url" alt="Sampul {{ $publication->title }}" :widths="[240, 320, 480]" sizes="(min-width: 1024px) 235px, (min-width: 640px) 41vw, 82vw" width="480" height="640" class="absolute inset-0 size-full object-cover" onerror="this.remove()" />
                                @endif
                                <span class="absolute inset-0" style="background: linear-gradient(155deg, {{ $palette['overlay'] }}, {{ $palette['from'] }} 70%, {{ $palette['to'] }});"></span>
                                <span class="absolute -right-10 -top-10 size-36 rounded-full border border-white/10"></span>
                                <span class="absolute -bottom-16 -left-12 size-44 rounded-full border border-white/10"></span>

                                <div class="relative">
                                    <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#efc66b]">{{ $typeName }}</p>
                                    <p class="mt-7 line-clamp-5 text-lg font-extrabold leading-snug tracking-[-0.012em] text-white">{{ $publication->title }}</p>
                                </div>

                                <svg class="relative h-20 w-20 self-end text-white/15" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                                    <path d="M16 4v23M9 8h14M12 27h8M7 12l-4 7h8l-4-7Zm18 0-4 7h8l-4-7Z" />
                                    <path d="M7 12h18" />
                                </svg>
                            </div>

                            <div class="-mt-4 rounded-xl border border-[#d9e4e0] bg-white px-5 pb-5 pt-8 shadow-[0_14px_34px_-28px_rgba(15,23,42,.7)] transition group-hover:border-[#d9a24c]/50 sm:-mt-10 sm:pt-14">
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.1em] text-[#b77928]">{{ $typeName }}</p>
                                <h3 class="mt-2 line-clamp-3 min-h-[4.5rem] text-base font-extrabold leading-6 text-[#061b3a] transition group-hover:text-[#174f7d]">{{ $publication->title }}</h3>
                                <div class="mt-4 min-h-6">
                                    @if (filled($publicationDate) && $publicationDate !== '-')
                                        <p class="line-clamp-1 text-sm text-slate-500">{{ $publicationDate }}</p>
                                    @endif
                                </div>
                                <span class="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-md bg-[#001b36] px-4 py-2.5 text-sm font-extrabold text-white transition group-hover:bg-[#155e68]">
                                    Lihat Publikasi →
                                </span>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="mt-9 overflow-hidden rounded-2xl border border-[#d9e4e0] bg-white/80 p-7 text-center sm:p-10">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-[#b77928]">Repositori Edulaw</p>
                <h3 class="mx-auto mt-3 max-w-xl text-2xl font-extrabold leading-tight tracking-[-0.015em] text-[#061b3a]">Publikasi sedang disiapkan.</h3>
                <p class="mx-auto mt-3 max-w-2xl text-[15px] leading-7 text-slate-600">Kajian, policy brief, naskah akademik, dan buku digital akan tampil setelah diterbitkan melalui sistem editorial.</p>
                <a href="{{ route('publications.index') }}" class="mt-6 inline-flex min-h-10 items-center justify-center rounded-md bg-[#001b36] px-5 py-2.5 text-sm font-extrabold text-white transition hover:bg-[#155e68]">Buka Repositori →</a>
            </div>
        @endif
    </div>
</section>
