@props([
    'programs' => collect(),
    'categories' => collect(),
    'years' => collect(),
    'search' => '',
    'selectedCategory' => '',
    'selectedYear' => '',
])

@php
    $items = $programs instanceof \Illuminate\Pagination\AbstractPaginator ? $programs->getCollection() : collect($programs);
    $archiveUrl = \Illuminate\Support\Facades\Route::has('programs.archive') ? route('programs.archive') : url('/program/archive');
    $sliderId = 'program-archive-slider';
@endphp

<section id="program-arsip" class="bg-[#F8FAFC] py-12 sm:py-14">
    <div class="mx-auto max-w-[1320px] px-5 sm:px-6 lg:px-8">
        <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.06)] sm:p-6">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-brand-navy">
                        Program Arsip
                    </p>
                    <h2 class="mt-2 whitespace-nowrap text-[clamp(1rem,4vw,1.875rem)] font-black leading-tight tracking-normal text-brand-ink">
                        Dokumentasi seluruh program yang telah selesai.
                    </h2>
                </div>

                <a href="{{ $archiveUrl }}" class="inline-flex items-center gap-2 text-sm font-black text-brand-navy transition hover:text-[#102B4B]">
                    Lihat Selengkapnya
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>

            @if ($items->isNotEmpty())
                <div class="relative overflow-hidden">
                    <button
                        type="button"
                        aria-label="Geser arsip ke kiri"
                        data-program-archive-prev
                        class="absolute left-0 top-1/2 z-10 grid h-11 w-11 -translate-x-3 -translate-y-1/2 place-items-center rounded-full border border-slate-200 bg-white text-brand-navy shadow-lg shadow-slate-900/10 transition hover:bg-brand-navy hover:text-white sm:-translate-x-5"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div
                        id="{{ $sliderId }}"
                        data-program-archive-slider
                        class="flex cursor-grab snap-x snap-mandatory gap-5 overflow-x-auto scroll-smooth pb-4 active:cursor-grabbing [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                    >
                        @foreach ($items as $program)
                            <div class="min-w-0 shrink-0 basis-[82%] snap-start sm:basis-[calc(50%_-_0.625rem)] md:basis-[calc(33.333%_-_0.834rem)] lg:basis-[calc(25%_-_0.938rem)] xl:basis-[calc(20%_-_1rem)]">
                                <x-program.archive-card :program="$program" />
                            </div>
                        @endforeach
                    </div>

                    <button
                        type="button"
                        aria-label="Geser arsip ke kanan"
                        data-program-archive-next
                        class="absolute right-0 top-1/2 z-10 grid h-11 w-11 translate-x-3 -translate-y-1/2 place-items-center rounded-full border border-slate-200 bg-white text-brand-navy shadow-lg shadow-slate-900/10 transition hover:bg-brand-navy hover:text-white sm:translate-x-5"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            @else
                <div class="rounded-[20px] border border-dashed border-slate-300 bg-[#F8FAFC] p-10 text-center">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-brand-navy">Arsip belum tersedia</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Dokumentasi program selesai akan tampil di sini.</p>
                </div>
            @endif
        </div>
    </div>
</section>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-program-archive-slider]').forEach((slider) => {
                    const section = slider.closest('section');
                    const previous = section?.querySelector('[data-program-archive-prev]');
                    const next = section?.querySelector('[data-program-archive-next]');
                    const scrollAmount = () => Math.max(260, slider.clientWidth * 0.82);
                    const advance = () => {
                        const maxScroll = slider.scrollWidth - slider.clientWidth - 4;

                        if (maxScroll <= 0) {
                            return;
                        }

                        if (slider.scrollLeft >= maxScroll) {
                            slider.scrollTo({ left: 0, behavior: 'smooth' });
                            return;
                        }

                        slider.scrollBy({ left: scrollAmount(), behavior: 'smooth' });
                    };
                    let autoplay = window.setInterval(advance, 15000);
                    const restartAutoplay = () => {
                        window.clearInterval(autoplay);
                        autoplay = window.setInterval(advance, 15000);
                    };

                    previous?.addEventListener('click', () => {
                        slider.scrollBy({ left: -scrollAmount(), behavior: 'smooth' });
                        restartAutoplay();
                    });

                    next?.addEventListener('click', () => {
                        slider.scrollBy({ left: scrollAmount(), behavior: 'smooth' });
                        restartAutoplay();
                    });

                    let isDragging = false;
                    let startX = 0;
                    let scrollLeft = 0;
                    let hasMoved = false;

                    slider.addEventListener('pointerdown', (event) => {
                        if (event.pointerType === 'touch' || event.target.closest('a, button')) {
                            return;
                        }

                        isDragging = true;
                        hasMoved = false;
                        startX = event.pageX;
                        scrollLeft = slider.scrollLeft;
                        slider.setPointerCapture(event.pointerId);
                    });

                    slider.addEventListener('pointermove', (event) => {
                        if (! isDragging) {
                            return;
                        }

                        const distance = event.pageX - startX;

                        if (Math.abs(distance) > 6) {
                            hasMoved = true;
                            event.preventDefault();
                            slider.scrollLeft = scrollLeft - distance;
                        }
                    });

                    const stopDragging = () => {
                        if (isDragging && hasMoved) {
                            restartAutoplay();
                        }

                        isDragging = false;
                        hasMoved = false;
                    };

                    slider.addEventListener('pointerup', stopDragging);
                    slider.addEventListener('pointercancel', stopDragging);
                    slider.addEventListener('mouseleave', stopDragging);
                    slider.addEventListener('mouseenter', () => window.clearInterval(autoplay));
                    slider.addEventListener('mouseleave', restartAutoplay);
                });
            });
        </script>
    @endpush
@endonce
