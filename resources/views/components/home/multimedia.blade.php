@props(['items' => collect()])

@php
    use Illuminate\Support\Str;

    $externalMediaUrl = function ($item): ?string {
        $url = filled($item->media_url) ? trim($item->media_url) : null;

        return $url && filter_var($url, FILTER_VALIDATE_URL) && Str::startsWith($url, ['http://', 'https://'])
            ? $url
            : null;
    };

    $mediaCta = function ($item): string {
        $type = \App\Models\Multimedia::normalizeType($item->type);

        return match ($type) {
            'video', 'shorts', 'reels', 'webinar' => 'Tonton',
            'podcast' => 'Dengarkan',
            'gallery', 'documentation', 'poster' => 'Lihat Konten',
            default => 'Buka Konten',
        };
    };
@endphp

<section id="multimedia" class="home-section scroll-mt-24 bg-white" aria-labelledby="home-multimedia-title">
    <div class="section-shell">
        <div class="home-section-header">
            <div class="home-section-copy">
                <p class="home-section-eyebrow">Tonton & Dengarkan</p>
                <h2 id="home-multimedia-title" class="home-section-title">Multimedia</h2>
                <p class="home-section-description">Video, podcast, dan dokumentasi terbaru untuk menikmati pembelajaran hukum dalam beragam format.</p>
            </div>
            <a href="{{ route('multimedia.index') }}" class="section-link">
                Lihat Semua Multimedia
                <span aria-hidden="true">→</span>
            </a>
        </div>

        @if ($items->isNotEmpty())
            <div class="mt-7 grid gap-5 md:grid-cols-3">
                @foreach ($items as $item)
                    @php($mediaUrl = $externalMediaUrl($item))
                    <article class="home-card home-card-interactive h-full" data-home-multimedia>
                        <div class="relative aspect-video overflow-hidden bg-linear-to-br from-brand-navy via-brand-blue to-brand-sky">
                            @if ($item->thumbnail_url)
                                <img
                                    src="{{ $item->thumbnail_url }}"
                                    alt="Thumbnail {{ $item->title }}"
                                    width="640"
                                    height="360"
                                    loading="lazy"
                                    decoding="async"
                                    class="h-full w-full object-cover"
                                >
                            @else
                                <div class="flex h-full items-center justify-center text-white" aria-hidden="true" data-home-multimedia-fallback>
                                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/30">
                                        <svg class="ml-0.5 h-6 w-6" viewBox="0 0 24 24" fill="none">
                                            <path d="m9 7 8 5-8 5V7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                            @endif
                            <span class="absolute left-3 top-3 edulaw-badge edulaw-badge-on-image">{{ $item->display_type }}</span>
                        </div>

                        <div class="p-5">
                            <p class="home-meta">
                                {{ $item->display_platform }}
                                @if ($item->published_at)
                                    <span aria-hidden="true">·</span> {{ $item->published_at->translatedFormat('d M Y') }}
                                @endif
                            </p>
                            <h3 class="mt-2 text-lg font-extrabold leading-snug text-brand-ink">{{ $item->title }}</h3>
                            @if ($item->description)
                                <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ $item->description }}</p>
                            @endif

                            @if ($mediaUrl)
                                <a
                                    href="{{ $mediaUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="home-card-link mt-4 inline-flex items-center gap-2 text-sm font-extrabold text-brand-navy"
                                    aria-label="Buka multimedia {{ $item->title }} di situs eksternal"
                                >
                                    {{ $mediaCta($item) }} <span aria-hidden="true">↗</span>
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="home-empty-state mt-7">
                <p class="text-sm font-bold text-brand-ink">Belum ada konten multimedia yang ditampilkan.</p>
                <p class="mt-1 text-sm text-slate-600">Nantikan video, podcast, dan dokumentasi terbaru dari Edulaw.</p>
            </div>
        @endif
    </div>
</section>
