@props(['articles', 'categoryName', 'publishedDate', 'readingTime', 'authorName', 'excerpt'])

@php
    $primary = collect($articles ?? [])->first();
    $hasImage = filled($primary?->cover_image) && edulaw_file_exists($primary?->cover_image);
@endphp

@if ($primary)
    <section class="channel-section bg-white" aria-labelledby="featured-editorial-heading">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <p class="channel-feature-label"><span class="text-[#D99A25]" aria-hidden="true">★</span> Editorial Utama</p>

            <article class="channel-feature-card grid overflow-hidden rounded-[14px] bg-[#f7f8fa] lg:grid-cols-[minmax(0,0.92fr)_minmax(0,1.08fr)]" data-featured-editorial="{{ $primary->id }}" data-channel-feature-card>
                <a href="{{ route('insights.show', $primary->slug) }}" class="group relative block min-h-[240px] overflow-hidden bg-brand-navy focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-brand-amber sm:min-h-[330px] lg:h-full lg:min-h-0">
                    @if ($hasImage)
                        <img src="{{ $primary->cover_image_url }}" alt="{{ $primary->title }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.02] motion-reduce:transition-none" fetchpriority="high">
                    @else
                        <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#234a70] to-[#0f766e]"></div>
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.14),transparent_30%)]"></div>
                    @endif
                </a>

                <div class="flex min-w-0 flex-col justify-center p-6 sm:p-8 lg:p-6">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="channel-feature-badge bg-brand-navy/7 text-brand-navy">{{ $categoryName($primary) }}</span>
                        @if ($primary->editor_pick)
                            <span class="channel-feature-badge bg-[#dff4ec] text-[#14705f]">Pilihan Editor</span>
                        @endif
                    </div>
                    <h2 id="featured-editorial-heading" class="channel-feature-title">{{ $primary->title }}</h2>
                    @if ($excerpt($primary, 240) !== '')
                        <p class="channel-feature-summary">{{ $excerpt($primary, 240) }}</p>
                    @endif
                    <div class="channel-feature-meta flex flex-wrap items-center gap-x-2 gap-y-1">
                        <time datetime="{{ optional($primary->published_at)->toDateString() }}">{{ $publishedDate($primary) }}</time>
                        <span aria-hidden="true">·</span><span>{{ $readingTime($primary) }}</span>
                        @if (filled($authorName($primary)))
                            <span aria-hidden="true">·</span><span>{{ $authorName($primary) }}</span>
                        @endif
                    </div>
                    <div class="channel-feature-actions">
                        <a href="{{ route('insights.show', $primary->slug) }}" class="channel-feature-primary-action focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-brand-amber">Baca Editorial <span aria-hidden="true">→</span></a>
                    </div>
                </div>
            </article>
        </div>
    </section>
@endif
