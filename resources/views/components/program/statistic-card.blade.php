@props([
    'label' => '',
    'value' => '0',
    'icon' => 'calendar',
])

<div {{ $attributes->merge(['class' => 'flex w-full min-w-[168px] items-center justify-between gap-3 border-white/20 px-3 py-2.5 md:w-[168px] lg:border-l lg:first:border-l-0']) }}>
    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-brand-navy shadow-lg shadow-black/10">
        @switch($icon)
            @case('briefcase')
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M9 7V6a3 3 0 0 1 3-3h0a3 3 0 0 1 3 3v1m-9 4h12M5 7h14a2 2 0 0 1 2 2v9a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V9a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                @break

            @case('users')
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m12-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm4 10v-2a4 4 0 0 0-3-3.87M20 3.13a4 4 0 0 1 0 7.75M12 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                @break

            @default
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 0 1 2 2v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
        @endswitch
    </span>

    <span class="ml-auto min-w-0 text-right">
        <span class="block text-2xl font-black leading-none tracking-normal text-white">{{ $value }}</span>
        <span class="mt-1 block text-[11px] font-black uppercase tracking-[0.14em] text-white/75">{{ $label }}</span>
    </span>
</div>
