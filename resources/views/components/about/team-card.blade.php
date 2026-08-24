@props(['person', 'profile' => null])

@php
    $hasProfile = $profile && filled($profile->slug);
    $fallbackImage = asset('images/logo/icon-bg.png');
    $role = $person['organization_position'] ?? $person['position'] ?? 'Contributor';
@endphp

<article class="group min-w-0">
    @if ($hasProfile)
        <a href="{{ route('profiles.show', $profile->slug) }}" class="block focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-brand-amber" aria-label="Lihat profil {{ $person['name'] }}">
    @else
        <div>
    @endif
        <div class="relative aspect-square overflow-hidden rounded-[13px] bg-brand-mist">
            <img src="{{ $person['photo'] }}" alt="{{ $person['name'] }}" loading="lazy" class="absolute inset-0 size-full object-cover object-top transition duration-500 group-hover:scale-[1.025]" onerror="this.onerror=null;this.src='{{ $fallbackImage }}'">
        </div>
        <h4 class="mt-3 line-clamp-2 text-sm font-black leading-snug text-brand-navy">{{ $person['name'] }}</h4>
        <p class="mt-1 line-clamp-2 text-xs font-semibold leading-5 text-slate-500">{{ $role }}</p>
        @if ($hasProfile)
            <span class="mt-2 inline-flex text-[11px] font-extrabold text-brand-navy">Lihat Profil <span class="ml-1" aria-hidden="true">→</span></span>
        @endif
    @if ($hasProfile)
        </a>
    @else
        </div>
    @endif
</article>
