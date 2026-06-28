@props([
    'audiences' => collect(),
])

@php
    $collaborationUrl = \Illuminate\Support\Facades\Route::has('collaboration.index') ? route('collaboration.index') : url('/kolaborasi');
@endphp

<aside {{ $attributes->merge(['class' => 'space-y-5 xl:sticky xl:top-24 xl:self-start']) }}>
    <section class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
        <h2 class="text-base font-black tracking-normal text-brand-ink">Untuk Siapa?</h2>

        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($audiences as $audience)
                <span class="inline-flex items-center rounded-full bg-[#F1F5F9] px-3 py-1.5 text-xs font-black text-brand-navy">
                    {{ $audience }}
                </span>
            @endforeach
        </div>
    </section>

    <section class="overflow-hidden rounded-[24px] border border-[#EBDCB9] bg-[#FFF8EA] p-5 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#B87316]">Ajukan Program</p>
        <h2 class="mt-2 text-xl font-black leading-tight tracking-normal text-brand-ink">
            Punya ide program? Ajukan kolaborasi bersama Edulaw.
        </h2>
        <p class="mt-3 text-sm leading-6 text-slate-600">
            Ceritakan kebutuhan kelas, diskusi, pelatihan, atau program riset yang ingin dikembangkan.
        </p>

        <a href="{{ $collaborationUrl }}" class="mt-5 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-brand-navy px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-brand-navy/18 transition hover:-translate-y-0.5 hover:bg-[#102B4B]">
            Ajukan Kolaborasi
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    </section>
</aside>
