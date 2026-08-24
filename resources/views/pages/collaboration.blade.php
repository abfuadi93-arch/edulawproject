@extends('layouts.app')

@section('title', 'Kolaborasi Hukum dan Riset | Edulaw Project')
@section('meta_description', 'Jalin kolaborasi bersama Edulaw Project untuk riset, publikasi, edukasi, program, dan kegiatan hukum yang memberi dampak bagi masyarakat.')

@section('content')
@php
    $collaborationScopes = [
        [
            'title' => 'Program Edukasi',
            'description' => 'Kerja sama kelas, pelatihan, diskusi, atau program literasi hukum untuk kampus, komunitas, dan lembaga.',
        ],
        [
            'title' => 'Diskusi Publik',
            'description' => 'Kolaborasi forum, seminar, webinar, dan ruang dialog mengenai isu hukum, konstitusi, dan kebijakan publik.',
        ],
        [
            'title' => 'Riset & Publikasi',
            'description' => 'Pengembangan kajian, policy brief, publikasi digital, dan diseminasi pengetahuan hukum.',
        ],
        [
            'title' => 'Kampanye Literasi',
            'description' => 'Produksi konten edukatif, multimedia, dan kampanye publik untuk memperluas pemahaman hukum masyarakat.',
        ],
    ];

    $steps = [
        [
            'title' => 'Sampaikan Usulan',
            'description' => 'Isi formulir kolaborasi dengan informasi singkat mengenai instansi, kebutuhan, dan bentuk kerja sama yang diharapkan.',
        ],
        [
            'title' => 'Diskusi Kebutuhan',
            'description' => 'Tim Edulaw Project akan meninjau usulan dan menghubungi Anda untuk memahami konteks, tujuan, serta kebutuhan program.',
        ],
        [
            'title' => 'Rancang Kegiatan',
            'description' => 'Konsep kolaborasi disusun bersama agar sesuai dengan sasaran peserta, format kegiatan, dan dampak yang diharapkan.',
        ],
        [
            'title' => 'Pelaksanaan',
            'description' => 'Program dijalankan secara kolaboratif dengan dokumentasi, publikasi, dan evaluasi sesuai kebutuhan kerja sama.',
        ],
    ];

@endphp

