# Audit preview tautan — 25 September 2026

Audit read-only mencakup 115 URL dari sitemap produksi, halaman publik utama, delapan detail pada ekspor GSC, dan tautan detail tambahan yang ditemukan pada halaman-halaman tersebut. Ini mencakup jenis halaman publik yang ditemukan, bukan bukti inventaris seluruh record database produksi. Parameter filter tidak diperiksa satu per satu karena memakai layout metadata yang sama.

## Temuan sebelum deploy

- Semua 115 respons HTML memuat og:title, og:description, dan og:image.
- 83 halaman menggunakan URL gambar HTTP, termasuk untuk og:image:secure_url. Nilai secure_url semestinya URL HTTPS.
- 84 gambar unik diperiksa melalui HEAD dengan mengikuti redirect; seluruhnya berakhir 200 dengan content-type gambar. Pemeriksaan header tidak membuktikan bahwa WhatsApp berhasil merender gambar.
- Gambar HTTP dialihkan ke HTTPS. Redirect ini tidak membuktikan penyebab pasti preview gagal, tetapi metadata secure_url yang salah dan hop tambahan telah diperbaiki.
- Audit menggunakan user-agent WhatsApp/2.0; ini simulasi akses HTTP, bukan pengujian aplikasi WhatsApp atau crawler resmi.

## Perbaikan lokal

SocialPreviewImage menormalkan URL HTTP/www milik origin canonical situs ke HTTPS. URL eksternal HTTP tetap dipertahankan dan tidak lagi diberi secure_url palsu. Gambar kosong atau skema yang tidak sesuai memakai gambar default. URL protocol-relative mendapat skema HTTPS.

Dimensi dan MIME gambar ditambahkan bila file lokal dapat diverifikasi. Tidak ada pengunduhan gambar remote saat render dan tidak ada dimensi rekaan untuk gambar eksternal. Metadata Twitter memakai URL yang sama. Deskripsi kosong mendapat deskripsi default. Aturan robots/canonical tidak diubah.

## Validasi

44 tes preview/metadata lulus (518 assertions), ditambah 25 tes regresi halaman detail, noindex dan sitemap (238 assertions). Tambahan 26 tes artikel dan multimedia lulus (197 assertions). Total 95 tes lulus. Format Pint dan git diff --check lulus.

## Setelah deploy

Tidak ada migrasi atau perubahan aset frontend. Deploy kode PHP/Blade, jalankan php artisan view:clear, dan buat ulang compiled views bila dipakai oleh prosedur release. Pastikan konfigurasi origin produksi adalah HTTPS; kode tetap menangani URL HTTP yang tersimpan sebelumnya.

Periksa HTML produksi: og:image serta secure_url HTTPS, judul/deskripsi sesuai konten, dan gambar dapat diakses tanpa login. Uji tautan beranda, artikel, publikasi, program, profil dan multimedia dengan menempelkannya pada editor Status WhatsApp. Tidak perlu memublikasikan status untuk memeriksa preview. Perubahan metadata tidak menjamin cache WhatsApp langsung diperbarui atau setiap permukaan aplikasi menampilkan kartu dengan cara sama.

Belum di-deploy; tidak mengubah data atau konfigurasi produksi dan tidak mengirim pesan/status WhatsApp.

Data per URL dan header gambar: [JSON](whatsapp-preview-2026-09-25.json).

Referensi properti Open Graph: https://ogp.me/ (secure_url, width, height, type).
