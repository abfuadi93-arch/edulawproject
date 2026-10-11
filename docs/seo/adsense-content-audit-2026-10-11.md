# Audit konten AdSense — 11 Oktober 2026

## Ruang lingkup dan kesimpulan

Proyek katafuadi mengoperasikan Edulaw Project (edulawproject.id). Audit membaca
kode, database lokal, serta sampel beranda, halaman magang, dan multimedia publik.
Database lokal merupakan salinan lama dan tidak sama dengan produksi: beranda
publik menyebut 75 insight, 24 program, 5 publikasi, dan 20 multimedia, sedangkan
lokal memiliki 53 insight, 21 program, 4 publikasi, dan 18 multimedia publik.
Angka lokal tidak boleh dianggap sebagai jumlah masalah produksi.

Pesan penolakan tidak mengidentifikasi URL penyebab. Temuan berikut adalah risiko
yang perlu diperbaiki, bukan bukti penyebab keputusan Google. Audit ini tidak
memverifikasi keaslian semua artikel, akurasi hukum, hak penggunaan media,
analytics, Search Console, atau akun AdSense.

## Temuan yang dapat direproduksi

- 53 insight lokal melewati pemeriksaan kelengkapan internal dan memiliki penulis.
  Panjang artikel saja belum membuktikan orisinalitas, akurasi, atau manfaat.
- 19 dari 21 program lokal belum memenuhi pedoman isi internal. Deskripsinya
  hanya 13–19 kata. Untuk arsip kegiatan, tambahkan catatan materi, hasil diskusi,
  dokumentasi, atau tautan rekaman berdasarkan kegiatan sebenarnya.
- Empat publikasi lokal memiliki ringkasan 25–65 kata. Sebagian sudah memiliki
  tautan dokumen, sehingga kekurangan ringkasan tidak otomatis membuat halaman
  tidak layak diindeks. Tambahkan konteks masalah, metode, temuan, serta kontribusi
  spesifik dari naskah asli dan pastikan dokumen/tautan dapat diakses.
- Seluruh 18 multimedia lokal perlu ditinjau: beberapa deskripsinya kosong;
  lainnya singkat. Kelompok ini juga mencakup Shorts/Reels, yang tidak memiliki
  halaman tonton tersendiri. Untuk video, tulis ringkasan substansi berdasarkan
  rekaman, topik yang dibahas, dan penanda waktu yang diverifikasi bila tersedia.
- Sampel produksi `/program/virtual-internship-edulaw-project` hanya memiliki satu
  kalimat gambaran program. Lengkapi sasaran peserta, kegiatan, keluaran, durasi,
  proses seleksi, serta ketentuan pendaftaran setelah dikonfirmasi penyelenggara.
  Jangan mengarang detail yang belum diumumkan.
- Halaman multimedia produksi menampilkan janji album yang belum tersedia.
  Kode kini menyembunyikan bagian album dan Shorts/Reels yang kosong beserta
  navigasinya; bagian tersedia tetap tampil.
- Halaman tentang, kontak, privasi, ketentuan, standar editorial, dan koreksi
  sudah tersedia. Penambahan halaman kebijakan lain bukan prioritas audit ini.

## Perubahan dan cara menjalankan audit

Perintah baca-saja baru:

```bash
php artisan edulaw:audit-public-content
php artisan edulaw:audit-public-content --json
```

Audit mengikuti scope publik/visible model, mengabaikan draft dan artikel
terjadwal, serta menghasilkan ID, slug, jumlah kata, dan alasan tinjauan.
`indexable` memakai aturan aplikasi yang sudah ada; `null` berarti multimedia
tidak memiliki halaman detail tonton. Ini bukan pemeriksaan indeks Google.
Hasil lokal tersimpan pada `adsense-content-audit-2026-10-11.json` (41 temuan).

Angka 300 kata untuk artikel dan 100 kata untuk video berasal dari pemeriksaan
internal yang sudah ada di proyek, bukan ketentuan resmi Google. Jangan menambah
teks pengisi untuk mengejar angka tersebut. `noindex` juga tidak memperbaiki
manfaat isi bagi pengunjung dan bukan solusi tunggal penolakan AdSense.

## Sebelum pengajuan ulang

1. Terapkan perubahan kode melalui alur deployment yang biasa; jalankan
   `php artisan view:clear`. Tidak ada migrasi atau perubahan aset JavaScript/CSS.
2. Jalankan audit pada database produksi dan prioritaskan halaman kosong,
   deskripsi yang hanya mengulang judul, serta halaman yang aktif dipromosikan
   di beranda. Daftar lokal disediakan sebagai contoh antrean redaksi.
3. Lengkapi isi menggunakan artikel, dokumen, rekaman, atau catatan kegiatan asli;
   verifikasi sumber primer untuk klaim hukum serta atribusi kontributor.
   Konten yang belum siap dapat dikembalikan menjadi draft melalui admin setelah
   ditinjau redaksi; audit tidak mengubah status secara otomatis.
4. Periksa URL publik dan tautan dokumen pada seluler dan desktop. Pastikan
   navigasi membawa pengunjung ke informasi yang dijanjikan.
5. Gunakan analytics yang benar-benar tersedia untuk menilai minat pengguna dan
   pola kunjungan kembali. Tidak ada angka trafik minimum yang ditetapkan audit.
6. Ajukan ulang setelah perbaikan isi tersedia di produksi dan telah diperiksa.
   Perubahan lokal belum diterapkan pada situs publik; keputusan tetap pada Google.

## Rujukan

- [Konten dan pengalaman pengguna AdSense](https://support.google.com/adsense/answer/10015918)
- [Laporan tindakan manual dan konten tipis](https://support.google.com/webmasters/answer/9044175#thin-content)
- [Kebijakan spam penelusuran untuk penerbit](https://support.google.com/publisherpolicies/answer/11035931)
- [Sampel program produksi](https://edulawproject.id/program/virtual-internship-edulaw-project)
- [Kanal multimedia produksi](https://edulawproject.id/multimedia)
