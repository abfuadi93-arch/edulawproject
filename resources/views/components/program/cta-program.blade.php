@php
    $archiveUrl = \Illuminate\Support\Facades\Route::has('programs.archive') ? route('programs.archive') : url('/program/archive');
@endphp

<div {{ $attributes->merge(['class' => 'mt-5 flex flex-col gap-4 rounded-[20px] border border-slate-200 bg-white px-5 py-4 shadow-[0_14px_34px_rgba(15,23,42,0.05)] sm:flex-row sm:items-center sm:justify-between']) }}>
    <div class="flex items-start gap-3">
        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-[#EAF2FF] text-brand-navy">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M4 19.5V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14.5M8 7h8M8 11h8M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <div>
            <p class="text-sm font-black text-brand-ink">Tidak menemukan program yang sesuai?</p>
            <p class="mt-1 text-sm leading-6 text-slate-600">Lihat seluruh dokumentasi program yang telah selesai.</p>
        </div>
    </div>

    <a href="{{ $archiveUrl }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-brand-navy/20 bg-white px-4 py-2.5 text-sm font-black text-brand-navy transition hover:border-brand-navy hover:bg-brand-navy hover:text-white">
        Lihat Selengkapnya
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </a>
</div>
