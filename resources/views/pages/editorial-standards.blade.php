@extends('layouts.app')

@section('title', 'Standar Editorial | Edulaw Project')
@section('meta_description', 'Pelajari prinsip, standar sumber, proses penyuntingan, koreksi, konflik kepentingan, dan penggunaan teknologi dalam publikasi Edulaw Project.')
@section('canonical_url', route('editorial-standards'))

@section('content')
@php
    $summaryItems = [
        ['title' => 'Akurat dan Dapat Ditelusuri', 'description' => 'Tulisan diarahkan untuk menggunakan fakta, konteks, dan sumber yang dapat diperiksa pembaca.'],
        ['title' => 'Independen dan Relevan', 'description' => 'Pertimbangan editorial mengutamakan kepentingan pengetahuan publik dan relevansi isu hukum.'],
        ['title' => 'Jelas dan Bertanggung Jawab', 'description' => 'Bahasa disusun agar mudah dipahami tanpa mengurangi ketepatan substansi hukum.'],
    ];

    $sections = [
        [
            'title' => '1. Prinsip Editorial',
            'content' => [
                'Edulaw Project berupaya menerbitkan konten yang akurat, independen, relevan, berimbang, dan mudah dibaca. Prinsip tersebut digunakan sebagai arah kerja dalam memilih topik, memeriksa argumentasi, dan menyajikan pengetahuan hukum kepada publik.',
                'Keberimbangan tidak selalu berarti memberi bobot yang sama kepada setiap klaim. Informasi dinilai berdasarkan kekuatan bukti, konteks hukum, dan kepentingannya bagi pembaca.',
            ],
        ],
        [
            'title' => '2. Standar Sumber',
            'content' => [
                'Sumber yang diutamakan meliputi peraturan perundang-undangan, putusan pengadilan, dokumen pemerintah dan lembaga resmi, jurnal ilmiah, buku akademik, serta sumber primer terpercaya lainnya.',
                'Sumber sekunder dapat digunakan untuk memperjelas konteks, tetapi klaim hukum dan faktual sedapat mungkin ditautkan atau dirujukkan pada dokumen yang paling otoritatif dan dapat ditelusuri.',
            ],
        ],
        [
            'title' => '3. Proses Editorial',
            'content' => [
                'Alur editorial pada umumnya mencakup penulisan, pemeriksaan substansi, penyuntingan, publikasi, serta pembaruan atau koreksi apabila diperlukan. Kedalaman pemeriksaan dapat disesuaikan dengan jenis dan kompleksitas konten.',
                'Penulis dan redaksi bertanggung jawab menjaga keterbacaan, ketepatan rujukan, serta pemisahan yang jelas antara fakta, analisis, dan pendapat.',
            ],
        ],
        [
            'title' => '4. Konflik Kepentingan',
            'content' => [
                'Penulis dan pihak yang terlibat dalam proses editorial diharapkan mengungkapkan hubungan atau kepentingan yang dapat memengaruhi independensi tulisan. Redaksi dapat meminta penjelasan tambahan atau menyesuaikan penanganan naskah untuk menjaga kepercayaan pembaca.',
            ],
        ],
        [
            'title' => '5. Pembaruan dan Koreksi',
            'content' => [
                'Konten dapat diperbarui ketika terdapat perkembangan hukum, informasi baru, atau kesalahan yang perlu diperbaiki. Perubahan substantif dapat disertai keterangan pembaruan agar konteks tulisan tetap jelas.',
                'Ketentuan lebih lanjut tersedia pada halaman Kebijakan Koreksi Edulaw Project.',
            ],
        ],
        [
            'title' => '6. Penggunaan Teknologi dan AI',
            'content' => [
                'Teknologi, termasuk alat bantu berbasis kecerdasan artifisial, dapat digunakan untuk mendukung pekerjaan seperti riset awal, transkripsi, pengorganisasian informasi, atau pemeriksaan bahasa.',
                'Penggunaan alat bantu tidak memindahkan tanggung jawab substantif. Penulis dan/atau redaksi tetap bertanggung jawab atas akurasi, sumber, argumentasi, serta keputusan publikasi.',
            ],
        ],
    ];
@endphp

<x-legal.document-page
    title="Standar Editorial"
    eyebrow="Editorial Edulaw"
    description="Prinsip dan praktik yang memandu Edulaw Project dalam menulis, memeriksa, menyunting, menerbitkan, dan memperbarui konten hukum."
    updated-at="27 Agustus 2026"
    :summary-items="$summaryItems"
    :sections="$sections"
    :background-image="asset('images/hero/hero-edulaw.jpg')"
    background-alt="Proses penulisan dan penyuntingan publikasi Edulaw Project"
    aside-eyebrow="Koreksi"
    aside-title="Menemukan kekeliruan pada konten?"
    aside-body="Sampaikan temuan melalui kanal kontak agar redaksi dapat meninjau sumber dan konteksnya."
/>
@endsection
