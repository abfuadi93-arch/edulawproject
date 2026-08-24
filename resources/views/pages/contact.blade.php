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

<main class="overflow-x-clip bg-[#f7f8fa] text-brand-ink">
    {{-- Hero --}}
    <x-shared.primary-hero
        title="Terhubung dengan Edulaw Project"
        eyebrow="Kontak"
        description="Sampaikan pertanyaan, kebutuhan informasi, usulan kerja sama, atau komunikasi lainnya melalui kanal resmi Edulaw Project."
        background-image="https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=1800&q=85"
        background-alt="Kanal komunikasi Edulaw Project"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Kontak'],
        ]"
        :highlights="collect($contactChannels)->pluck('title')->all()"
        panel-label="Aksi kontak"
    >
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
            <a
                href="#form-kontak"
                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-brand-amber px-5 py-3 text-sm font-black text-brand-navy transition hover:bg-[#ffd670]"
            >
                Kirim Pesan
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>

            <a
                href="{{ url('/kolaborasi') }}"
                class="inline-flex min-h-11 items-center justify-center rounded-lg border border-white/20 bg-white/10 px-5 py-3 text-sm font-black text-white backdrop-blur transition hover:bg-white/15"
            >
                Ajukan Kolaborasi
            </a>
        </div>
    </x-shared.primary-hero>

    {{-- Contact Channels --}}
    <section class="bg-white py-9 sm:py-10 lg:py-11">
        <div class="section-shell">
            <div class="max-w-3xl">
                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-brand-teal">
                    Kanal Resmi
                </p>

                <h2 class="mt-2 font-display text-2xl font-black leading-tight text-brand-navy sm:text-3xl">
                    Pilih kanal komunikasi yang paling sesuai.
                </h2>

                <p class="mt-2 max-w-3xl text-base leading-7 text-slate-600">
                    Edulaw Project menyediakan beberapa kanal untuk memudahkan komunikasi
                    terkait program, publikasi, kolaborasi, dan informasi umum.
                </p>
            </div>

            <div class="mt-6 grid gap-3 md:grid-cols-3">
                @foreach ($contactChannels as $channel)
                    @if ($channel['icon'] === 'mail')
                        <!--email_off-->
                    @endif
                    <article class="rounded-[14px] bg-[#f7f8fa] p-5 sm:p-6">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="grid size-10 shrink-0 place-items-center rounded-full bg-brand-teal-soft text-brand-navy">
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

                            <div class="min-w-0">
                                <h3 class="text-xl font-black leading-tight text-brand-navy">
                                    {{ $channel['title'] }}
                                </h3>

                                <p class="mt-1 break-words text-sm font-bold leading-5 text-brand-ink">
                                    {{ $channel['value'] }}
                                </p>
                            </div>
                        </div>

                        <p class="mt-4 text-sm leading-6 text-slate-600">
                            {{ $channel['description'] }}
                        </p>

                        <a
                            href="{{ $channel['url'] }}"
                            target="{{ str_starts_with($channel['url'], 'http') ? '_blank' : '_self' }}"
                            rel="{{ str_starts_with($channel['url'], 'http') ? 'noopener noreferrer' : '' }}"
                            class="mt-5 inline-flex items-center gap-2 text-sm font-black text-brand-navy transition hover:text-brand-teal"
                        >
                            {{ $channel['label'] }}
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </article>
                    @if ($channel['icon'] === 'mail')
                        <!--/email_off-->
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    {{-- Form Section --}}
    <section id="form-kontak" class="scroll-mt-20 py-9 sm:py-10 lg:py-11">
        <div class="section-shell grid gap-5 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
            <aside>
                <div class="sticky top-24 rounded-[14px] bg-brand-navy p-6 text-white sm:p-7">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-amber">
                        Kirim Pesan
                    </p>

                    <h2 class="mt-2 font-display text-2xl font-black leading-tight text-white sm:text-3xl">
                        Sampaikan pertanyaan Anda kepada Edulaw.
                    </h2>

                    <p class="mt-4 text-sm font-medium leading-6 text-white/85">
                        Gunakan formulir ini untuk pertanyaan umum, informasi program,
                        publikasi, multimedia, kerja sama, atau komunikasi lainnya.
                    </p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                        <div class="rounded-[12px] bg-white/8 p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-brand-amber">
                                Respons
                            </p>
                            <p class="mt-2 text-sm font-semibold leading-6 text-white/75">
                                Pesan akan ditinjau oleh pengelola Edulaw Project dan direspons melalui email atau kanal kontak yang tersedia.
                            </p>
                        </div>

                        <div class="rounded-[12px] bg-white/8 p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-brand-amber">
                                Kolaborasi
                            </p>
                            <a
                                href="{{ url('/kolaborasi') }}"
                                class="mt-3 inline-flex items-center gap-2 text-sm font-black text-white transition hover:text-brand-amber"
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
    <section class="bg-white py-9 sm:py-10 lg:py-11">
        <div class="section-shell grid gap-6 lg:grid-cols-[0.42fr_0.58fr] lg:items-start">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-brand-teal">
                    Pertanyaan Umum
                </p>

                <h2 class="mt-2 font-display text-2xl font-black leading-tight text-brand-navy sm:text-3xl">
                    Informasi sebelum menghubungi kami.
                </h2>

                <p class="mt-2 text-base leading-7 text-slate-600">
                    Beberapa informasi berikut dapat membantu Anda menentukan kanal komunikasi
                    atau jenis pesan yang ingin disampaikan.
                </p>
            </div>

            <div class="divide-y divide-slate-200 rounded-[14px] bg-[#f7f8fa] px-5 sm:px-6">
                @foreach ($faqItems as $item)
                    <article class="py-5 first:pt-5 last:pb-5">
                        <h3 class="text-lg font-black leading-snug text-brand-navy">
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
