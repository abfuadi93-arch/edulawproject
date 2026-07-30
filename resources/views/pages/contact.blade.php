@extends('layouts.app')

@section('title', 'Kontak dan Informasi | Edulaw Project')
@section('meta_description', 'Hubungi Edulaw Project untuk pertanyaan, informasi program, kerja sama riset, publikasi, media, dan kebutuhan kolaborasi di bidang hukum.')

@section('content')
@php
    $contactChannels = [
        [
            'title' => 'Email',
            'description' => 'Untuk pertanyaan umum, kerja sama, publikasi, dan komunikasi resmi.',
            'value' => 'edulawproject@gmail.com',
            'url' => 'mailto:edulawproject@gmail.com',
            'label' => 'Kirim Email',
            'icon' => 'mail',
        ],
        [
            'title' => 'WhatsApp',
            'description' => 'Untuk komunikasi cepat terkait program, kolaborasi, atau informasi kegiatan.',
            'value' => '+62 815-2992-7677',
            'url' => 'https://wa.me/6281529927677',
            'label' => 'Chat WhatsApp',
            'icon' => 'phone',
        ],
        [
            'title' => 'Instagram',
            'description' => 'Ikuti pembaruan konten, dokumentasi, dan informasi kegiatan Edulaw Project.',
            'value' => '@edulaw.project',
            'url' => 'https://www.instagram.com/edulaw.project',
            'label' => 'Buka Instagram',
            'icon' => 'social',
        ],
    ];

    $faqItems = [
        [
            'question' => 'Apakah Edulaw Project menerima kerja sama kegiatan?',
            'answer' => 'Ya. Edulaw Project terbuka untuk kerja sama program edukasi, diskusi publik, riset, publikasi, pelatihan, dan kampanye literasi hukum.',
        ],
        [
            'question' => 'Apakah saya bisa menghubungi Edulaw untuk informasi program?',
            'answer' => 'Bisa. Silakan gunakan formulir kontak atau WhatsApp untuk menanyakan informasi program yang tersedia.',
        ],
        [
            'question' => 'Apakah Edulaw menerima pengiriman artikel dari publik?',
            'answer' => 'Untuk saat ini, konten website disusun dan dikurasi oleh Edulaw Project. Kanal kontak dapat digunakan untuk kerja sama atau kolaborasi kelembagaan.',
        ],
        [
            'question' => 'Berapa lama pesan akan direspons?',
            'answer' => 'Pesan akan ditinjau dan direspons sesuai ketersediaan pengelola Edulaw Project. Untuk kebutuhan cepat, gunakan kanal WhatsApp.',
        ],
    ];
@endphp

