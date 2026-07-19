@props(['archiveUrl' => null])

<section class="relative isolate min-h-[300px] overflow-hidden bg-brand-navy text-white sm:min-h-[320px] lg:min-h-[360px]">
    <img
        src="https://images.unsplash.com/photo-1589578527966-fdac0f44566c?auto=format&fit=crop&w=1800&q=85"
        alt="Patung Lady Justice sebagai simbol hukum"
        class="absolute inset-0 -z-20 h-full w-full object-cover object-[center_38%]"
    >
    <div class="absolute inset-0 -z-10 bg-brand-navy/55"></div>
    <div class="absolute inset-0 -z-10 bg-linear-to-r from-[#041128]/98 via-[#061a3d]/82 to-[#061a3d]/28"></div>

    <div class="mx-auto grid min-h-[300px] max-w-7xl items-center gap-8 px-5 py-9 sm:min-h-[320px] sm:px-6 sm:py-10 lg:min-h-[360px] lg:grid-cols-[1.1fr_0.9fr] lg:px-8">
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
            <p class="mt-5 max-w-2xl text-pretty text-sm leading-7 text-white/78 sm:text-base sm:leading-8">
                Editorial Edulaw menghadirkan analisis hukum, regulasi, dan kebijakan publik yang jernih untuk memperluas pemahaman bersama.
            </p>
        </div>

        <div class="min-w-0 lg:pl-8">
            <div class="rounded-2xl border border-white/16 bg-white/10 p-5 shadow-2xl shadow-black/20 backdrop-blur sm:p-6">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-brand-amber">Fokus Editorial</p>
                <p class="mt-3 text-sm leading-7 text-white/78">
                    Ikuti artikel terbaru tentang hukum publik, regulasi, dan kebijakan yang berdampak pada masyarakat.
                </p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-white/82">Regulasi</span>
                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-white/82">Kebijakan Publik</span>
                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-white/82">Literasi Hukum</span>
                </div>
                <div class="mt-5 flex flex-col gap-3 sm:flex-row lg:flex-col xl:flex-row">
                    <a href="#editorial-terbaru" class="inline-flex min-h-11 w-full items-center justify-center rounded-md bg-brand-amber px-5 text-sm font-bold text-brand-ink transition hover:bg-[#d99a25] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
                        Jelajahi Artikel Terbaru
                    </a>
                    <a href="{{ route('collaboration.index') }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-md border border-white/30 bg-white/5 px-5 text-sm font-bold text-white transition hover:border-white/55 hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">
                        Ajukan Kolaborasi
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
