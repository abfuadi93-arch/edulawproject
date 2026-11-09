# Audit Edulaw Project — 9 November 2026 (WIB)

Audit ini memisahkan observasi produksi, diagnosis kode, dan penilaian Google. Status akun yang dilaporkan pengelola: kepemilikan terverifikasi, ads.txt diotorisasi, penolakan Konten tanpa manfaat per 7 November, dan pengajuan ulang paling cepat 14 November. Status akun tersebut tidak dicek dari dashboard.

## Ruang lingkup dan metode

Proyek ini memakai Laravel 13 (PHP), Blade SSR, Filament 5 untuk CMS, Eloquent, MySQL pada konfigurasi kerja, SQLite in-memory untuk tes, serta Vite/Tailwind untuk aset. Deployment Hostinger/LiteSpeed mendokumentasikan document root `public_html` di README. Rute dan controller menentukan visibilitas; canonical dan metadata ada di layout serta template; robots adalah berkas statis dan sitemap dirender server. Panel internal dan draft tidak diaudit lewat HTTP produksi.

Inventaris memulai crawl GET dari sitemap, kanal, pencarian, dan beberapa probe URL; tautan HTML HTTPS pada domain yang sama ditelusuri hingga antrean habis, maksimal empat permintaan paralel. 220 URL diperiksa: 212 respons akhir 200 (207 HTML dan 5 dokumen) serta 8 respons 404. Dua alias berakhir di kanal kanonis. Semua 120 URL sitemap memperoleh 200 langsung, memiliki canonical yang cocok, dan tidak menyatakan noindex. Ini observasi HTTP, bukan bukti indeks. Lima unduhan PDF merespons 200; isi, hak, dan validitas setiap dokumen tidak diaudit. Tidak ada 5xx dalam crawl; tidak sengaja memicu error di produksi.

CSV menginventaris URL yang ditemukan, termasuk varian pagination, URL uji dan unduhan. Arsip tersembunyi, URL tanpa tautan atau sitemap, dan item video yang hanya tersedia di platform lain tidak tercakup sebagai halaman detail. Kolom kata adalah estimasi teks landmark utama HTML (termasuk elemen navigasi lokal/CTA di dalamnya), bukan panjang naskah. Tanggal/penulis dilaporkan dari JSON-LD konten saat ada, bukan verifikasi riwayat; kolom kosong berarti sinyal belum ditemukan. Anchor catatan kaki adalah indikator, bukan bukti mutu atau ketiadaan sumber. Jumlah tautan memakai URL internal yang dinormalisasi; link eksternal, fragmen, dan aset di luar sampel. Klasifikasi awal: 97 KEEP, 86 IMPROVE, 35 REVIEW FOR NOINDEX, 2 MERGE CANDIDATE. KEEP pada probe berarti pertahankan perilaku; REVIEW FOR NOINDEX pada baris tersebut berarti tinjau halaman yang **sudah** noindex, tidak ada aksi otomatis.

Kategori produksi yang ditemukan: Law & Governance, Legal 101, Regulatory Update dan Edulaw Insight; tidak ditemukan rute mandiri untuk nama kategori lain dalam brief. Selain kategori dan artikel, crawl mencakup beranda, program dan arsip, publikasi, peluang, profil, multimedia, pencarian, kontak, kolaborasi dan kebijakan.

## Temuan berdasarkan prioritas

