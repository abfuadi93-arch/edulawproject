@props(['archiveUrl' => null])

<section class="relative isolate overflow-hidden bg-brand-navy text-white">
    <img
        src="https://images.unsplash.com/photo-1589578527966-fdac0f44566c?auto=format&fit=crop&w=1800&q=85"
        alt="Patung Lady Justice sebagai simbol hukum"
        class="absolute inset-0 -z-20 h-full w-full object-cover object-[center_38%]"
    >
    <div
        class="absolute inset-0 -z-10"
        style="background-image: linear-gradient(90deg, rgba(4, 17, 40, 0.82) 0%, rgba(6, 26, 61, 0.58) 52%, rgba(6, 26, 61, 0.34) 100%), linear-gradient(180deg, rgba(6, 19, 42, 0.12) 0%, rgba(6, 19, 42, 0.28) 100%);"
    ></div>

    <div class="mx-auto grid max-w-7xl gap-6 px-5 py-7 sm:px-6 sm:py-8 lg:min-h-[240px] lg:grid-cols-[minmax(0,1.12fr)_minmax(360px,0.88fr)] lg:items-center lg:gap-10 lg:px-8 lg:py-5">
        <div class="min-w-0 max-w-3xl">
            <nav aria-label="Breadcrumb" class="flex items-center gap-1.5 text-[11px] font-medium text-white/55">
                <a href="{{ route('home') }}" class="rounded-sm transition hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">Beranda</a>
                <span aria-hidden="true">/</span>
                <span class="text-white">Editorial</span>
            </nav>
            <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.2em] text-brand-amber">Kanal Editorial</p>
            <h1 class="mt-1 max-w-3xl text-balance font-display text-3xl font-black leading-[1.08] tracking-normal text-white sm:text-4xl lg:text-4xl">
                Gagasan Hukum untuk Ruang Publik
            </h1>
            <p class="mt-1 max-w-2xl text-pretty text-sm leading-6 text-white/78">
                Editorial Edulaw menghadirkan analisis hukum, regulasi, dan kebijakan publik yang jernih untuk memperluas pemahaman bersama.
            </p>
        </div>

        <div class="grid min-w-0 gap-2.5 rounded-2xl border border-white/15 bg-white/10 p-3 shadow-sm backdrop-blur sm:grid-cols-2 lg:justify-self-end">
            <a href="#editorial-terbaru" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-amber px-4 text-center text-sm font-bold text-brand-ink transition duration-200 hover:bg-[#d99a25] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
                Jelajahi Artikel
            </a>
            <a href="{{ route('collaboration.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-white/30 bg-white/5 px-4 text-center text-sm font-bold text-white transition duration-200 hover:border-white/55 hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">
                Ajukan Kolaborasi
            </a>
        </div>
    </div>
</section>
