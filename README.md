# Saji Baik

Website Laravel untuk makanan harian yang lebih terjangkau bagi mahasiswa, pekerja, pencari kerja, dan orang dewasa yang merasa membutuhkan. Fokus SDG 2 — Tanpa Kelaparan: memperluas akses makanan bergizi dan terjangkau. Inisiatif mandiri, bukan program MBG pemerintah.

## Fitur

- Beranda, katalog dengan pencarian/filter harga, kategori dan outlet.
- Detail menu, isi porsi, estimasi gizi, dan informasi alergen.
- Registrasi, login, dan pesanan milik masing-masing pelanggan.
- Pesan 1–2 porsi, pilih waktu ambil, pernyataan kebutuhan, bayar di outlet.
- Kode pengambilan, pembatalan, dan pembaruan stok dalam transaksi database.
- Dashboard admin khusus: ringkasan, pesanan, menu harian, outlet, laporan SDG 2 dan foto tim.
- Salin menu ke tanggal lain dengan stok baru tanpa mengubah pesanan sebelumnya.
- Batas waktu ambil dan status Tidak diambil.
- Laporan porsi diambil berdasarkan waktu selesai, filter tanggal, dan ekspor CSV.
- Tim creator pada Tentang Kami; unggah foto melalui halaman pengelola.
- Responsif untuk desktop dan mobile.

## Menjalankan

PHP >= 8.3, Composer, MySQL 8, ekstensi pdo_mysql, pdo_sqlite (untuk pengujian), dan GD.
Aplikasi menggunakan Blade, CSS, dan JavaScript biasa; tidak memerlukan build frontend.

1. Jalankan composer install.
2. Salin .env.example ke .env.
3. Jalankan php artisan key:generate.
4. Buat database MySQL saji_baik melalui phpMyAdmin, lalu sesuaikan DB_USERNAME dan DB_PASSWORD di .env.
5. Jalankan php artisan migrate --seed.
6. Jalankan php artisan serve, kemudian buka http://127.0.0.1:8000.

Di Laragon lokal proyek ini berada di C:\laragon\www\saji-baik dan dapat dibuka di http://localhost/saji-baik/public saat Apache aktif.

## Akun demo lokal

Seeder hanya membuat akun demo pada lingkungan local dan testing.

- Pelanggan: teman@sajibaik.test
- Pengelola: pengelola@sajibaik.test
- Kata sandi keduanya: SajiBaik!2026

Akun ini untuk demonstrasi lokal. Jangan gunakan kredensial demo untuk penerapan publik.

Masuk admin: /admin/masuk. Dashboard: /mitra. Database lokal MySQL: saji_baik (dapat dibuka melalui phpMyAdmin Laragon).

Menu memiliki tanggal dan stok harian. Gunakan Menu harian → Salin menu untuk menyiapkan hari berikutnya. Menu sumber dan pesanan lama tidak berubah; salinan ganda dari sumber dan tanggal yang sama dilewati. Waktu ambil dalam WIB dan lebih dari 15 menit setelah waktu pemesanan.

Batas ambil adalah waktu pilihan ditambah toleransi outlet (default 30 menit), maksimal jam tutup. Status Tidak diambil hanya dapat ditetapkan setelah batas lewat; tidak menambah stok dan tidak dihitung sebagai porsi tersalurkan.

Laporan hanya menjumlah pesanan completed berdasarkan completed_at. Akun unik bukan jumlah individu. Nilai ini menggambarkan aktivitas layanan, bukan bukti berkurangnya kelaparan.

## Tim creator

- Aviel Aquino — 2802393711
- Kent Nathan Herlambang — 2802397464
- Pharrel Jibril Tristan — 2802393964
- Bangkit Ahktar Fahd — 2802401625

Foto tim bawaan tersedia pada public/images/creators dan dipasangkan berdasarkan NIM. Foto dapat diganti melalui /mitra/tim; foto pengganti tersimpan pada penyimpanan lokal dan tidak ikut masuk repositori.

## Pengujian

php artisan test

Data database, .env, unggahan pribadi, cadangan, cache, log, dan vendor tidak disertakan ke Git. Database SQLite lama tetap tersimpan sebagai cadangan lokal; perintah saji:import-sqlite hanya mengimpor ke tabel MySQL kosong di lingkungan local.

## Data dan gambar contoh

Seluruh outlet, harga, dan menu dalam seeder adalah ilustrasi dan belum menerima transaksi sungguhan. Nilai gizi adalah estimasi, bukan hasil uji laboratorium. Foto hanya ilustrasi penyajian.

Foto area Alam Sutera dan gedung BINUS disediakan pengguna. Outlet Gading Serpong memakai outlet-illustration.png, gambar dapur warteg fiktif yang dibuat dengan AI. Seluruh data gerai tetap berupa contoh demonstrasi.

Sumber gambar menu contoh:
- nasi-ayam.jpg: https://i.gojekapi.com/darkroom/gofood-indonesia/v2/images/uploads/3cde3e28-af0a-4715-b618-0f3399599b2d_Go-Food-Merchant_20250506_094624.jpeg
- nasi-lengkap.jpg: https://i.gojekapi.com/darkroom/gofood-indonesia/v2/images/uploads/b4d696e7-e1df-4d2c-b164-62d92a180577_Go-Food-Merchant_20250823_181115.jpeg
- nasi-goreng.jpg: https://images.unsplash.com/photo-1603133872878-684f208fb84b
- rice-bowl.jpg: https://images.unsplash.com/photo-1546069901-ba9599a7e63c

Ganti dengan foto menu dan outlet milik sendiri sebelum digunakan untuk operasional.
