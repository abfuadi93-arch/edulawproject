@extends('layouts.app')

@section('title', 'Syarat dan Ketentuan | Edulaw Project')
@section('meta_description', 'Baca syarat dan ketentuan penggunaan situs Edulaw Project, termasuk hak, tanggung jawab, penggunaan konten, serta batasan layanan pengguna.')

@section('content')
@php
    $updatedAt = '11 Juni 2026';

    $summaryItems = [
        [
            'title' => 'Penggunaan Website',
            'description' => 'Website Edulaw Project digunakan untuk mengakses informasi, edukasi hukum, publikasi, program, multimedia, opportunities, dan kanal kolaborasi.',
        ],
        [
            'title' => 'Konten & Informasi',
            'description' => 'Konten disusun untuk tujuan literasi hukum dan tidak dimaksudkan sebagai nasihat hukum khusus untuk perkara tertentu.',
        ],
        [
            'title' => 'Tautan Eksternal',
            'description' => 'Beberapa halaman dapat memuat tautan ke platform pihak ketiga. Pengguna perlu memeriksa ketentuan pada situs tujuan.',
        ],
    ];

    $sections = [
        [
            'title' => '1. Pendahuluan',
            'content' => [
                'Syarat dan Ketentuan ini mengatur penggunaan website Edulaw Project, termasuk akses terhadap konten edukasi, editorial, riset dan publikasi, program, multimedia, opportunities, formulir kontak, serta formulir kolaborasi.',
                'Dengan mengakses dan menggunakan website Edulaw Project, pengguna dianggap telah membaca, memahami, dan menyetujui Syarat dan Ketentuan ini.',
            ],
        ],
        [
            'title' => '2. Tujuan Website',
            'content' => [
                'Website Edulaw Project dikembangkan sebagai platform literasi hukum di ruang digital yang menghadirkan edukasi hukum, kajian kebijakan, publikasi, program pengembangan kapasitas, multimedia, opportunities, dan ruang kolaborasi.',
                'Informasi yang tersedia pada website ini ditujukan untuk tujuan edukasi, pembelajaran, diseminasi pengetahuan, dan pengembangan literasi hukum publik.',
            ],
        ],
        [
            'title' => '3. Penggunaan Konten',
            'content' => [
                'Pengguna dapat membaca, membagikan, dan menggunakan konten Edulaw Project untuk tujuan edukasi, akademik, diskusi publik, atau kegiatan non-komersial dengan tetap mencantumkan sumber secara layak.',
                'Pengguna tidak diperkenankan menggunakan konten Edulaw Project untuk tujuan yang menyesatkan, melanggar hukum, merugikan pihak lain, atau mengubah konteks informasi sehingga menimbulkan pemahaman yang keliru.',
            ],
        ],
        [
            'title' => '4. Bukan Nasihat Hukum',
            'content' => [
                'Konten pada website Edulaw Project disusun untuk tujuan literasi dan edukasi hukum umum. Konten tersebut tidak dimaksudkan sebagai nasihat hukum khusus, pendapat hukum resmi, atau pengganti konsultasi dengan advokat atau ahli hukum terkait perkara tertentu.',
                'Pengguna yang membutuhkan nasihat hukum untuk kasus konkret disarankan berkonsultasi dengan pihak yang memiliki kompetensi profesional sesuai kebutuhan.',
            ],
        ],
        [
            'title' => '5. Akurasi dan Pembaruan Informasi',
            'content' => [
                'Edulaw Project berupaya menyajikan informasi secara akurat, relevan, dan mudah dipahami. Namun, perkembangan hukum, regulasi, kebijakan, jadwal program, dan informasi opportunities dapat berubah sewaktu-waktu.',
                'Pengguna disarankan memeriksa sumber resmi, dokumen hukum, atau laman penyelenggara terkait sebelum mengambil keputusan berdasarkan informasi yang tersedia di website ini.',
            ],
        ],
        [
            'title' => '6. Opportunities dan Informasi Pihak Ketiga',
            'content' => [
                'Kanal opportunities dapat memuat informasi mengenai beasiswa, magang, lowongan hukum, kompetisi, call for papers, konferensi, atau kesempatan pengembangan lain yang berasal dari berbagai penyelenggara.',
                'Edulaw Project tidak bertanggung jawab atas perubahan jadwal, persyaratan, seleksi, keputusan penyelenggara, atau konsekuensi lain yang timbul dari penggunaan informasi opportunities. Pengguna wajib memeriksa informasi resmi pada laman penyelenggara.',
            ],
        ],
        [
            'title' => '7. Formulir Kontak dan Kolaborasi',
            'content' => [
                'Pengguna dapat mengirimkan pesan, pertanyaan, atau usulan kerja sama melalui formulir yang tersedia pada website.',
                'Dengan mengirimkan formulir, pengguna menyatakan bahwa informasi yang diberikan adalah benar dan dapat digunakan oleh Edulaw Project untuk kebutuhan komunikasi, tindak lanjut, atau pengelolaan usulan kolaborasi.',
            ],
        ],
        [
            'title' => '8. Tautan ke Situs Eksternal',
            'content' => [
                'Website Edulaw Project dapat memuat tautan ke situs eksternal seperti YouTube, Instagram, Google Photos, laman penyelenggara opportunities, atau platform pihak ketiga lainnya.',
                'Edulaw Project tidak mengendalikan dan tidak bertanggung jawab atas isi, kebijakan, keamanan, atau praktik pengelolaan data pada situs eksternal tersebut.',
            ],
        ],
        [
            'title' => '9. Hak Kekayaan Intelektual',
            'content' => [
                'Seluruh desain, teks, struktur konten, identitas visual, dan materi lain pada website Edulaw Project dilindungi sesuai ketentuan hukum yang berlaku, kecuali dinyatakan lain.',
                'Penggunaan ulang konten untuk kepentingan komersial, publikasi ulang berskala besar, atau modifikasi substansial memerlukan izin dari Edulaw Project.',
            ],
        ],
        [
            'title' => '10. Perubahan Layanan',
            'content' => [
                'Edulaw Project dapat memperbarui, mengubah, menambah, atau menghapus konten, fitur, halaman, dan layanan website dari waktu ke waktu sesuai kebutuhan pengembangan platform.',
                'Edulaw Project juga dapat memperbarui Syarat dan Ketentuan ini dengan mencantumkan tanggal pembaruan terakhir pada halaman ini.',
            ],
        ],
        [
            'title' => '11. Batasan Tanggung Jawab',
            'content' => [
                'Edulaw Project tidak bertanggung jawab atas kerugian langsung maupun tidak langsung yang timbul dari penggunaan informasi pada website ini tanpa verifikasi lebih lanjut atau tanpa konsultasi profesional yang diperlukan.',
                'Pengguna bertanggung jawab atas keputusan, tindakan, atau penggunaan informasi yang diperoleh dari website Edulaw Project.',
            ],
        ],
        [
            'title' => '12. Kontak',
            'content' => [
                'Pertanyaan mengenai Syarat dan Ketentuan ini dapat disampaikan melalui halaman Kontak atau kanal komunikasi resmi Edulaw Project.',
            ],
        ],
    ];
@endphp

<x-legal.document-page
    title="Syarat & Ketentuan"
    eyebrow="Ketentuan Layanan"
    description="Ketentuan penggunaan website Edulaw Project, termasuk akses konten, penggunaan informasi, tautan eksternal, dan kanal komunikasi."
    :updated-at="$updatedAt"
    :summary-items="$summaryItems"
    :sections="$sections"
    background-image="https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=1800&q=85"
    background-alt="Syarat dan ketentuan penggunaan website Edulaw Project"
    aside-eyebrow="Catatan Penggunaan"
    aside-title="Gunakan informasi secara bijak."
    aside-body="Konten Edulaw Project disusun untuk literasi hukum umum dan tidak menggantikan konsultasi profesional untuk kasus konkret."
/>
@endsection
