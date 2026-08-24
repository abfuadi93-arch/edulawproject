@props([
    'platform',
    'label' => null,
    'dark' => false,
])

@php
    $platform = strtolower($platform);
    $label ??= match ($platform) {
        'youtube' => 'YouTube',
        'instagram' => 'Instagram',
        'google_photos' => 'Google Photos',
        default => 'Platform eksternal',
    };
@endphp

<span {{ $attributes->class([
    'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-black uppercase tracking-[0.1em]',
    'bg-white/92 text-brand-navy shadow-sm' => $dark,
    'border border-slate-200 bg-white text-brand-navy' => ! $dark,
]) }}>
    @if ($platform === 'instagram')
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3.5" y="3.5" width="17" height="17" rx="5" stroke="currentColor" stroke-width="2"/>
            <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"/>
            <circle cx="17.5" cy="6.8" r="1" fill="currentColor"/>
        </svg>
    @elseif ($platform === 'google_photos')
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 2a5 5 0 0 1 5 5v5h-5a5 5 0 0 1 0-10Z" fill="currentColor" opacity=".9"/>
            <path d="M22 12a5 5 0 0 1-5 5h-5v-5a5 5 0 0 1 10 0Z" fill="currentColor" opacity=".65"/>
            <path d="M12 22a5 5 0 0 1-5-5v-5h5a5 5 0 0 1 0 10Z" fill="currentColor" opacity=".45"/>
            <path d="M2 12a5 5 0 0 1 5-5h5v5a5 5 0 0 1-10 0Z" fill="currentColor" opacity=".75"/>
        </svg>
    @else
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M21.2 7.2a2.8 2.8 0 0 0-2-2C17.4 4.7 12 4.7 12 4.7s-5.4 0-7.2.5a2.8 2.8 0 0 0-2 2A29 29 0 0 0 2.3 12a29 29 0 0 0 .5 4.8 2.8 2.8 0 0 0 2 2c1.8.5 7.2.5 7.2.5s5.4 0 7.2-.5a2.8 2.8 0 0 0 2-2 29 29 0 0 0 .5-4.8 29 29 0 0 0-.5-4.8ZM10 15.2V8.8l5.5 3.2-5.5 3.2Z"/>
        </svg>
    @endif

    {{ $label }}
</span>
