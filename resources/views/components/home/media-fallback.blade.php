@props([
    'kind' => 'editorial',
    'label' => null,
    'dark' => false,
    'placement' => 'center',
])

<div
    aria-hidden="true"
    data-home-media-fallback="{{ $kind }}"
    {{ $attributes->class([
        'absolute inset-0 flex flex-col gap-3 overflow-hidden p-4',
        'items-center justify-center text-center' => $placement === 'center',
        'items-end justify-start text-right' => $placement === 'corner',
        'bg-[linear-gradient(145deg,#eef3f7_0%,#dbe6ee_100%)] text-brand-navy' => ! $dark,
        'bg-[linear-gradient(145deg,#173b68_0%,#0f2f55_100%)] text-white' => $dark,
    ]) }}
>
    <span @class(['grid size-10 place-items-center rounded-lg border', 'border-brand-navy/10 bg-white/65' => ! $dark, 'border-white/15 bg-white/10' => $dark])>
        @switch($kind)
            @case('program')
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5.5A3.5 3.5 0 0 1 7.5 2H11v17H7.5A3.5 3.5 0 0 0 4 22zM20 5.5A3.5 3.5 0 0 0 16.5 2H13v17h3.5A3.5 3.5 0 0 1 20 22z"/></svg>
                @break
            @case('opportunity')
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 11v2M6 9v6M9 7v10M12 12l8-5v10l-8-5Z"/><path d="M12 12v7a2 2 0 0 1-2 2H9"/></svg>
                @break
            @case('publication')
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 3h9l3 3v15H6zM14 3v4h4M9 11h6M9 15h6"/></svg>
                @break
            @default
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 3h9l3 3v15H6zM14 3v4h4M9 11h6M9 15h6"/></svg>
        @endswitch
    </span>

    @if (filled($label))
        <span @class(['max-w-full truncate text-xs font-bold uppercase tracking-[0.1em]', 'text-brand-navy/65' => ! $dark, 'text-white/70' => $dark])>{{ $label }}</span>
    @endif
</div>