| Prioritas | Bukti | Lokasi dan tindakan |
|---|---|---|
| P0 | Tidak ada gangguan seluruh situs pada sampel. | Monitor respons setelah deploy. |
| P1 | [Pencarian Konstitusi](https://edulawproject.id/search?q=konstitusi) menampilkan data contoh dan tiga tautan 404: regulasi perlindungan data pribadi, reformasi hukum acara perdata, dan program DIKSI. Tombol jenis konten tidak berfungsi. | `resources/views/search/index.blade.php` menimpa hasil controller. Gunakan hasil nyata, scope publik, dan hilangkan tombol semu. |
| P1 | Tiga artikel PHPU/KPU menautkan `/profil/redaksi-edulaw`, yang 404 karena scope profil publik mengecualikan profil organisasi tersebut. | Tampilkan nama tanpa URL yang tidak tersedia; perbaiki byline dan author JSON-LD. |
| P1 | Privacy publik tidak menjelaskan penggunaan Google Ads/AdSense dan teknologi iklan walau HTML memuat tag Google. Tidak ada bukti konfigurasi CMP regional dari crawl ini. | `resources/views/pages/privacy.blade.php`. Draft pengungkapan dan pemeriksaan CMP ada di EDITORIAL_REVIEW; perlu persetujuan pengelola. |
| P1 | [Magang](https://edulawproject.id/program/virtual-internship-edulaw-project) dan [DIKSI 12](https://edulawproject.id/program/diskusi-literasi-konstitusi-seri-12) memerlukan penjelasan yang lebih berguna. Sebagian katalog multimedia bergantung pada tautan keluar. | Isi berdasarkan fakta penyelenggara dan rekaman; jangan memperbanyak kata pengisi atau menghapus URL. |
| P2 | Pencarian mengekspor noindex, tetapi robots.txt produksi melarang crawl `/search`. | Izinkan crawl agar Google dapat membaca aturan noindex yang sudah ada. |
| P2 | 207 HTML sukses pada domain menggunakan `lang=en`, meski antarmuka berbahasa Indonesia. Layout dan beberapa template menumpuk elemen main. Satu gambar editorial tidak memiliki alt. | Perbaiki bahasa/landmark di Blade; penulis mengisi alt dari konteks gambar asli. |
| P2 | Perubahan workflow/featured menggeser `updated_at`, yang sebelumnya bisa dianggap tanggal pembaruan isi oleh schema dan halaman. | Simpan waktu revisi konten editorial terpisah; jangan isi riwayat lama berdasarkan tebakan. |
| P2 | Profil `fadila-sharfina` dan `fadila-sharfina-2` punya H1 yang sama. | Cek identitas dan atribusi; kandidat merge saja. Judul serupa pada pagination normal. |
| P3 | JSON-LD bisa diparse, 207 HTML sukses punya satu H1 serta meta description. BreadcrumbList, Article, Person, Event, dan schema publikasi ditemukan. HTML sukses memakai publisher yang sama dengan ads.txt; satu pemuatan skrip per halaman. | Pertahankan; lakukan validasi Rich Results dan URL Inspection untuk URL contoh setelah deploy. Jangan samakan parse JSON dengan kelayakan rich result. |

Endpoint Cloudflare `/cdn-cgi/l/email-protection` muncul sebagai 404 pada crawler tanpa JS; jangan diperlakukan sebagai artikel rusak. Probe URL acak dan pagination di luar rentang menghasilkan 404 sebagaimana diharapkan. Tautan eksternal tidak dicrawl. Tautan peluang yang tampil sebagai detail di produksi perlu dibandingkan dengan perilaku branch lokal saat deploy; jangan mengganti URL lama tanpa memeriksa tujuan. Tidak dilakukan perubahan canonical massal, noindex konten, atribusi, atau redirect massal.

Performa: review kode menunjukkan aset Vite, font lokal, gambar responsif dan lazy loading di beberapa template. Build terukur tercantum dalam TEST_RESULTS. Crawl tidak mengukur LCP, INP atau CLS lapangan. Browser produksi diuji pada 390 × 844: tidak ada overflow horizontal di beranda, skip link tersedia, menu dibuka dengan Enter dan ditutup dengan Escape. Ini bukan audit WCAG menyeluruh.

## Rujukan resmi

- [Pengalaman pengguna dan konten AdSense](https://support.google.com/adsense/answer/10015918)
- [AdSense Program Policies](https://support.google.com/adsense/answer/48182)
- [Google Publisher Policies, termasuk pengungkapan privasi](https://support.google.com/publisherpolicies/answer/10502938#privacy-disclosures)
- [Persyaratan CMP Google](https://support.google.com/adsense/answer/13554116)
- [Google Search: noindex harus dapat dirayapi](https://developers.google.com/search/docs/crawling-indexing/block-indexing)
