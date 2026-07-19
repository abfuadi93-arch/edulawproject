@props(['archiveUrl' => null])

<section class="relative isolate overflow-hidden bg-brand-navy text-white">
    <img
        src="https://images.unsplash.com/photo-1589578527966-fdac0f44566c?auto=format&fit=crop&w=1800&q=85"
        alt="Patung Lady Justice sebagai simbol hukum"
        class="absolute inset-0 -z-20 h-full w-full object-cover object-[center_38%]"
    >
    <div class="absolute inset-0 -z-10 bg-brand-navy/70"></div>
    <div class="absolute inset-0 -z-10 bg-linear-to-r from-[#041128]/98 via-[#061a3d]/92 to-[#061a3d]/60"></div>

    <div class="mx-auto grid max-w-7xl gap-6 px-4 py-7 sm:px-6 sm:py-8 lg:grid-cols-[minmax(0,1.12fr)_minmax(360px,0.88fr)] lg:items-center lg:gap-10 lg:px-8 lg:py-9">
        <div class="min-w-0 max-w-3xl">
            <nav aria-label="Breadcrumb" class="flex items-center gap-1.5 text-[11px] font-medium text-white/55">
                <a href="{{ route('home') }}" class="rounded-sm transition hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">Beranda</a>
                <span aria-hidden="true">/</span>
                <span class="text-white">Editorial</span>
            </nav>
            <p class="mt-3 text-[10px] font-bold uppercase tracking-[0.2em] text-brand-amber">Kanal Editorial</p>
            <h1 class="mt-2 max-w-3xl text-balance font-display text-4xl font-bold leading-[1.04] tracking-tight text-white sm:text-5xl lg:text-6xl">
                Gagasan Hukum untuk Ruang Publik
            </h1>
            <p class="mt-3 max-w-2xl text-pretty text-sm leading-6 text-white/78 sm:text-base sm:leading-7">
                Editorial Edulaw menghadirkan analisis hukum, regulasi, dan kebijakan publik yang jernih untuk memperluas pemahaman bersama.
            </p>
        </div>

        <div class="min-w-0 rounded-2xl border border-white/15 bg-white/10 p-4 shadow-sm backdrop-blur sm:p-5 lg:justify-self-end">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-amber">Fokus Editorial</p>
                <p class="mt-2 text-sm leading-6 text-white/75">Analisis hukum publik, regulasi, dan kebijakan yang berdampak pada masyarakat.</p>
                <div class="mt-2.5 flex flex-wrap gap-1.5">
                    <span class="rounded-full border border-white/15 bg-white/8 px-2.5 py-1 text-[11px] font-semibold text-white/80">Regulasi</span>
                    <span class="rounded-full border border-white/15 bg-white/8 px-2.5 py-1 text-[11px] font-semibold text-white/80">Kebijakan Publik</span>
                    <span class="rounded-full border border-white/15 bg-white/8 px-2.5 py-1 text-[11px] font-semibold text-white/80">Literasi Hukum</span>
                </div>
            </div>
            <div class="mt-4 grid gap-2.5 sm:grid-cols-2">
                <a href="#editorial-terbaru" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-amber px-4 text-center text-sm font-bold text-brand-ink transition duration-200 hover:bg-[#d99a25] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
                    Jelajahi Artikel
                </a>
                <a href="{{ route('collaboration.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-white/30 bg-white/5 px-4 text-center text-sm font-bold text-white transition duration-200 hover:border-white/55 hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">
                    Ajukan Kolaborasi
                </a>
            </div>
        </div>
    </div>
</section>
