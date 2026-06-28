@props([
    'stats' => [],
    'backgroundImage' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1800&q=85',
])

@php
    $homeUrl = \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/');
@endphp

<section class="relative isolate overflow-hidden bg-[#071426] text-white">
    <div class="absolute inset-0 -z-10">
        <img
            src="{{ $backgroundImage }}"
            alt=""
            class="h-full w-full object-cover opacity-52"
        >
        <div class="absolute inset-0 bg-linear-to-r from-[#06111F]/96 via-[#102B4B]/82 to-[#071426]/60"></div>
        <div class="absolute inset-y-0 right-0 w-2/3 bg-linear-to-l from-[#071426]/82 via-[#071426]/30 to-transparent"></div>
    </div>

    <div class="mx-auto flex max-w-[1320px] items-center px-5 py-11 sm:px-6 lg:min-h-[300px] lg:px-8 lg:py-16">
        <div class="grid w-full items-end gap-8 lg:grid-cols-[minmax(0,1fr)_auto]">
            <div class="max-w-3xl">
                <nav class="mb-7 flex items-center gap-2 text-xs font-bold text-white/70" aria-label="Breadcrumb">
                    <a href="{{ $homeUrl }}" class="transition hover:text-white">Beranda</a>
                    <span aria-hidden="true">/</span>
                    <span class="text-white">Program</span>
                </nav>

                <span class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.22em] text-white shadow-sm backdrop-blur">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#D99A25]"></span>
                    Katalog Program
                </span>

                <h1 class="mt-4 max-w-2xl text-4xl font-black leading-tight tracking-normal text-white sm:text-5xl lg:text-[3.25rem]">
                    Program Edulaw
                </h1>

                <p class="mt-4 max-w-2xl text-sm leading-7 text-white/78 sm:text-base">
                    Program Edulaw Project dirancang sebagai ruang belajar, diskusi, riset, dan kolaborasi untuk memperkuat literasi hukum publik yang setara, relevan, dan berdampak.
                </p>
            </div>

            <div class="flex flex-col rounded-[24px] border border-white/12 bg-white/10 p-2 shadow-2xl shadow-black/20 backdrop-blur md:flex-row md:items-center">
                @foreach ($stats as $stat)
                    <x-program.statistic-card
                        :label="$stat['label'] ?? ''"
                        :value="$stat['value'] ?? '0'"
                        :icon="$stat['icon'] ?? 'calendar'"
                    />
                @endforeach
            </div>
        </div>
    </div>
</section>
