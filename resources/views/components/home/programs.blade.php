@props(['programs' => collect()])

@php
    $programCollection = collect($programs)->take(3)->values();
    $usesThreeColumns = $programCollection->count() >= 3;
@endphp

<section id="program-edulaw" class="scroll-mt-20 bg-[#fbf9f3] py-6 sm:py-10" aria-labelledby="home-programs-title">
    <div class="section-shell">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="home-section-eyebrow">Program Edulaw</p>
                <h2 id="home-programs-title" class="home-section-title">Belajar Hukum secara Kontekstual</h2>
            </div>
            <a href="{{ route('programs.index') }}" class="home-section-link hidden sm:inline-flex">Semua Program →</a>
        </div>

        <div @class(['mt-8 grid gap-5 md:grid-cols-2', 'xl:grid-cols-3' => $usesThreeColumns])>
            @forelse ($programCollection as $program)
                @php
                    $image = $program->hero_image_url ?: $program->image_url;
                    $eventDate = $program->event_date ?? $program->starts_at;
                    $format = $program->display_format ?: $program->location;
                    $statusLabel = match ($program->status) {
                        'ongoing' => 'Berlangsung',
                        'archived' => 'Arsip',
                        default => 'Akan Datang',
                    };
                @endphp
                <article data-home-program class="group overflow-hidden rounded-xl border border-slate-200 bg-white transition duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-sm">
                    <a href="{{ route('programs.show', $program->slug) }}" class="grid h-full grid-cols-[112px_minmax(0,1fr)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber sm:block">
                        <div class="relative min-h-32 overflow-hidden sm:aspect-video sm:min-h-0">
                            <x-home.media-fallback kind="program" :label="$program->categoryRelation?->name ?? 'Program Edulaw'" />
                            @if ($image)
                                <x-responsive-image :src="$image" alt="Poster {{ $program->display_title }}" :widths="[320, 480, 640, 960]" sizes="(min-width: 1280px) 390px, (min-width: 768px) 50vw, 100vw" width="640" height="360" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]" onerror="this.remove()" />
                            @endif
                            <div class="absolute inset-x-3 top-3 flex items-center justify-between gap-2">
                                <span class="max-w-40 truncate rounded bg-[#17375f]/90 px-2.5 py-1.5 text-xs font-bold uppercase tracking-wide text-white">{{ $program->categoryRelation?->name ?? 'Program' }}</span>
                                <span class="hidden rounded-full bg-white/95 px-2.5 py-1.5 text-xs font-bold text-[#102f56] sm:inline-flex">{{ $statusLabel }}</span>
                            </div>
                        </div>
                        <div class="flex min-w-0 flex-col p-4 sm:block sm:p-5">
                            <h3 class="line-clamp-3 text-base font-bold leading-[1.35] text-brand-navy sm:line-clamp-2 sm:text-lg">{{ $program->display_title }}</h3>
                            <div class="home-card-meta flex flex-wrap gap-x-3 gap-y-1">
                                @if ($eventDate)<span>{{ $eventDate->translatedFormat('d M Y') }}</span>@endif
                                @if ($eventDate && $format)<span>·</span>@endif
                                @if ($format)<span>{{ $format }}</span>@endif
                            </div>
                            <span class="home-card-action mt-auto pt-3 sm:mt-5 sm:pt-0">Lihat Detail →</span>
                        </div>
                    </a>
                </article>
            @empty
                <div class="home-empty-state col-span-full"><p class="text-sm leading-6 text-slate-600">Program terbaru sedang disiapkan.</p></div>
            @endforelse
        </div>

        <a href="{{ route('programs.index') }}" class="home-section-link mt-6 sm:hidden">Semua Program →</a>
    </div>
</section>
