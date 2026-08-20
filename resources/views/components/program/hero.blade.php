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

    <div class="mx-auto flex max-w-7xl items-center px-5 py-8 sm:px-6 lg:min-h-[240px] lg:px-8 lg:py-8">
        <div class="grid w-full items-center gap-6 lg:grid-cols-2">
            <div class="max-w-3xl justify-self-start text-left">
                <nav class="flex items-center gap-1.5 text-[11px] font-medium text-white/55" aria-label="Breadcrumb">
                    <a href="{{ $homeUrl }}" class="rounded-sm transition hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">Beranda</a>
                    <span aria-hidden="true">/</span>
                    <span class="text-white">Program</span>
                </nav>

                <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.2em] text-brand-amber">Kanal Program</p>

                <h1 class="mt-1 max-w-2xl text-3xl font-black leading-[1.08] tracking-normal text-white sm:text-4xl lg:text-[2.45rem]">
                    Program Edulaw
                </h1>
                <p class="mt-1 max-w-2xl text-pretty text-sm leading-6 text-white/78">
                    Program Edulaw Project dirancang sebagai ruang belajar, diskusi, riset, dan kolaborasi untuk memperkuat literasi hukum publik yang setara, relevan, dan berdampak.
                </p>
            </div>

            <div class="flex min-w-0 flex-col items-end text-right lg:justify-self-end">
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
