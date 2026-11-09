# Perbaikan lokal

| Perbaikan | File utama | Dampak |
|---|---|---|
| Pencarian menggunakan hasil database | `app/Http/Controllers/SearchController.php`, `resources/views/search/index.blade.php` | Maksimal delapan hasil per kanal, label input, batas 150 karakter, dan state kosong yang jujur. Draft dan publikasi terjadwal tidak muncul; program memakai scope yang sama dengan halaman detail. Tombol filter palsu dan data contoh dihapus. |
| Pencarian tetap noindex dan bisa dirayapi | `public/robots.txt`, view pencarian | Crawler dapat melihat noindex yang sudah ada. Jalur admin tetap diblokir. |
| Bahasa dan landmark | `resources/views/layouts/app.blade.php` serta template publik dengan main ganda | Satu landmark main dengan skip link, deklarasi bahasa Indonesia. Kelas visual dipertahankan. |
| Byline dan schema hanya menautkan profil publik | `resources/views/insights/show.blade.php`, `app/Support/StructuredData.php` | Nama penulis tercatat tetap tampil; tidak menyamakan pembuat record atau reviewer dengan penulis. URL profil memakai scope yang sama dengan halaman profil. |
| Timestamp revisi terpisah | `app/Models/Concerns/TracksContentRevision.php`, model Insight/Publication, migration `2026_11_09_000001_add_content_modified_at_to_editorial_content.php`, `app/Http/Controllers/SitemapController.php` | Penyimpanan model biasa mencatat perubahan field isi; pilihan featured dan workflow saja tidak mengubah tanggal revisi. Baris lama dibiarkan NULL. UI artikel dan JSON-LD hanya memakai revisi tercatat setelah terbit; sitemap editorial kembali ke tanggal publikasi bila revisi tidak diketahui. Impor massal atau saveQuietly perlu menangani pencatatan terpisah. |
| Aset dan regresi | `public/build`, tes Feature terkait | Aset dibangun ulang. Tes mencakup hasil pencarian, visibilitas, profil tertutup dan tanggal revisi. |

Tidak ada deploy, perubahan data konten produksi, penggabungan profil, perubahan publisher ID, atau perubahan penempatan iklan. Siapkan deployment hanya setelah otorisasi: backup sesuai prosedur, jalankan migration tambahan sebelum aplikasi baru melayani permintaan, deploy aset hasil build dan robots.txt, bersihkan cache view/route sesuai prosedur hosting, lalu ulangi smoke test.
