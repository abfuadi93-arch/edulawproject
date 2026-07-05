@extends('layouts.app')

@section('title', 'Kebijakan Privasi - Edulaw Project')

@section('content')
@php
    $updatedAt = '11 Juni 2026';

    $summaryItems = [
        [
            'title' => 'Data yang Dikumpulkan',
            'description' => 'Data yang Anda isi melalui formulir kontak atau kolaborasi, seperti nama, email, nomor WhatsApp, instansi, dan pesan.',
        ],
        [
            'title' => 'Tujuan Penggunaan',
            'description' => 'Data digunakan untuk menanggapi pesan, menindaklanjuti usulan kolaborasi, dan mengelola komunikasi terkait Edulaw Project.',
        ],
        [
            'title' => 'Perlindungan Data',
            'description' => 'Edulaw Project berupaya menjaga data yang diberikan agar tidak digunakan di luar kebutuhan komunikasi dan tindak lanjut.',
        ],
    ];

    $sections = [
        [
            'title' => '1. Pendahuluan',
            'content' => [
                'Kebijakan Privasi ini menjelaskan bagaimana Edulaw Project mengelola informasi yang diberikan oleh pengguna melalui website, formulir kontak, formulir kolaborasi, atau kanal komunikasi resmi lainnya.',
                'Dengan mengakses website Edulaw Project atau mengirimkan informasi melalui formulir yang tersedia, pengguna dianggap telah membaca dan memahami Kebijakan Privasi ini.',
            ],
        ],
        [
            'title' => '2. Informasi yang Dapat Dikumpulkan',
            'content' => [
                'Edulaw Project dapat mengumpulkan informasi yang secara sukarela diberikan oleh pengguna, antara lain nama lengkap, alamat email, nomor WhatsApp, nama instansi atau komunitas, jenis kolaborasi, subjek pesan, dan isi pesan.',
                'Website juga dapat memproses informasi teknis dasar seperti alamat IP, jenis perangkat, browser, halaman yang diakses, dan waktu kunjungan untuk kebutuhan keamanan, statistik, dan peningkatan layanan website.',
            ],
        ],
        [
            'title' => '3. Tujuan Penggunaan Informasi',
            'content' => [
                'Informasi yang diberikan digunakan untuk menanggapi pertanyaan, menindaklanjuti usulan kerja sama, mengelola komunikasi, mengirimkan informasi terkait program atau kegiatan, serta meningkatkan kualitas layanan dan konten Edulaw Project.',
                'Edulaw Project tidak menggunakan data pribadi pengguna untuk tujuan yang tidak relevan dengan komunikasi, pengelolaan program, atau pengembangan layanan literasi hukum.',
            ],
        ],
        [
            'title' => '4. Penyimpanan dan Perlindungan Informasi',
            'content' => [
                'Edulaw Project berupaya menjaga informasi yang diberikan pengguna dengan langkah-langkah yang wajar untuk mencegah akses, penggunaan, perubahan, atau pengungkapan yang tidak sah.',
                'Meskipun demikian, pengguna memahami bahwa tidak ada sistem elektronik yang sepenuhnya bebas dari risiko. Karena itu, pengguna diharapkan tidak mengirimkan informasi yang sangat sensitif melalui formulir website.',
            ],
        ],
        [
            'title' => '5. Pembagian Informasi kepada Pihak Lain',
            'content' => [
                'Edulaw Project tidak menjual, menyewakan, atau memperdagangkan data pribadi pengguna kepada pihak lain.',
                'Informasi dapat dibagikan secara terbatas apabila diperlukan untuk menindaklanjuti kolaborasi, memenuhi kewajiban hukum, melindungi keamanan layanan, atau berdasarkan persetujuan pengguna.',
            ],
        ],
        [
            'title' => '6. Tautan ke Situs Pihak Ketiga',
            'content' => [
                'Website Edulaw Project dapat memuat tautan ke situs pihak ketiga, seperti YouTube, Instagram, Google Photos, atau laman resmi penyelenggara opportunities.',
                'Edulaw Project tidak bertanggung jawab atas kebijakan privasi, isi, atau praktik pengelolaan data pada situs pihak ketiga tersebut. Pengguna disarankan membaca kebijakan privasi pada setiap situs yang dikunjungi.',
            ],
        ],
        [
            'title' => '7. Hak Pengguna',
            'content' => [
                'Pengguna dapat menghubungi Edulaw Project untuk meminta klarifikasi, pembaruan, atau penghapusan informasi pribadi yang pernah dikirimkan melalui kanal komunikasi resmi.',
                'Permintaan tersebut akan ditinjau sesuai kebutuhan, ketersediaan data, dan kewajiban pengelolaan informasi yang relevan.',
            ],
        ],
        [
            'title' => '8. Perubahan Kebijakan Privasi',
            'content' => [
                'Edulaw Project dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu untuk menyesuaikan dengan pengembangan website, perubahan layanan, atau kebutuhan hukum yang relevan.',
                'Perubahan akan ditampilkan pada halaman ini dengan mencantumkan tanggal pembaruan terakhir.',
            ],
        ],
        [
            'title' => '9. Kontak',
            'content' => [
                'Pertanyaan mengenai Kebijakan Privasi ini dapat disampaikan melalui halaman Kontak atau melalui kanal resmi Edulaw Project.',
            ],
        ],
    ];
