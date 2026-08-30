<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Edulaw Deployment Notes

Use these environment values on production:

```env
APP_URL=https://edulawproject.id
FILESYSTEM_DISK=public
```

Manual storage symlink on Hostinger:

```bash
cd ~/domains/edulawproject.id
rm -rf public_html/storage
ln -s ~/domains/edulawproject.id/laravel/storage/app/public ~/domains/edulawproject.id/public_html/storage
ls -la public_html/storage
```

Expected result:

```text
public_html/storage -> /home/u815125696/domains/edulawproject.id/laravel/storage/app/public
```

## Data terstruktur acara (Google Search Console)

Setelah menerapkan perubahan kode, jalankan `php artisan migrate --force` pada server
untuk menambahkan kolom harga tiket dan detail acara, lalu `php artisan view:clear`.
Jalankan `npm run build` jika menerapkan dari source tanpa aset hasil build.
Migrasi tidak mengisi atau mengubah data acara lama.

Di admin **Program**, periksa acara pada URL yang dilaporkan Search Console:

- **Narasumber**: isi nama dan jenis (individu/kelompok) yang sudah dikonfirmasi;
  diterbitkan sebagai `performer`.
- **Tanggal Selesai**: isi tanggal sebenarnya. Acara satu hari memakai tanggal yang
  sama dengan tanggal mulai. Tanggal kosong tidak otomatis disamakan dengan tanggal
  mulai. Jam mulai dan selesai bersifat opsional. Tanpa jam, JSON-LD menggunakan
  `YYYY-MM-DD`; jika jam diketahui, sistem menyertakan offset zona waktu acara.
- **Zona Waktu**: berlaku untuk jam acara dan waktu pendaftaran dibuka. Data lama
  tetap menggunakan zona waktu situs (Asia/Jakarta), tanpa menambahkan jam fiktif.
- **Status Penyelenggaraan**: bedakan terjadwal, dibatalkan, ditunda, dan dijadwalkan
  ulang. Status arsip berdasarkan tanggal tidak dianggap sebagai pembatalan.
- **Lokasi Acara**: isi nama tempat dan komponen alamat sebenarnya. Untuk online
  atau hybrid, gunakan URL acara daring publik yang terpisah dari link pendaftaran.
  Jangan memublikasikan URL rapat privat. Acara online tanpa URL akses publik tidak
  diterbitkan sebagai `Event`; tautan pendaftaran bukan lokasi virtual.
- **Link Pendaftaran**: gunakan URL HTTP/HTTPS khusus pendaftaran acara tersebut.
- **Harga Tiket / Mata Uang**: isi harga terendah termasuk biaya layanan, atau `0` untuk
  acara gratis. Jika nominal kosong, hanya label jenis biaya `Gratis` atau `free`
  yang dianggap bernilai nol. Label `Berbayar` saja tidak cukup untuk mengetahui
  nominal. Nominal yang diisi menjadi sumber biaya di halaman publik dan schema.
- **Ketersediaan / Pendaftaran Dibuka Pada**: isi jika sudah diketahui. `availability`
  tidak diasumsikan tersedia pada acara yang sudah selesai, batal, ditunda, atau
  belum membuka pendaftaran. `validFrom` berasal dari waktu pembukaan pendaftaran.
- **Penyelenggara**: nama dan website Edulaw digunakan jika kolom kosong. Penyelenggara
  lain tidak otomatis memakai website Edulaw. Data ini juga terlihat di halaman publik.
- **Galeri Acara**: gambar yang diunggah tampil di galeri dan `image` JSON-LD, bersama
  poster dan hero. Gunakan gambar acara sebenarnya; contoh rasio 1:1, 4:3, dan 16:9.

`offers` hanya diterbitkan jika URL pendaftaran dan harga diketahui. Nama narasumber,
harga, ketersediaan tiket, tanggal penjualan, dan tanggal selesai tidak dibuat-buat
demi menghilangkan peringatan. Karena itu, acara dengan data yang belum lengkap
masih dapat memiliki peringatan non-kritis setelah kode diterapkan.

Uji URL publik di [Rich Results Test](https://search.google.com/test/rich-results),
lalu gunakan **Validasi perbaikan** pada laporan Acara di Search Console. Perubahan
lokal atau tes otomatis tidak berarti laporan Google sudah divalidasi; Google perlu
merayapi ulang halaman yang telah diperbarui. Rujukan:
[panduan Event Google](https://developers.google.com/search/docs/appearance/structured-data/event).

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
