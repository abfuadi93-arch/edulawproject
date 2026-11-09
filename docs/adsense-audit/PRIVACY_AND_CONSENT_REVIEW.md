# Tinjauan pengungkapan Google dan consent

**Terverifikasi:** HTML produksi halaman Kebijakan Privasi memuat Google tag untuk Google Ads dan skrip Google AdSense. Kebijakan menjelaskan formulir dan sebagian data teknis, tetapi belum menjelaskan pemrosesan melalui kedua layanan iklan atau menyediakan tautan penjelasan Google. Tag yang termuat tidak membuktikan bahwa iklan aktif atau konversi tertentu dikirim.

**Belum terverifikasi:** HTML mentah yang diperiksa tidak memperlihatkan penanda Google CMP/TCF. Ini tidak membuktikan CMP tidak aktif: pesan dapat dimuat setelah halaman berjalan dan bergantung pada wilayah atau konfigurasi akun. Dashboard, penayangan regional, dan perilaku tag sebelum/sesudah pilihan pengguna belum diperiksa.

## Usulan teks — untuk persetujuan pengelola

Tambahkan bagian **Google Ads, AdSense, dan cookie**:

> Edulaw Project memuat tag Google Ads dan Google AdSense. Saat layanan Google dimuat, Google dapat menerima informasi seperti alamat IP dan URL halaman. Google Ads dapat menggunakan informasi terkait kunjungan dan interaksi untuk pengukuran iklan sesuai pengaturan yang berlaku. Jika iklan ditayangkan, Google dan penyedia teknologi iklan pihak ketiga dapat menempatkan atau membaca cookie, atau menggunakan web beacon, alamat IP, dan pengenal lain untuk penayangan dan pengukuran iklan. Personalisasi iklan bergantung pada pilihan pengguna dan pengaturan yang berlaku.
>
> Baca [Cara Google menggunakan data dari situs atau aplikasi partner](https://policies.google.com/technologies/partner-sites?hl=id) untuk mengetahui cara Google menggunakan data dan pilihan yang tersedia.

Perjelas bahwa tujuan komunikasi pada bagian 3 merujuk pada data yang diberikan melalui formulir; jelaskan pemrosesan data teknis oleh layanan iklan secara terpisah. Pernyataan bahwa pengunjung dianggap membaca kebijakan tidak menggantikan mekanisme persetujuan. Tambahkan petunjuk mengubah atau menarik pilihan setelah mekanismenya benar-benar diuji. Sesuaikan tanggal kebijakan saat perubahan diterbitkan.

## Verifikasi di AdSense

1. Buka **Privasi & pesan (Privacy & messaging) → Peraturan Eropa (European regulations) → Kelola (Manage)**. Periksa apakah ada pesan berstatus **Dipublikasikan (Published)** untuk `edulawproject.id`, atau identifikasi CMP lain yang telah digunakan. Hindari menjalankan dua CMP untuk wilayah dan tujuan yang sama.
2. Jika menggunakan CMP Google, cocokkan URL kebijakan, bahasa dan situs yang dipilih. Periksa pilihan **Setuju**, **Tidak setuju**, dan **Kelola opsi**, serta cara pengunjung menarik persetujuan.
3. Untuk pesan Google yang sudah dipublikasikan, uji di jendela samaran memakai `https://edulawproject.id/?fc=alwaysshow&fctype=gdpr` sebagai pratinjau internal. Uji menolak, menerima, membuka kembali pilihan dan memastikan tautan kebijakan bekerja. Pratinjau mengabaikan wilayah; konfirmasi perilaku penayangan dengan pengujian wilayah yang relevan, tanpa parameter paksa. Tidak munculnya pesan paksa saja tidak membuktikan konfigurasi akun salah.
4. Periksa tag Google Ads secara terpisah dengan Tag Assistant: tinjau default dan perubahan `ad_storage`, `ad_user_data`, dan `ad_personalization` ketika menolak, menyetujui atau menarik pilihan. Keberadaan pesan tidak membuktikan tag Google Ads menghormati pilihan. Jangan menganggap semua trafik berasal dari Indonesia tanpa bukti.

Rujukan: [Pengungkapan privasi penerbit](https://support.google.com/publisherpolicies/answer/10502938#privacy-disclosures), [CMP bersertifikasi Google](https://support.google.com/adsense/answer/13554116), [Uji pesan](https://support.google.com/adsense/answer/10924669), [Penarikan consent](https://support.google.com/adsense/answer/10959060).

## Implementasi lokal

Pengungkapan dan tautan Google ditambahkan ke kebijakan lokal. Google tag mengantrekan default `denied` untuk ketiga sinyal iklan sebelum konfigurasi Google Ads. Ini Consent Mode lanjutan: skrip masih dimuat dan sinyal tanpa cookie dapat dikirim. Tidak ada pembaruan ke `granted` yang dibuat oleh kode lokal; tanpa integrasi CMP yang mengirim pilihan, pengukuran Google Ads dapat berkurang. Default ini tidak mengonfigurasi AdSense CMP atau mengendalikan seluruh skrip AdSense. Tinjau default dan pembaruan sinyal dengan [Tag Assistant](https://developers.google.com/tag-platform/security/guides/consent) sebelum mengandalkan pengukuran. Produksi dan pengaturan akun belum diubah.