<main class="overflow-x-clip bg-[#f7f8fa] text-brand-ink">
    {{-- Hero --}}
    <x-shared.primary-hero
        title="Bangun Literasi Hukum Bersama Edulaw Project"
        eyebrow="Kolaborasi"
        description="Edulaw Project membuka ruang kerja sama dengan kampus, komunitas, lembaga, organisasi mahasiswa, pusat studi, dan mitra strategis untuk mengembangkan edukasi hukum yang inklusif, relevan, dan berdampak."
        :background-image="asset('images/hero/hero-edulaw.jpg')"
        background-alt="Diskusi kolaborasi literasi hukum Edulaw Project"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Kolaborasi'],
        ]"
        panel-label="Aksi kolaborasi"
    >
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
            <a
                href="#form-kolaborasi"
                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-brand-amber px-5 py-3 text-sm font-black text-brand-navy transition hover:bg-[#ffd670]"
            >
                Ajukan Kolaborasi
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>

            <a
                href="{{ url('/program') }}"
                class="inline-flex min-h-11 items-center justify-center rounded-lg border border-white/20 bg-white/10 px-5 py-3 text-sm font-black text-white backdrop-blur transition hover:bg-white/15"
            >
                Lihat Program
            </a>
        </div>
    </x-shared.primary-hero>

    {{-- Scope --}}
    <section class="bg-white py-9 sm:py-10 lg:py-11">
        <div class="section-shell">
            <div class="max-w-3xl">
                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-brand-teal">
                    Bentuk Kolaborasi
                </p>

                <h2 class="mt-2 font-display text-2xl font-black leading-tight text-brand-navy sm:text-3xl">
                    Ruang kerja sama yang dapat dikembangkan bersama.
                </h2>

                <p class="mt-2 max-w-3xl text-base leading-7 text-slate-600">
                    Kolaborasi dapat disesuaikan dengan kebutuhan mitra, sasaran peserta,
                    format kegiatan, dan tujuan pengembangan literasi hukum.
                </p>
            </div>

            <div class="mt-6 grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                @foreach ($collaborationScopes as $index => $scope)
                    <article class="rounded-[14px] bg-[#f7f8fa] p-5">
                        <div class="grid size-9 place-items-center rounded-full bg-brand-amber-soft text-[10px] font-black text-brand-navy">
                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                        </div>

                        <h3 class="mt-4 text-lg font-black leading-tight text-brand-navy">
                            {{ $scope['title'] }}
                        </h3>

                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            {{ $scope['description'] }}
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Process --}}
    <section class="py-9 sm:py-10 lg:py-11">
        <div class="section-shell">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-brand-teal">
                    Alur Kolaborasi
                </p>

                <h2 class="mt-2 font-display text-2xl font-black leading-tight text-brand-navy sm:text-3xl">
                    Dari usulan hingga pelaksanaan program.
                </h2>

                <p class="mt-2 max-w-3xl text-base leading-7 text-slate-600">
                    Setiap usulan kolaborasi akan ditinjau secara bertahap agar kegiatan yang
                    dirancang sesuai dengan kebutuhan mitra dan arah pengembangan Edulaw Project.
                </p>
            </div>

            <div class="mt-6 grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                @foreach ($steps as $index => $step)
                    <article class="rounded-[14px] bg-white p-5">
                        <div class="grid size-9 place-items-center rounded-full bg-brand-teal-soft text-[10px] font-black text-brand-navy">
                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                        </div>

                        <div class="mt-4">
                            <h3 class="text-lg font-black text-brand-navy">
                                {{ $step['title'] }}
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                {{ $step['description'] }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Form Section --}}
    <section id="form-kolaborasi" class="scroll-mt-20 bg-white py-9 sm:py-10 lg:py-11">
        <div class="section-shell grid gap-5 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
            <aside>
                <div class="sticky top-24 rounded-[14px] bg-brand-navy p-6 text-white sm:p-7">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-amber">
                        Ajukan Kolaborasi
                    </p>

                    <h2 class="mt-2 font-display text-2xl font-black leading-tight text-white sm:text-3xl">
                        Ceritakan gagasan kerja sama Anda.
                    </h2>

                    <p class="mt-4 text-sm font-medium leading-6 text-white/85">
                        Gunakan formulir ini untuk menyampaikan usulan program, diskusi,
                        riset, publikasi, pelatihan, atau bentuk kerja sama lain yang relevan.
                    </p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                        <div class="rounded-[12px] bg-white/8 p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-brand-amber">
                                Respons
                            </p>
                            <p class="mt-2 text-sm font-semibold leading-6 text-white/75">
                                Tim Edulaw Project akan meninjau usulan dan menghubungi Anda melalui kontak yang tersedia.
                            </p>
                        </div>

                        <div class="rounded-[12px] bg-white/8 p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-brand-amber">
                                Kontak Cepat
                            </p>
                            <a
                                href="https://wa.me/6281529927677"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-3 inline-flex items-center gap-2 text-sm font-black text-white transition hover:text-brand-amber"
                            >
                                Hubungi via WhatsApp
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </aside>

            <div>
                <x-forms.collaboration-form />
            </div>
        </div>
    </section>

    {{-- Closing CTA --}}
    <x-shared.cta-collaboration
        eyebrow="Langkah Berikutnya"
        title="Mari susun format kolaborasi yang paling tepat."
        title-class="lg:whitespace-nowrap"
        body="Ceritakan kebutuhan, sasaran peserta, dan bentuk kegiatan yang Anda bayangkan agar Edulaw dapat menyiapkan respons yang relevan."
        :primary-url="route('collaboration.index').'#form-kolaborasi'"
        primary-label="Isi Form Kolaborasi"
        :secondary-url="route('contact.index')"
        secondary-label="Hubungi Kami"
    />
</main>
@endsection
