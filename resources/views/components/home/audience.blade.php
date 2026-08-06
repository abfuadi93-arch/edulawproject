@php
    $audiences = [
        ['title' => 'Mahasiswa & Pembelajar Hukum', 'description' => 'Materi dan ruang belajar hukum yang terbuka.', 'symbol' => '◆', 'accent' => 'bg-[#fff2cc] text-[#b98213]', 'label' => 'Jelajahi Program', 'url' => '#program-edulaw'],
        ['title' => 'Peneliti & Akademisi', 'description' => 'Pengetahuan hukum berbasis bukti dan kajian.', 'symbol' => '●', 'accent' => 'bg-[#ddf7ed] text-[#168565]', 'label' => 'Jelajahi Riset', 'url' => '#riset-publikasi'],
        ['title' => 'Praktisi & Profesional', 'description' => 'Analisis yang relevan untuk praktik dan kebijakan.', 'symbol' => '■', 'accent' => 'bg-[#e8f1ff] text-[#32689c]', 'label' => 'Baca Editorial', 'url' => '#edulaw-insight'],
        ['title' => 'Masyarakat & Komunitas', 'description' => 'Literasi hukum yang dekat dengan kebutuhan publik.', 'symbol' => '✦', 'accent' => 'bg-[#fde8e2] text-[#a85c4b]', 'label' => 'Lihat Multimedia', 'url' => '#multimedia'],
    ];
@endphp

<section class="bg-white pb-8 pt-2 lg:pb-10" aria-labelledby="home-audience-title" data-home-audience>
    <div class="section-shell">
        <div class="mb-5 flex items-center gap-4">
            <span class="h-px flex-1 bg-slate-200"></span>
            <h2 id="home-audience-title" class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#1f3c69]">Untuk Siapa Edulaw</h2>
            <span class="h-px flex-1 bg-slate-200"></span>
        </div>

        <div class="grid overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_18px_45px_-38px_rgba(15,23,42,.7)] sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($audiences as $audience)
                <article class="border-slate-200 lg:border-r lg:last:border-r-0" data-home-audience-card>
                    <a href="{{ $audience['url'] }}" class="group flex min-h-40 items-start gap-3 p-4 transition hover:bg-slate-50">
                        <span class="grid size-10 shrink-0 place-items-center rounded-full text-sm font-black {{ $audience['accent'] }}" aria-hidden="true">{{ $audience['symbol'] }}</span>
                        <div class="flex min-w-0 flex-1 self-stretch flex-col">
                            <h3 class="text-base font-extrabold leading-snug text-[#142f57]">{{ $audience['title'] }}</h3>
                            <p class="mt-2 flex-1 text-[13px] leading-5 text-slate-500">{{ $audience['description'] }}</p>
                            <span class="mt-3 inline-flex text-xs font-extrabold text-[#1f3c69]">{{ $audience['label'] }} →</span>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>
