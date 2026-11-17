# Saji Baik

Website Laravel untuk makanan harian yang lebih terjangkau bagi mahasiswa, pekerja, pencari kerja, dan orang dewasa yang merasa membutuhkan. Fokus SDG 2, dengan perencanaan porsi yang mendukung arah SDG 12. Inisiatif mandiri, bukan program MBG pemerintah.

## Fitur

- Beranda, katalog dengan pencarian/filter harga, kategori dan outlet.
- Detail menu, isi porsi, estimasi gizi, dan informasi alergen.
- Registrasi, login, dan pesanan milik masing-masing pelanggan.
- Pesan 1–2 porsi, pilih waktu ambil, pernyataan kebutuhan, bayar di outlet.
- Kode pengambilan, pembatalan, dan pembaruan stok dalam transaksi database.
- Meja pengelola: menu, sisa stok, tanggal tersedia, dan status pesanan.
- Tim creator pada footer dan Tentang Kami; unggah foto melalui halaman pengelola.
- Responsif untuk desktop dan mobile.

## Menjalankan

PHP >= 8.3, Composer, ekstensi SQLite, dan GD (untuk pengujian foto).
Aplikasi menggunakan Blade, CSS, dan JavaScript biasa; tidak memerlukan build frontend.

1. Jalankan composer install.
2. Salin .env.example ke .env.
3. Jalankan php artisan key:generate.
4. Buat database/database.sqlite jika belum tersedia.
5. Jalankan php artisan migrate --seed.
6. Jalankan php artisan serve, kemudian buka http://127.0.0.1:8000.

Di Laragon lokal proyek ini berada di C:\laragon\www\saji-baik dan dapat dibuka di http://localhost/saji-baik/public saat Apache aktif.

## Akun demo lokal

Seeder hanya membuat akun demo pada lingkungan local dan testing.

- Pelanggan: teman@sajibaik.test
- Pengelola: pengelola@sajibaik.test
- Kata sandi keduanya: SajiBaik!2026

Akun ini untuk demonstrasi lokal. Jangan gunakan kredensial demo untuk penerapan publik.

Menu memiliki tanggal ketersediaan dan stok harian. Pengelola perlu memperbarui tanggal, waktu ambil, dan sisa stok melalui Kelola menu setiap hari. Waktu ambil ditampilkan dalam WIB dan harus lebih dari 15 menit setelah waktu pemesanan.

## Tim creator

- Aviel Aquino — 2802393711
- Kent Nathan Herlambang — 2802397464
- Pharrel Jibril Tristan — 2802393964
- Bangkit Ahktar Fahd — 2802401625

Foto asli belum disediakan. Kartu menampilkan inisial sampai pengelola mengunggah foto masing-masing di /mitra/tim. Foto tersimpan pada penyimpanan lokal, bukan pada repositori.

## Pengujian

php artisan test

Data SQLite, .env, unggahan pribadi, cache, log, dan vendor tidak disertakan ke Git.

## Data dan gambar contoh

Seluruh outlet, harga, dan menu dalam seeder adalah ilustrasi dan belum menerima transaksi sungguhan. Nilai gizi adalah estimasi, bukan hasil uji laboratorium. Foto hanya ilustrasi penyajian.

Sumber gambar contoh:
- nasi-ayam.jpg: https://i.gojekapi.com/darkroom/gofood-indonesia/v2/images/uploads/3cde3e28-af0a-4715-b618-0f3399599b2d_Go-Food-Merchant_20250506_094624.jpeg
- nasi-lengkap.jpg: https://i.gojekapi.com/darkroom/gofood-indonesia/v2/images/uploads/b4d696e7-e1df-4d2c-b164-62d92a180577_Go-Food-Merchant_20250823_181115.jpeg
- nasi-goreng.jpg: https://images.unsplash.com/photo-1603133872878-684f208fb84b
- rice-bowl.jpg: https://images.unsplash.com/photo-1546069901-ba9599a7e63c

Ganti dengan foto menu dan outlet milik sendiri sebelum digunakan untuk operasional.
