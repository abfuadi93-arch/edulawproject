<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedBlocks();
    }

    private function seedSettings(): void
    {
        $settings = [
            ['group' => 'Identitas', 'key' => 'site.name', 'label' => 'Nama Situs', 'value' => 'Edulaw Project', 'sort_order' => 1],
            ['group' => 'Identitas', 'key' => 'site.meta_title', 'label' => 'Meta Title Default', 'value' => 'Edulaw Project - Platform Literasi Hukum Digital', 'sort_order' => 2],
            ['group' => 'Identitas', 'key' => 'site.meta_description', 'label' => 'Meta Description Default', 'type' => 'textarea', 'value' => 'Edulaw Project adalah platform literasi hukum digital yang menghadirkan edukasi hukum, riset, program, multimedia, opportunities, dan ruang kolaborasi.', 'sort_order' => 3],
            ['group' => 'Identitas', 'key' => 'site.short_description', 'label' => 'Deskripsi Singkat', 'type' => 'textarea', 'value' => 'Platform literasi hukum digital yang menghadirkan edukasi, riset, program, multimedia, dan kanal pengembangan hukum.', 'sort_order' => 4],
            ['group' => 'Identitas', 'key' => 'site.tagline', 'label' => 'Tagline', 'value' => '#TemanBelajarHukumTerbaikmu', 'sort_order' => 5],
            ['group' => 'Identitas', 'key' => 'site.nav_subtitle', 'label' => 'Subtitle Navbar', 'value' => 'Legal Education · Research · Policy', 'sort_order' => 6],
            ['group' => 'Aset', 'key' => 'site.logo', 'label' => 'Logo Navbar', 'type' => 'image', 'value' => 'images/logo/edulaw-icon.png', 'sort_order' => 10],
            ['group' => 'Aset', 'key' => 'site.footer_logo', 'label' => 'Logo Footer', 'type' => 'image', 'value' => 'images/logo/edulaw-logo.png', 'sort_order' => 11],
            ['group' => 'Kontak', 'key' => 'contact.email', 'label' => 'Email', 'type' => 'email', 'value' => 'hello@edulawproject.id', 'sort_order' => 20],
            ['group' => 'Kontak', 'key' => 'contact.whatsapp_label', 'label' => 'Label WhatsApp', 'value' => '0815-2992-7677', 'sort_order' => 21],
            ['group' => 'Kontak', 'key' => 'contact.whatsapp_url', 'label' => 'URL WhatsApp', 'type' => 'url', 'value' => 'https://wa.me/6281529927677', 'sort_order' => 22],
            ['group' => 'Kontak', 'key' => 'contact.location', 'label' => 'Lokasi', 'value' => 'Jakarta, Indonesia', 'sort_order' => 23],
            ['group' => 'Sosial Media', 'key' => 'social.instagram_url', 'label' => 'Instagram', 'type' => 'url', 'value' => 'https://www.instagram.com/edulaw.project', 'sort_order' => 30],
            ['group' => 'Sosial Media', 'key' => 'social.youtube_url', 'label' => 'YouTube', 'type' => 'url', 'value' => 'https://www.youtube.com/@EdulawProject', 'sort_order' => 31],
            ['group' => 'Sosial Media', 'key' => 'social.linkedin_url', 'label' => 'LinkedIn', 'type' => 'url', 'value' => 'https://www.linkedin.com/company/edulaw-project/', 'sort_order' => 32],
        ];

        foreach ($settings as $setting) {
            SiteSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting + ['type' => $setting['type'] ?? 'text', 'is_public' => true]
            );
        }
    }

    private function seedBlocks(): void
    {
        $blocks = [
            ['area' => 'home.hero', 'eyebrow' => 'Equal · Educative · Embrace', 'title' => 'Membangun Literasi Hukum yang Mudah Diakses, Relevan, dan Berdampak', 'body' => 'Edulaw Project menghadirkan edukasi, riset, program, multimedia, dan kanal pengembangan hukum dalam satu platform digital yang terintegrasi.', 'image' => 'images/hero/hero-edulaw.jpg', 'image_alt' => 'Kegiatan literasi hukum Edulaw Project', 'url' => '/insight', 'url_label' => 'Jelajahi Insight', 'meta' => ['secondary_url' => '/program', 'secondary_label' => 'Lihat Program'], 'sort_order' => 1],
            ['area' => 'home.values', 'title' => 'Belajar', 'body' => 'Kuasai konsep hukum secara nyata.', 'icon' => 'book-open', 'accent' => 'bg-white/15 text-white', 'sort_order' => 1],
            ['area' => 'home.values', 'title' => 'Memahami', 'body' => 'Pahami hukum untuk kehidupan publik.', 'icon' => 'scale', 'accent' => 'bg-brand-teal/20 text-brand-teal', 'sort_order' => 2],
            ['area' => 'home.values', 'title' => 'Berkembang', 'body' => 'Kembangkan peran, ciptakan dampak.', 'icon' => 'chart', 'accent' => 'bg-brand-coral/20 text-brand-coral', 'sort_order' => 3],
            ['area' => 'home.audience_intro', 'eyebrow' => 'Siapa yang Kami Layani', 'title' => 'Ruang literasi hukum untuk berbagai kebutuhan.', 'sort_order' => 1],
            ['area' => 'home.audience', 'title' => 'Mahasiswa', 'body' => 'Akses hukum untuk penelitian, penulisan, dan pengembangan diri.', 'icon' => 'cap', 'accent' => 'bg-brand-amber text-brand-black', 'sort_order' => 1],
            ['area' => 'home.audience', 'title' => 'Profesional Hukum', 'body' => 'Perbarui keahlian, temukan perspektif baru.', 'icon' => 'briefcase', 'accent' => 'bg-brand-teal text-brand-ink', 'sort_order' => 2],
            ['area' => 'home.audience', 'title' => 'Masyarakat', 'body' => 'Pahami hak dan kewajiban, ambil bagian dalam perubahan.', 'icon' => 'users', 'accent' => 'bg-brand-coral text-brand-ink', 'sort_order' => 3],
            ['area' => 'home.audience', 'title' => 'Pembuat Kebijakan', 'body' => 'Dapatkan data dan perspektif hukum yang kredibel.', 'icon' => 'building', 'accent' => 'bg-brand-sky text-white', 'sort_order' => 4],
            ['area' => 'shared.cta', 'eyebrow' => 'Kolaborasi', 'title' => 'Bangun ruang literasi hukum bersama Edulaw Project.', 'body' => 'Edulaw Project terbuka untuk kerja sama program edukasi hukum, diskusi publik, riset, publikasi, pelatihan, dan pengembangan ekosistem literasi hukum.', 'image' => 'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=1400&q=85', 'url' => '/kolaborasi', 'url_label' => 'Ajukan Kerja Sama', 'meta' => ['secondary_url' => '/program', 'secondary_label' => 'Lihat Program'], 'sort_order' => 1],

            ['area' => 'about.hero', 'eyebrow' => 'Tentang Kami', 'title' => 'Edulaw Project', 'body' => "Edulaw Project adalah platform literasi hukum digital yang berfokus pada penyajian edukasi hukum, riset kebijakan, publikasi, program pengembangan kapasitas, dan kanal pengembangan hukum yang aplikatif.\n\nMelalui pendekatan kolaboratif dan berbasis data, kami membangun ekosistem pengetahuan hukum yang inklusif, kritis, dan berdampak.", 'image' => 'https://images.unsplash.com/photo-1505664194779-8beaceb93744?auto=format&fit=crop&w=900&q=85', 'image_alt' => 'Perpustakaan hukum Edulaw Project', 'sort_order' => 1],
            ['area' => 'about.stats', 'title' => 'Program', 'subtitle' => '21+', 'icon' => 'calendar', 'sort_order' => 1],
            ['area' => 'about.stats', 'title' => 'Publikasi', 'subtitle' => '300+', 'icon' => 'book', 'sort_order' => 2],
            ['area' => 'about.stats', 'title' => 'Peserta', 'subtitle' => '3.800+', 'icon' => 'users', 'sort_order' => 3],
            ['area' => 'about.stats', 'title' => 'Kolaborasi', 'subtitle' => '23', 'icon' => 'handshake', 'sort_order' => 4],
            ['area' => 'about.stats', 'title' => 'Diskusi Publik', 'subtitle' => '10+', 'icon' => 'chat', 'sort_order' => 5],
            ['area' => 'about.stats', 'title' => 'Konten Edukasi', 'subtitle' => '1.200+', 'icon' => 'play', 'sort_order' => 6],
            ['area' => 'about.leaders', 'title' => 'Abdul Basid Fuadi', 'subtitle' => 'Founder', 'image' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=500&q=85', 'sort_order' => 1],
            ['area' => 'about.leaders', 'title' => 'Azmi Fathu Rohman', 'subtitle' => 'Co-Founder', 'image' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=500&q=85', 'sort_order' => 2],
            ['area' => 'about.leaders', 'title' => 'Faraz Almira Areila', 'subtitle' => 'Co-Founder', 'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=500&q=85', 'sort_order' => 3],
            ['area' => 'about.leaders', 'title' => 'Umi Zakia Azzahro', 'subtitle' => 'Co-Founder', 'image' => 'https://images.unsplash.com/photo-1531123897727-8f129e1688ce?auto=format&fit=crop&w=500&q=85', 'sort_order' => 4],
            ['area' => 'about.managers', 'title' => 'Nabila Rahma', 'subtitle' => 'Manager Program', 'body' => 'Mengelola perencanaan, pelaksanaan, dan evaluasi program edukasi hukum.', 'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=500&q=85', 'sort_order' => 1],
            ['area' => 'about.managers', 'title' => 'Ricky Pratama', 'subtitle' => 'Manager Riset & Publikasi', 'body' => 'Memimpin proses riset, kajian kebijakan, dan publikasi berbasis data.', 'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=500&q=85', 'sort_order' => 2],
            ['area' => 'about.managers', 'title' => 'Dewi Safitri', 'subtitle' => 'Manager Insight Editorial', 'body' => 'Mengelola konten editorial, analisis hukum, dan pengembangan penulis.', 'image' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=500&q=85', 'sort_order' => 3],
            ['area' => 'about.managers', 'title' => 'Fauzan Aditya', 'subtitle' => 'Manager Multimedia', 'body' => 'Mengelola produksi audiovisual, media sosial, dokumentasi, dan distribusi konten.', 'image' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=500&q=85', 'sort_order' => 4],
            ['area' => 'about.focus_intro', 'eyebrow' => 'Fokus Kerja', 'title' => 'Dari Literasi Hukum Menuju Pengetahuan Publik', 'sort_order' => 1],
            ['area' => 'about.focus', 'title' => 'Literasi Hukum', 'body' => 'Materi belajar yang ringkas, kontekstual, dan mudah digunakan.', 'icon' => 'book', 'sort_order' => 1],
            ['area' => 'about.focus', 'title' => 'Riset Kebijakan', 'body' => 'Kajian berbasis regulasi, putusan, data, dan kebutuhan publik.', 'icon' => 'chart', 'sort_order' => 2],
            ['area' => 'about.focus', 'title' => 'Insight Editorial', 'body' => 'Esai dan analisis hukum dengan gaya akademik yang tetap terbaca.', 'icon' => 'pen', 'sort_order' => 3],
            ['area' => 'about.focus', 'title' => 'Kolaborasi Publik', 'body' => 'Ruang kerja bersama untuk diskusi, advokasi, dan penguatan komunitas.', 'icon' => 'users', 'sort_order' => 4],
            ['area' => 'about.why', 'eyebrow' => 'Mengapa', 'title' => 'Mengapa Edulaw Hadir?', 'body' => "Hukum sering hadir dalam bahasa yang teknis, tertutup, dan sulit dijangkau publik. Padahal, kualitas demokrasi dan kewargaan sangat bergantung pada kemampuan masyarakat memahami hak, kewajiban, serta arah kebijakan negara.\n\nEdulaw Project hadir untuk menjembatani pengetahuan hukum, riset kebijakan, dan kebutuhan masyarakat atas informasi yang jernih, reflektif, serta dapat digunakan dalam pembelajaran, diskusi publik, dan advokasi berbasis pengetahuan.", 'sort_order' => 1],
            ['area' => 'about.timeline_intro', 'eyebrow' => 'Perjalanan Edulaw', 'title' => 'Dari forum kecil menuju ekosistem literasi hukum.', 'sort_order' => 1],
            ['area' => 'about.timeline', 'eyebrow' => '2021', 'title' => 'Gagasan Awal', 'body' => 'Forum virtual dan ruang diskusi kecil mulai dijalankan sebagai ruang membaca dan berdiskusi.', 'sort_order' => 1],
            ['area' => 'about.timeline', 'eyebrow' => '2022', 'title' => 'Pengembangan Komunitas', 'body' => 'Penguatan forum dan pengembangan pembelajaran hukum kolaboratif mulai tertata.', 'sort_order' => 2],
            ['area' => 'about.timeline', 'eyebrow' => '2023', 'title' => 'Edulaw Project Didirikan', 'body' => 'Pada 23 Juni 2023, Edulaw Project resmi hadir sebagai platform edukasi hukum independen.', 'sort_order' => 3],
            ['area' => 'about.timeline', 'eyebrow' => '2024', 'title' => 'Ekspansi Program', 'body' => 'Diskusi Literasi Konstitusi, editorial insight, dan kolaborasi publik mulai berkembang.', 'sort_order' => 4],
            ['area' => 'about.timeline', 'eyebrow' => '2025', 'title' => 'Transformasi Digital', 'body' => 'Pengembangan website dan ekosistem publikasi digital dilakukan untuk memperluas akses pengetahuan hukum.', 'sort_order' => 5],
            ['area' => 'about.timeline_meta', 'title' => 'Didirikan', 'subtitle' => '23 Juni 2023', 'icon' => 'calendar', 'sort_order' => 1],
            ['area' => 'about.timeline_meta', 'title' => 'Karakter', 'subtitle' => 'Independen, edukatif, dan kolaboratif.', 'icon' => 'users', 'sort_order' => 2],
            ['area' => 'about.timeline_meta', 'title' => 'Fokus', 'subtitle' => 'Literasi hukum dan kebijakan publik', 'icon' => 'focus', 'sort_order' => 3],
        ];

        $teamMembers = [
            ['Raihan Malik', 'Officer Program', 'Mendukung operasional program dan koordinasi peserta kegiatan.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=500&q=85'],
            ['Salsabila H.', 'Officer Riset', 'Mendukung pengumpulan data, analisis awal, dan penyusunan kajian.', 'https://images.unsplash.com/photo-1548142813-c348350df52b?auto=format&fit=crop&w=500&q=85'],
            ['Hafizh Acmi', 'Officer Publikasi', 'Mengelola proses penyuntingan naskah dan layout publikasi.', 'https://images.unsplash.com/photo-1507591064344-4c6ce005b128?auto=format&fit=crop&w=500&q=85'],
            ['Putri Aulia', 'Officer Insight', 'Mendukung produksi konten artikel dan analisis hukum.', 'https://images.unsplash.com/photo-1534751516642-a1af1ef26a56?auto=format&fit=crop&w=500&q=85'],
            ['Daniel Hermawan', 'Officer Multimedia', 'Mendukung produksi video, desain grafis, dan sosial media harian.', 'https://images.unsplash.com/photo-1519345182560-3f2917c472ef?auto=format&fit=crop&w=500&q=85'],
            ['Nadira Putri', 'Writer', 'Menulis artikel edukatif, insight hukum, dan feature tematik.', 'https://images.unsplash.com/photo-1544725176-7c40e5a71c5e?auto=format&fit=crop&w=500&q=85'],
            ['Firmansyah', 'Writer', 'Menulis konten riset, kebijakan publik, dan literasi hukum.', 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=500&q=85'],
            ['Zahra Putri', 'Writer', 'Menulis konten populer, infografis, dan serial edukasi hukum.', 'https://images.unsplash.com/photo-1531123897727-8f129e1688ce?auto=format&fit=crop&w=500&q=85'],
            ['Ricky Maulana', 'Designer', 'Merancang visual konten, infografis, dan layout publikasi digital.', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=500&q=85'],
            ['Alya Humaira', 'Designer', 'Mendukung desain grafis, branding, dan kebutuhan visual.', 'https://images.unsplash.com/photo-1499952127939-9bbf5af6c51c?auto=format&fit=crop&w=500&q=85'],
            ['Ikhsan Pradipta', 'Content Support', 'Mendukung riset konten, fact-checking, dan manajemen data.', 'https://images.unsplash.com/photo-1531891437562-4301cf35b7e4?auto=format&fit=crop&w=500&q=85'],
            ['Vina Oktaviani', 'Content Support', 'Membantu pengelolaan konten dan koordinasi dengan kontributor.', 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=500&q=85'],
        ];

        foreach ($teamMembers as $index => [$name, $role, $description, $image]) {
            $blocks[] = [
                'area' => 'about.team',
                'title' => $name,
                'subtitle' => $role,
                'body' => $description,
                'image' => $image,
                'sort_order' => $index + 1,
            ];
        }

        foreach ($blocks as $block) {
            ContentBlock::firstOrCreate(
                [
                    'area' => $block['area'],
                    'title' => $block['title'] ?? null,
                ],
                $block + ['is_active' => true]
            );
        }
    }
}
