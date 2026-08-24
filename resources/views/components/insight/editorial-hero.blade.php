@props([
    'articleCount' => 0,
    'categoryCount' => 0,
])

<section class="relative isolate overflow-hidden bg-brand-navy text-white">
    <img
        src="https://images.unsplash.com/photo-1589578527966-fdac0f44566c?auto=format&fit=crop&w=1800&q=85"
        alt="Patung Lady Justice sebagai simbol hukum"
        class="absolute inset-0 -z-20 h-full w-full object-cover object-[center_38%]"
    >
    <div class="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgba(4,17,40,0.94)_0%,rgba(6,26,61,0.82)_55%,rgba(6,26,61,0.66)_100%)]"></div>

    <div class="mx-auto grid max-w-7xl gap-5 px-5 py-6 sm:px-6 sm:py-7 lg:min-h-[210px] lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center lg:gap-12 lg:px-8 lg:py-4">
        <div class="min-w-0 max-w-3xl">
            <nav aria-label="Breadcrumb" class="flex items-center gap-1.5 text-xs font-medium text-white/60">
                <a href="{{ route('home') }}" class="rounded-sm transition hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">Beranda</a>
                <span aria-hidden="true">/</span>
                <span class="text-white">Editorial</span>
            </nav>
            <p class="mt-2 text-[11px] font-extrabold uppercase tracking-[0.18em] text-brand-amber">Kanal Editorial</p>
            <h1 class="mt-1 max-w-3xl text-balance font-display text-3xl font-black leading-[1.08] tracking-normal text-white sm:text-4xl lg:text-[2.4rem]">Gagasan Hukum untuk Ruang Publik</h1>
            <p class="mt-2 max-w-2xl text-pretty text-base leading-7 text-white/82">Editorial Edulaw menghadirkan analisis hukum, regulasi, dan kebijakan publik yang jernih untuk memperluas pemahaman bersama.</p>
        </div>

        <dl class="grid min-w-[280px] grid-cols-2 overflow-hidden rounded-xl border border-white/15 bg-white/8 backdrop-blur-sm sm:min-w-[330px]">
            <div class="px-5 py-3.5">
                <dt class="text-[11px] font-bold uppercase tracking-[0.11em] text-white/70">Artikel Terbit</dt>
                <dd class="mt-1 font-display text-3xl font-black tabular-nums text-brand-amber">{{ number_format((int) $articleCount, 0, ',', '.') }}</dd>
            </div>
            <div class="border-l border-white/15 px-5 py-3.5">
                <dt class="text-[11px] font-bold uppercase tracking-[0.11em] text-white/70">Kategori Editorial</dt>
                <dd class="mt-1 font-display text-3xl font-black tabular-nums text-white">{{ number_format((int) $categoryCount, 0, ',', '.') }}</dd>
            </div>
        </dl>
    </div>
</section>