@endphp

<main class="bg-brand-paper">
    {{-- Header --}}
    <x-shared.page-header
        title="Kebijakan Privasi"
        eyebrow="Kebijakan"
        description="Halaman ini menjelaskan bagaimana Edulaw Project mengelola, menggunakan, dan melindungi informasi yang diberikan oleh pengguna melalui website dan kanal komunikasi resmi."
        background-image="https://images.unsplash.com/photo-1563986768494-4dee2763ff3f?auto=format&fit=crop&w=1800&q=85"
        background-alt="Kebijakan privasi dan perlindungan informasi Edulaw Project"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Kebijakan Privasi'],
        ]"
    >
        <div class="edulaw-badge edulaw-badge-lg edulaw-badge-dark normal-case tracking-normal">
            Terakhir diperbarui: {{ $updatedAt }}
        </div>
    </x-shared.page-header>

    {{-- Summary --}}
    <section class="py-12 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($summaryItems as $index => $item)
                    <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-navy text-sm font-extrabold text-white">
                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                        </div>

                        <h2 class="mt-6 text-lg font-extrabold leading-tight text-brand-ink">
                            {{ $item['title'] }}
                        </h2>

                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            {{ $item['description'] }}
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Policy content --}}
    <section class="pb-14 lg:pb-20">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_320px] lg:px-8">
            <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8 lg:p-10">
                <div class="space-y-10">
                    @foreach ($sections as $section)
                        <section>
                            <h2 class="text-2xl font-extrabold leading-tight tracking-tight text-brand-ink">
                                {{ $section['title'] }}
                            </h2>

                            <div class="edulaw-readable mt-4 text-[15px] text-slate-700 sm:text-base">
                                @foreach ($section['content'] as $paragraph)
                                    <p>
                                        {{ $paragraph }}
                                    </p>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            </article>

            {{-- Sidebar --}}
            <aside class="space-y-6">
                <div class="sticky top-24 space-y-6">
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                            Navigasi
                        </p>

                        <div class="mt-5 grid gap-2">
                            @foreach ($sections as $section)
                                <a
                                    href="#"
                                    class="rounded-2xl px-4 py-3 text-sm font-bold text-slate-600 transition hover:bg-brand-paper hover:text-brand-ink"
                                >
                                    {{ $section['title'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <x-shared.cta-card
                        eyebrow="Pertanyaan?"
                        title="Hubungi Edulaw Project."
                        body="Jika Anda memiliki pertanyaan mengenai pengelolaan data atau kebijakan privasi, silakan hubungi kami melalui halaman kontak."
                        :url="url('/kontak')"
                        label="Hubungi Kami"
                    />
                </div>
            </aside>
        </div>
    </section>
</main>
@endsection
