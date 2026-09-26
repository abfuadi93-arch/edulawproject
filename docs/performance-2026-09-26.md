# Optimasi akses — 26 September 2026

Perubahan lokal:
- TrackPageVisit menangkap data request sebelum respons selesai dan menyimpan analytics melalui Laravel defer, pada fase terminasi setelah respons. Tidak membutuhkan queue worker; manfaat waktu kirim paling efektif pada PHP-FPM/FastCGI. Beban INSERT tetap ada setelah respons, bukan dihapus. Analytics ini best-effort dan bukan audit log transaksi.
- Request bot/preview, non-GET, dan error tidak lagi menerima cookie analytics baru.
- Pemindaian Tailwind CSS publik dibatasi ke sumber aplikasi, template publik, JavaScript publik, dan pagination Laravel. Sumber Filament serta tes tidak lagi ikut menghasilkan utility publik. Theme admin tetap terpisah.
- CSS publik 195.909 → 186.418 byte (sekitar 4,8% lebih kecil). Aset lama dipertahankan agar HTML yang masih tersimpan di cache tetap bisa mengakses stylesheet sebelumnya.

Validasi: npm run build berhasil, Pint lulus, 50 tes analytics/preview/SEO lulus (541 assertions). Tes baru membuktikan INSERT belum terjadi sebelum callback terminasi dijalankan, serta bot WhatsApp tidak menerima cookie analytics.

Batas pengukuran: request HTTPS produksi gagal terhubung setelah 5 detik; tidak ada angka TTFB produksi yang valid. Pengurangan CSS merupakan hasil build lokal, bukan klaim peningkatan Core Web Vitals atau waktu muat server. Belum di-deploy; akses SSH terakhir ditolak autentikasi.

Deployment: kirim kode middleware serta public/build (termasuk manifest dan stylesheet baru). Jalankan php artisan view:clear dan buat ulang cache sesuai prosedur release. Tidak ada migrasi. Jangan menimpa .env atau storage/app/public. Hostinger memakai public_html terpisah: pastikan public/build release tersedia di public_html/build. Setelah deployment, ukur halaman yang sama beberapa kali dari koneksi yang sama, catat HTTP status, TTFB, waktu total dan transfer CSS. Jika timeout HTTPS tetap terjadi sebelum respons, periksa hosting/CDN/jaringan; perubahan PHP tidak memperbaiki kegagalan koneksi TCP.
