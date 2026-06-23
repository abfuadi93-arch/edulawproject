@props([
    'eyebrow' => 'Butuh Bantuan?',
    'title' => 'Hubungi Edulaw Project.',
    'body' => 'Sampaikan pertanyaan atau kebutuhan informasi melalui halaman kontak.',
    'url' => null,
    'label' => 'Hubungi Kami',
])

@php
    $url = $url ?? route('contact.index');
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl bg-[#07111F] p-5 text-white shadow-sm shadow-brand-ink/10']) }}>
    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#C9891F]">
        {{ $eyebrow }}
    </p>

    <h3 class="mt-3 text-xl font-extrabold leading-tight text-white">
        {{ $title }}
    </h3>

    <p class="mt-2 text-sm leading-6 text-white/72">
        {{ $body }}
    </p>

    <a
        href="{{ $url }}"
        class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#B87316] px-4 py-3 text-sm font-black text-white transition duration-300 hover:bg-[#D99A25] hover:text-white"
    >
        {{ $label }}
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </a>
</div>
