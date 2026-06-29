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
    @endphp

    <a href="{{ $url }}" class="group/article grid grid-cols-[4.25rem_minmax(0,1fr)] gap-3 border-t border-slate-100 py-3 first:border-t-0 first:pt-0">
        <div class="relative aspect-4/3 overflow-hidden rounded-xl bg-linear-to-br from-[#061A3D] via-[#1E3763] to-[#476D8A]">
            @if ($image)
                <img
                    src="{{ $image }}"
                    alt="{{ $article->title }}"
                    loading="lazy"
                    class="absolute inset-0 h-full w-full object-cover transition duration-[250ms] ease-out group-hover/article:scale-[1.05]"
                    onerror="this.remove()"
                >
            @endif
        </div>

        <div class="min-w-0">
            <h4 class="insight-clamp-2 text-sm font-semibold leading-snug text-brand-ink underline-offset-4 transition group-hover/article:text-brand-navy group-hover/article:underline">
                {{ $article->title }}
            </h4>

            <p class="mt-1 text-xs font-medium text-slate-500">
                {{ $date }}
            </p>
        </div>
    </a>
@endif
