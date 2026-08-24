@props([
    'featured' => null,
    'items' => collect(),
])

@if ($featured)
    <section id="multimedia" class="home-section home-surface-paper scroll-mt-24" aria-labelledby="home-multimedia-title">
        <div class="section-shell">
            <div class="home-section-header">
                <div class="home-section-copy">
                    <p class="home-section-eyebrow">Multimedia</p>
                    <h2 id="home-multimedia-title" class="home-section-title">Belajar Hukum Melalui Beragam Format</h2>
                    <p class="home-section-description">Video, podcast, dan diskusi untuk memperluas wawasan hukum.</p>
                </div>

                <a href="{{ route('multimedia.index') }}" class="home-section-link">Lihat Semua Multimedia →</a>
            </div>

            <div @class([
                'mt-8 grid items-stretch gap-6',
                'mx-auto max-w-3xl' => $items->isEmpty(),
                'lg:grid-cols-[minmax(0,1.85fr)_minmax(320px,1fr)]' => $items->isNotEmpty(),
            ])>
                <x-multimedia.featured-card :item="$featured" variant="home" />

                @if ($items->isNotEmpty())
                    <div class="grid auto-rows-fr content-stretch gap-3">
                        @foreach ($items->take(3) as $item)
                            <x-multimedia.media-card :item="$item" variant="horizontal" />
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
