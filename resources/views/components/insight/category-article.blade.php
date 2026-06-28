@props([
    'article' => null,
])

@if ($article)
    @php
        $url = route('insights.show', $article->slug);
        $image = $article->cover_image_url;
        $date = $article->published_at
            ? \Illuminate\Support\Carbon::parse($article->published_at)->translatedFormat('d M Y')
            : 'Belum dijadwalkan';
        $readingTime = ! empty($article->reading_time)
            ? $article->reading_time.' menit baca'
            : max(1, (int) ceil(str_word_count(strip_tags(($article->content ?? '').' '.($article->excerpt ?? ''))) / 200)).' menit baca';
    @endphp

    <a href="{{ $url }}" class="group/article grid min-h-[4.5rem] grid-cols-[3.25rem_minmax(0,1fr)] gap-3 rounded-xl p-2 transition duration-200 hover:bg-[#EAF2FF]">
        <div class="relative h-[3.25rem] w-[3.25rem] overflow-hidden rounded-[10px] bg-linear-to-br from-brand-navy via-[#224C7D] to-brand-teal">
            @if ($image)
                <img
                    src="{{ $image }}"
                    alt="{{ $article->title }}"
                    loading="lazy"
                    class="absolute inset-0 z-10 h-full w-full object-cover transition duration-200 group-hover/article:scale-105"
                    onerror="this.classList.add('hidden')"
                >
            @endif
        </div>

        <div class="min-w-0">
            <h4 class="insight-clamp-2 text-[13px] font-black leading-snug text-brand-ink transition group-hover/article:text-brand-navy">
                {{ $article->title }}
            </h4>

            <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] font-semibold text-slate-500">
                <span>{{ $date }}</span>
                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                <span>{{ $readingTime }}</span>
            </p>
        </div>
    </a>
@endif
