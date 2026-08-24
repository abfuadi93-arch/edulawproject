@props(['person', 'profile' => null, 'role', 'compact' => false, 'stacked' => false, 'mini' => false])

@php
    $hasProfile = $profile && filled($profile->slug);
    $fallbackImage = asset('images/logo/icon-bg.png');
    $description = $person['interest_text'] ?: ($person['organization_position'] ?? $person['position'] ?? $role);
    $cardLayout = $mini
        ? 'flex h-full flex-col'
        : ($stacked
            ? 'flex h-full flex-col'
            : 'grid h-full '.($compact ? 'sm:grid-cols-[0.82fr_1.18fr]' : 'sm:grid-cols-[0.9fr_1.1fr]'));
@endphp

<article class="group h-full overflow-hidden {{ $mini ? 'rounded-xl bg-white' : 'rounded-[14px] bg-[#f7f8fa]' }}">
    @if ($hasProfile)
        <a href="{{ route('profiles.show', $profile->slug) }}" class="{{ $cardLayout }} focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-brand-amber" aria-label="Lihat profil {{ $person['name'] }}">
    @else
        <div class="{{ $cardLayout }}">
    @endif

        <div class="relative overflow-hidden bg-brand-mist {{ $mini ? 'aspect-square' : ($stacked ? 'aspect-[4/3]' : 'min-h-52 sm:min-h-64') }}">
            <img src="{{ $person['photo'] }}" alt="{{ $person['name'] }}" loading="lazy" class="absolute inset-0 size-full object-cover object-top transition duration-500 group-hover:scale-[1.02]" onerror="this.onerror=null;this.src='{{ $fallbackImage }}'">
        </div>
        <div class="flex min-w-0 {{ $stacked || $mini ? 'flex-1' : '' }} flex-col {{ $mini ? 'p-2' : 'justify-center p-5 sm:p-6' }}">
            <span class="w-fit rounded-full bg-brand-amber-soft font-extrabold uppercase text-brand-navy {{ $mini ? 'px-2 py-0.5 text-[11px] tracking-[0.05em]' : 'px-3 py-1 text-[11px] tracking-[0.09em]' }}">{{ $role }}</span>
            <h3 class="font-display font-black leading-tight text-brand-navy {{ $mini ? 'mt-1.5 line-clamp-2 text-xs' : 'mt-3 '.($compact ? 'text-xl' : 'text-2xl') }}">{{ $person['name'] }}</h3>
            @if (filled($person['organization_position'] ?? null) && $person['organization_position'] !== $role)
                <p class="mt-1 text-xs font-bold leading-5 text-brand-coral">{{ $person['organization_position'] }}</p>
            @endif
            @if ($mini)
                <p class="mt-1 line-clamp-1 text-[11px] leading-4 text-slate-500">{{ $description }}</p>
            @else
                <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">{{ $description }}</p>
            @endif
            @if ($hasProfile && ! $mini)
                <span class="mt-4 inline-flex text-xs font-extrabold text-brand-navy">Lihat Profil <span class="ml-1" aria-hidden="true">→</span></span>
            @endif
        </div>

    @if ($hasProfile)
        </a>
    @else
        </div>
    @endif
</article>
