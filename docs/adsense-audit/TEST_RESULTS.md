# Hasil validasi — 9 November 2026

| Pemeriksaan | Hasil |
|---|---|
| Baseline `php artisan test --compact` | 430 tes: 423 lulus, 7 gagal; 3.344 assertion. |
| Akhir `php artisan test --compact` | 432 tes: 425 lulus, 7 gagal; 3.361 assertion. Daftar kegagalan identik dengan baseline. |
| Regresi lokal | Pencarian database, draft/jadwal, profil nonpublik, revisi substantif, robots, metadata, sitemap, struktur artikel/publikasi diuji melalui suite Feature dengan SQLite in-memory. |
| Build | `npm run build` berhasil (Vite 8.0.16, ~667 ms); JS utama 4,76 kB dan CSS utama ~190 kB sebelum kompresi. Ukuran build bukan hasil Core Web Vitals. |
| Lint | `vendor/bin/pint --dirty --test` dan `git diff --check` berhasil. Tidak ada skrip lint JS atau type-check terkonfigurasi di package.json. |
| Rute | `php artisan route:list --except-vendor` menampilkan 83 rute. Probe produksi untuk URL tak dikenal dan pagination di luar rentang mengembalikan 404; dua alias berakhir di tujuan kanonis. |
| HTTP dan SEO produksi | 220 URL, 120 URL sitemap seluruhnya 200 langsung/self-canonical dan tanpa noindex; 207 HTML sukses dengan satu H1 dan deskripsi. Seluruh blok JSON-LD dalam sampel dapat diparse. |
| ads.txt dan robots.txt | Keduanya diakses di produksi. ads.txt 200 text/plain dengan publisher sesuai HTML. robots lokal telah mengizinkan crawl hasil pencarian supaya noindex dapat dibaca. |
| Seluler dan keyboard produksi | Viewport 390 × 844, lebar dokumen dan scroll sama-sama 390. Skip link ada. Enter membuka menu dan Escape menutupnya (aria-expanded false → true → false). |
| CSV | 220 URL unik, header dan jumlah kolom valid; semua baris diklasifikasikan. |

## Tujuh kegagalan yang sudah ada sebelum perubahan

- HomePageTest (5): jumlah editorial terbaru dengan pilihan editor; jumlah dengan pilihan editor fallback; batas publikasi yang tampil; urutan bagian tentang/kolaborasi; jumlah/urutan teaser multimedia.
- PublicHeroConsistencyTest (1): hitungan elemen ritme vertikal kanal.
- StructuredDataTest (1): tautan pendaftaran acara berbayar tidak terlihat pada halaman fixture meski diuji pada schema.

Kegagalan ini tetap perlu ditinjau sebelum menjadikan suite sebagai gerbang deploy yang seluruhnya hijau. Pengujian ulang juga perlu memastikan CTA acara yang aktif dan penempatan hero benar di produksi.

## Belum diuji

Tidak ada pengukuran Lighthouse/CrUX, kontras WCAG menyeluruh, uji perangkat fisik, simulasi 500 produksi, pengujian semua tautan eksternal, validasi Google Rich Results, dashboard Search Console/AdSense, atau uji geografis CMP. Migration diuji via suite SQLite dan belum dijalankan di database produksi. Uji browser memakai produksi sebelum patch, sehingga bukan verifikasi browser terhadap patch yang belum diterapkan.
