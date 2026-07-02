@props([
    'stats' => [],
    'backgroundImage' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1800&q=85',
])

@php
    $homeUrl = \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/');
@endphp

<section class="relative isolate overflow-hidden text-white" style="background-color: #06132a;">
    <div class="absolute inset-0 -z-10">
        <img
            src="{{ $backgroundImage }}"
            alt=""
            class="h-full w-full object-cover"
        >
        <div class="absolute inset-0" style="background-color: rgba(6, 19, 42, 0.72);"></div>
    </div>

    <div class="mx-auto flex max-w-[1320px] items-center px-5 py-8 sm:px-6 lg:min-h-[240px] lg:px-8 lg:py-8">
        <div class="grid w-full items-center gap-6 lg:grid-cols-2">
            <div class="max-w-3xl justify-self-start text-left">
                <nav class="mb-4 flex items-center gap-2 text-xs font-bold text-white/70" aria-label="Breadcrumb">
                    <a href="{{ $homeUrl }}" class="transition hover:text-white">Beranda</a>
                    <span aria-hidden="true">/</span>
                    <span class="text-white">Program</span>
                </nav>

                <span class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.22em] text-white shadow-sm backdrop-blur">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#D99A25]"></span>
                    Katalog Program
                </span>

                <h1 class="mt-3 max-w-2xl text-3xl font-black leading-[1.08] tracking-normal text-white sm:text-4xl lg:text-[2.45rem]">
                    Program Edulaw
                </h1>
            </div>

            <div class="flex min-w-0 flex-col items-end gap-4 text-right lg:justify-self-end">
                <p class="max-w-2xl text-sm leading-6 text-white/78 lg:ml-auto">
                    Program Edulaw Project dirancang sebagai ruang belajar, diskusi, riset, dan kolaborasi untuk memperkuat literasi hukum publik yang setara, relevan, dan berdampak.
                </p>

                <div class="flex w-full max-w-sm flex-col rounded-[20px] border border-white/12 bg-white/10 p-1.5 text-right shadow-2xl shadow-black/20 backdrop-blur md:ml-auto md:w-auto md:max-w-none md:flex-row md:items-center md:justify-end">
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
    </div>
</section>
