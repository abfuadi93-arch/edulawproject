@props([
    'program' => null,
])

@if ($program)
    @php
        $detailUrl = \Illuminate\Support\Facades\Route::has('programs.show')
            ? route('programs.show', $program->slug)
            : url('/program/'.$program->slug);

        $image = edulaw_file_url($program->image ?? null);
        $title = $program->display_title ?? $program->name ?? 'Program Edulaw';
        $excerpt = $program->display_description ?? $program->short_description ?? '';
        $category = $program->display_category ?? $program->categoryRelation?->name ?? 'Program';
        $date = $program->event_date ? $program->event_date->translatedFormat('d M Y') : 'Tanggal menyusul';
        $format = $program->display_format ?? \Illuminate\Support\Str::headline((string) ($program->format ?? 'Fleksibel'));
        $level = $program->display_level ?? \Illuminate\Support\Str::headline((string) ($program->level ?? 'Umum'));
        $location = $program->location ?: 'Lokasi menyusul';
    @endphp

    <section class="channel-section home-surface-paper">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="channel-feature-label">
                <span class="text-[#D99A25]">★</span>
                Featured Program
            </div>

            <article class="channel-feature-card grid overflow-hidden rounded-2xl border border-[#dce5e3] bg-white lg:grid-cols-[minmax(0,40fr)_minmax(0,60fr)]" data-channel-feature-card>
                <a href="{{ $detailUrl }}" class="group relative min-h-[300px] overflow-hidden bg-[#102B4B] sm:min-h-[360px] lg:h-full lg:min-h-0">
                    @if ($image)
                        <img
                            src="{{ $image }}"
                            alt="{{ $title }}"
                            class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                        >
                    @else
                        <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#123D68] to-brand-teal"></div>
                    @endif

                    <div class="absolute inset-0 bg-linear-to-t from-[#071426]/55 via-transparent to-transparent"></div>
                </a>

                <div class="flex flex-col justify-center channel-feature-content">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="channel-feature-badge bg-[#EAF2FF] text-brand-navy">
                            {{ $category }}
                        </span>
                        <span class="channel-feature-badge bg-[#DFF7EF] text-[#087B65]">
                            Featured
                        </span>
                    </div>

                    <h2 class="type-role-feature channel-feature-title">
                        {{ $title }}
                    </h2>

                    <p class="channel-feature-summary">
                        {{ $excerpt }}
                    </p>

                    <div class="channel-feature-meta grid gap-2 sm:grid-cols-2">
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 0 1 2 2v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ $date }}
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 20h9M3 4h18M5 4v16h4V4m6 0v16h4V4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ $format }}
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 3 3 8l9 5 9-5-9-5Zm-7 8v5l7 4 7-4v-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ $level }}
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Zm0-8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ $location }}
                        </span>
                    </div>

                    <div class="channel-feature-actions">
                        <a href="{{ $detailUrl }}" class="channel-feature-primary-action">
                            Lihat Detail Program
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </article>
        </div>
    </section>
@endif
