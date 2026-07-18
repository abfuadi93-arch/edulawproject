@props(['archiveUrl'])

<section class="relative isolate min-h-[280px] overflow-hidden bg-brand-navy text-white sm:min-h-[300px] lg:min-h-[320px]">
    <img
        src="https://images.unsplash.com/photo-1589578527966-fdac0f44566c?auto=format&fit=crop&w=1800&q=85"
        alt="Patung Lady Justice sebagai simbol hukum"
        class="absolute inset-0 -z-20 h-full w-full object-cover object-[center_38%]"
    >
    <div class="absolute inset-0 -z-10 bg-brand-navy/55"></div>
    <div class="absolute inset-0 -z-10 bg-linear-to-r from-[#041128]/98 via-[#061a3d]/82 to-[#061a3d]/28"></div>

    <div class="mx-auto grid min-h-[280px] max-w-7xl items-center gap-6 px-5 py-8 sm:min-h-[300px] sm:px-6 lg:min-h-[320px] lg:grid-cols-[1.18fr_0.82fr] lg:px-8">
        <div class="min-w-0">
            <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs font-semibold text-white/65">
                <a href="{{ route('home') }}" class="rounded-sm transition hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">Beranda</a>
                <span aria-hidden="true">/</span>
                <span class="text-white">Editorial</span>
            </nav>
            <p class="mt-5 text-[11px] font-bold uppercase tracking-[0.2em] text-brand-amber">Kanal Editorial</p>
            <h1 class="mt-2 max-w-2xl text-balance font-display text-[2.55rem] font-bold leading-[1.02] tracking-tight text-white sm:text-5xl lg:text-[3.45rem]">
                Gagasan Hukum untuk Ruang Publik
            </h1>
        </div>

        <div class="min-w-0 lg:pl-8">
            <p class="max-w-xl text-pretty text-sm leading-6 text-white/78 sm:text-base sm:leading-7">
                Analisis hukum, regulasi, dan kebijakan publik yang jernih untuk memperluas pemahaman bersama.
            </p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="#editorial-terbaru" class="inline-flex min-h-11 items-center justify-center rounded-md bg-brand-amber px-5 text-sm font-bold text-brand-ink transition hover:bg-[#d99a25] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
                    Baca Editorial Terbaru
                </a>
                <a href="{{ $archiveUrl }}" class="inline-flex min-h-11 items-center justify-center rounded-md border border-white/30 bg-white/5 px-5 text-sm font-bold text-white transition hover:border-white/55 hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">
                    Jelajahi Arsip
                </a>
            </div>
        </div>
    </div>
</section>
