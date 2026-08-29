@extends('layouts.app')

@section('title', 'Kebijakan Koreksi | Edulaw Project')
@section('meta_description', 'Baca kebijakan Edulaw Project mengenai perbaikan kesalahan faktual, pembaruan substantif, catatan koreksi, dan saluran pengaduan pembaca.')
@section('canonical_url', route('corrections-policy'))

@section('content')
@php
    $summaryItems = [
        ['title' => 'Kesalahan Dapat Diperbaiki', 'description' => 'Kekeliruan faktual, penulisan, atau rujukan dapat diperbaiki setelah ditinjau.'],
        ['title' => 'Konteks Tetap Dijaga', 'description' => 'Koreksi tidak digunakan untuk menghilangkan konteks secara menyesatkan.'],
        ['title' => 'Pembaca Dapat Melapor', 'description' => 'Temuan dapat disampaikan melalui halaman Kontak Edulaw Project.'],
    ];

    $sections = [
        [
            'title' => '1. Tujuan Kebijakan',
            'content' => [
                'Kebijakan ini menjadi pedoman umum Edulaw Project dalam menangani kesalahan, pembaruan informasi, dan masukan pembaca. Tujuannya adalah menjaga akurasi tanpa mengaburkan riwayat atau konteks publikasi.',
            ],
        ],
        [
            'title' => '2. Perbaikan Kesalahan Faktual',
            'content' => [
                'Kesalahan faktual dapat diperbaiki setelah redaksi meninjau informasi, sumber, dan konteks yang relevan. Kesalahan teknis kecil seperti ejaan atau format dapat diperbaiki tanpa mengubah maksud tulisan.',
            ],
        ],
        [
            'title' => '3. Perubahan Substantif',
            'content' => [
                'Perubahan yang memengaruhi kesimpulan, argumentasi, angka, kutipan, atau konteks utama dapat diberi catatan pembaruan. Keterangan tersebut membantu pembaca memahami bahwa materi telah ditinjau atau diubah secara berarti.',
            ],
        ],
        [
            'title' => '4. Menjaga Konteks',
            'content' => [
                'Koreksi tidak boleh digunakan untuk menghilangkan konteks secara menyesatkan. Jika suatu bagian perlu dihapus atau ditulis ulang, redaksi mempertimbangkan dampaknya terhadap pemahaman keseluruhan tulisan.',
            ],
        ],
        [
            'title' => '5. Masukan Pembaca',
            'content' => [
                'Pembaca dapat menyampaikan dugaan kekeliruan melalui halaman Kontak dengan menyertakan judul atau URL konten, bagian yang dipersoalkan, alasan koreksi, dan sumber pendukung apabila tersedia.',
                'Setiap masukan akan ditinjau sesuai konteks dan bukti yang tersedia. Pengiriman masukan tidak selalu berarti perubahan akan dilakukan.',
            ],
        ],
        [
            'title' => '6. Kewenangan Redaksi',
            'content' => [
                'Redaksi berhak memperbarui konten untuk menjaga akurasi, relevansi, keamanan, dan kejelasan. Keputusan koreksi mempertimbangkan kepentingan pembaca, integritas arsip, serta perkembangan hukum yang berkaitan.',
            ],
        ],
    ];
@endphp

<x-legal.document-page
    title="Kebijakan Koreksi"
    eyebrow="Akurasi Publikasi"
    description="Cara Edulaw Project menerima, meninjau, dan menerapkan perbaikan untuk menjaga akurasi serta konteks publikasi."
    updated-at="27 Agustus 2026"
    :summary-items="$summaryItems"
    :sections="$sections"
    :background-image="asset('images/hero/hero-edulaw.jpg')"
    background-alt="Peninjauan dan koreksi naskah Edulaw Project"
    aside-eyebrow="Laporkan"
    aside-title="Sampaikan temuan secara jelas."
    aside-body="Sertakan URL, bagian yang perlu ditinjau, dan sumber pendukung melalui halaman Kontak."
/>
@endsection