<main class="bg-brand-paper">
    {{-- Hero --}}
    <x-shared.page-header
        title="Terhubung dengan Edulaw Project"
        :compact="true"
        eyebrow="Kontak"
        description="Sampaikan pertanyaan, kebutuhan informasi, usulan kerja sama, atau komunikasi lainnya melalui kanal resmi Edulaw Project."
        background-image="https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=1800&q=85"
        background-alt="Kanal komunikasi Edulaw Project"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Kontak'],
        ]"
    >
        <div class="flex flex-col gap-3 sm:flex-row">
            <a
                href="#form-kontak"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-silver px-6 py-3.5 text-sm font-extrabold text-brand-ink shadow-sm transition hover:bg-white"
            >
                Kirim Pesan
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>

            <a
                href="{{ url('/kolaborasi') }}"
                class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/10 px-6 py-3.5 text-sm font-extrabold text-white backdrop-blur transition hover:bg-white/15"
            >
                Ajukan Kolaborasi
            </a>
        </div>
    </x-shared.page-header>

    {{-- Contact Channels --}}
    <section class="py-14 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                    Kanal Resmi
                </p>

                <h2 class="mt-4 text-3xl font-extrabold leading-tight tracking-tight text-brand-ink sm:text-4xl">
                    Pilih kanal komunikasi yang paling sesuai.
                </h2>

                <p class="mt-4 text-base leading-7 text-slate-600">
                    Edulaw Project menyediakan beberapa kanal untuk memudahkan komunikasi
                    terkait program, publikasi, kolaborasi, dan informasi umum.
                </p>
            </div>

            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ($contactChannels as $channel)
                    <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-brand-silver hover:shadow-xl hover:shadow-slate-900/5">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-navy text-white">
                            @if ($channel['icon'] === 'mail')
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M4 6h16v12H4V6Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @elseif ($channel['icon'] === 'phone')
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M7 4h3l1.5 4-2 1.2c.9 1.9 2.4 3.4 4.3 4.3l1.2-2L19 13v3c0 1.7-1.3 3-3 3A11 11 0 0 1 5 8c0-1.7 1.3-3 3-3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @else
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <rect x="5" y="5" width="14" height="14" rx="4" stroke="currentColor" stroke-width="1.8"/>
                                    <circle cx="12" cy="12" r="3.2" stroke="currentColor" stroke-width="1.8"/>
                                    <circle cx="16.5" cy="7.5" r="0.8" fill="currentColor"/>
                                </svg>
                            @endif
                        </div>

                        <h3 class="mt-6 text-xl font-extrabold text-brand-ink">
                            {{ $channel['title'] }}
                        </h3>

                        <p class="mt-2 text-sm font-bold text-brand-ink">
                            {{ $channel['value'] }}
                        </p>

                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            {{ $channel['description'] }}
                        </p>

                        <a
                            href="{{ $channel['url'] }}"
                            target="{{ str_starts_with($channel['url'], 'http') ? '_blank' : '_self' }}"
                            rel="{{ str_starts_with($channel['url'], 'http') ? 'noopener noreferrer' : '' }}"
                            class="mt-6 inline-flex items-center gap-2 text-sm font-extrabold text-brand-ink transition hover:text-brand-ink"
                        >
                            {{ $channel['label'] }}
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Form Section --}}
    <section id="form-kontak" class="bg-white py-14 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.85fr_1.15fr] lg:px-8">
            <aside>
                <div class="sticky top-24 rounded-[2rem] bg-brand-navy p-6 text-white shadow-xl shadow-slate-900/10 sm:p-8">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-amber">
                        Kirim Pesan
                    </p>

                    <h2 class="mt-4 text-3xl font-extrabold leading-tight tracking-tight text-white sm:text-4xl">
                        Sampaikan pertanyaan Anda kepada Edulaw.
                    </h2>

                    <p class="mt-4 text-sm font-medium leading-6 text-white/85">
                        Gunakan formulir ini untuk pertanyaan umum, informasi program,
                        publikasi, multimedia, kerja sama, atau komunikasi lainnya.
                    </p>

                    <div class="mt-8 space-y-4">
                        <div class="rounded-2xl border border-white/15 bg-white p-5 text-brand-ink shadow-sm shadow-slate-950/10">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-brand-navy/70">
                                Respons
                            </p>
                            <p class="mt-2 text-sm font-semibold leading-6 text-slate-700">
                                Pesan akan ditinjau oleh pengelola Edulaw Project dan direspons melalui email atau kanal kontak yang tersedia.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/15 bg-white p-5 text-brand-ink shadow-sm shadow-slate-950/10">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-brand-navy/70">
                                Kolaborasi
                            </p>
                            <a
                                href="{{ url('/kolaborasi') }}"
                                class="mt-3 inline-flex items-center gap-2 text-sm font-extrabold text-brand-navy transition hover:text-brand-teal"
                            >
                                Ajukan kerja sama
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </aside>

            <div>
                <x-forms.contact-form />
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section class="py-14 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.8fr_1.2fr] lg:px-8">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                    Pertanyaan Umum
                </p>

                <h2 class="mt-4 text-3xl font-extrabold leading-tight tracking-tight text-brand-ink sm:text-4xl">
                    Informasi sebelum menghubungi kami.
                </h2>

                <p class="mt-5 text-base leading-7 text-slate-600">
                    Beberapa informasi berikut dapat membantu Anda menentukan kanal komunikasi
                    atau jenis pesan yang ingin disampaikan.
                </p>
            </div>

            <div class="space-y-4">
                @foreach ($faqItems as $item)
                    <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-extrabold leading-snug text-brand-ink">
                            {{ $item['question'] }}
                        </h3>

                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            {{ $item['answer'] }}
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Closing CTA --}}
    <x-shared.cta-collaboration
        eyebrow="Mulai Kolaborasi"
        title="Sudah siap merancang agenda bersama Edulaw?"
        body="Jika kebutuhan Anda sudah jelas, lanjutkan ke formulir kolaborasi agar tim kami dapat menindaklanjuti dengan konteks yang lengkap."
        :primary-url="route('collaboration.index')"
        primary-label="Ajukan Kolaborasi"
        :secondary-url="route('programs.index')"
        secondary-label="Lihat Program"
    />
</main>
@endsection
