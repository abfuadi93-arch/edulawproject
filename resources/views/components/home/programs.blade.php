@props(['programs' => collect()])

@php
    $programCollection = collect($programs)->take(3)->values();
@endphp

<section id="program-edulaw" class="home-surface-warm scroll-mt-20 py-7 sm:py-8 lg:py-9" aria-labelledby="home-programs-title">
    <div class="section-shell">
        <div>
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="home-section-eyebrow text-[#b18332]">Program Edulaw</p>
                        <h2 id="home-programs-title" class="home-section-title">Belajar Hukum secara Kontekstual</h2>
                    </div>
                    <a href="{{ route('programs.index') }}" class="home-section-link hidden sm:inline-flex">Semua Program →</a>
                </div>

                <div class="mt-7 grid gap-4 md:grid-cols-3">
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
                        <article data-home-program class="group overflow-hidden rounded-xl border border-[#e7ebf0] bg-white transition hover:border-slate-300">
                            <a href="{{ route('programs.show', $program->slug) }}" class="block h-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                                <div class="relative h-40 overflow-hidden bg-[#234777]">
                                    @if ($image)
                                        <x-responsive-image :src="$image" alt="Poster {{ $program->display_title }}" :widths="[320, 480, 640]" sizes="(min-width: 1280px) 253px, (min-width: 768px) 33vw, 100vw" width="640" height="400" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]" onerror="this.remove()" />
                                    @endif
                                    <div class="absolute inset-x-3 top-3 flex items-center justify-between gap-2">
                                        <span class="max-w-32 truncate rounded bg-[#17375f]/90 px-2 py-1 text-[11px] font-extrabold uppercase tracking-wide text-white">{{ $program->categoryRelation?->name ?? 'Program' }}</span>
                                        <span class="rounded-full bg-white/90 px-2 py-1 text-[11px] font-extrabold text-[#102f56]">{{ $statusLabel }}</span>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h3 class="home-card-title line-clamp-2 min-h-12">{{ $program->display_title }}</h3>
                                    <div class="home-card-meta flex flex-wrap gap-x-3 gap-y-1">
                                        @if ($eventDate)<span>{{ $eventDate->translatedFormat('d M Y') }}</span>@endif
                                        @if ($eventDate && $format)<span>·</span>@endif
                                        @if ($format)<span>{{ $format }}</span>@endif
                                    </div>
                                    <span class="home-card-action mt-4">Lihat Detail →</span>
                                </div>
                            </a>
                        </article>
                    @empty
                        <div class="home-empty-state col-span-full py-4"><p class="text-sm leading-6 text-slate-600">Program terbaru sedang disiapkan.</p></div>
                    @endforelse
                </div>

                <a href="{{ route('programs.index') }}" class="home-section-link mt-6 sm:hidden">Semua Program →</a>
        </div>
    </div>
</section>
