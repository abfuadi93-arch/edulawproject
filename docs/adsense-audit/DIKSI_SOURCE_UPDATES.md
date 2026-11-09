# Pengayaan program dan multimedia DIKSI

Materi editorial di `config/diksi-editorial.php` mencakup **7 ringkasan video (seri 10–16)** dan **5 halaman program (seri 10–13 dan 15)**. Setiap seri memakai liputan resmi MKRI sebagai dasar ringkasan, dengan panduan menonton yang ditulis oleh redaksi. Tautan rekaman diidentifikasi dari katalog Multimedia Edulaw. Ringkasan ini bukan transkrip atau penanda waktu rekaman. Tanggal penyelenggaraan tidak dipakai untuk mengganti tanggal publikasi video.

Halaman program mencantumkan liputan MKRI dan tautan rekaman. Halaman tonton menampilkan liputan MKRI sebagai bacaan pendamping. Pencocokan memakai slug program dan ID YouTube; tidak menebak dari judul. Program magang tidak diubah karena tidak ada sumber untuk durasi, persyaratan, seleksi, dan jadwal terperinci. Video/Shorts/Reels lain di luar seri tersebut belum ditinjau dalam pembaruan ini.

## Pratinjau dan penerapan

```bash
php artisan edulaw:enrich-diksi
php artisan edulaw:enrich-diksi --apply
```

Perintah pertama baca-saja. Perintah kedua menulis deskripsi untuk record publik yang cocok dan isinya lebih pendek daripada naskah usulan. Deskripsi yang sama atau lebih panjang dipertahankan; record yang hilang, duplikat ID video publik, draft, atau video terjadwal tidak diperbarui. Tinjau naskah dan hasil pratinjau sebelum memakai opsi `--apply` di produksi. Tidak perlu migrasi tambahan untuk paket konten ini. Jangan jalankan ulang seeder DIKSI lama setelah pembaruan ini karena seeder lama dapat menimpa deskripsi video.

Pada database lokal, 7 deskripsi diperbarui: program 10, 11, 12, 13 dan video 12, 13, 14. Program 15 dipertahankan. Video 10, 11, 15, 16 tidak memiliki satu kecocokan ID publik yang unik pada data lokal; naskahnya tetap tersedia untuk data produksi yang cocok. Pratinjau setelah penerapan tidak menawarkan pembaruan ulang. **Produksi belum diubah.**

Validasi: 8 tes lulus (84 assertion), termasuk mode pratinjau, penyimpanan, perlindungan draft, idempotensi, tautan sumber, dan rendering halaman tonton/program.
