<?php

namespace Database\Seeders;

use App\Models\Creator;
use App\Models\Meal;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Aviel Aquino', '2802393711'],
            ['Kent Nathan Herlambang', '2802397464'],
            ['Pharrel Jibril Tristan', '2802393964'],
            ['Bangkit Ahktar Fahd', '2802401625'],
        ] as $position => [$name,$nim]) {
            Creator::firstOrCreate(['student_id' => $nim], ['name' => $name, 'position' => $position]);
        }
        if (app()->environment(['local', 'testing'])) {
            $admin = User::firstOrNew(['email' => 'pengelola@sajibaik.test']);
            $admin->fill(['name' => 'Pengelola Saji Baik', 'password' => 'SajiBaik!2026', 'phone' => '081234567890']);
            $admin->is_admin = true;
            $admin->save();
            User::firstOrCreate(['email' => 'teman@sajibaik.test'], ['name' => 'Teman Saji', 'password' => 'SajiBaik!2026', 'phone' => '081234567891']);
        }
        $locations = [
            ['name' => 'Dapur Alam Sutera', 'image' => 'outlet-alam-sutera-ai.png', 'area' => 'Alam Sutera', 'address' => 'Jl. Jalur Sutera Barat No. 12, Tangerang', 'description' => 'Dekat area kampus. Cari papan Saji Baik di meja depan.'],
            ['name' => 'Dapur Kemanggisan', 'image' => 'outlet-kemanggisan-ai.png', 'area' => 'Kemanggisan', 'address' => 'Jl. Kebon Jeruk Raya No. 8, Jakarta Barat', 'description' => 'Di area warung makan, pengambilan melalui kasir utama.'],
            ['name' => 'Dapur Gading Serpong', 'area' => 'Gading Serpong', 'address' => 'Jl. Boulevard Gading Serpong No. 21, Tangerang', 'description' => 'Masuk dari pintu depan ruko, lalu tunjukkan kode ambil.'],
        ];
        $outlets = collect($locations)->map(fn ($d) => Outlet::firstOrCreate(['name' => $d['name']], $d));
        $menus = [
            ['Nasi Ayam Kecap', 'Nasi rumahan', 12000, 24, 0, 'nasi-ayam.jpg', 580, 28, 'Nasi putih, Ayam kecap, Tumis sayur, Tempe, Sambal terpisah', 'Kedelai, gluten', 'Ayam berbumbu kecap yang gurih, sayur harian, dan tempe. Rasa rumahan yang selalu bikin pulang.'],
            ['Paket Warteg Komplet', 'Nasi rumahan', 15000, 18, 1, 'nasi-lengkap.jpg', 640, 30, 'Nasi putih, Ayam goreng, Tahu, Tempe, Sayur, Sambal terpisah', 'Kedelai', 'Seporsi lengkap untuk hari yang panjang. Lauk ayam, protein nabati, dan sayur dalam satu kotak.'],
            ['Nasi Telur Sayur', 'Nasi rumahan', 8000, 30, 0, 'nasi-lengkap.jpg', 490, 18, 'Nasi putih, Telur dadar, Tumis sayur, Tempe', 'Telur, kedelai', 'Sederhana, akrab, dan mengenyangkan. Telur dadar, tempe, dan sayur segar yang dimasak hari ini.'],
            ['Rice Bowl Ayam Teriyaki', 'Rice bowl', 14000, 16, 2, 'rice-bowl.jpg', 560, 27, 'Nasi putih, Ayam teriyaki, Wortel, Buncis, Wijen', 'Kedelai, gluten, wijen', 'Ayam teriyaki gurih manis dengan nasi hangat dan sayuran. Praktis dibawa kembali ke aktivitasmu.'],
            ['Nasi Tempe Tahu', 'Tanpa daging', 9000, 22, 1, 'nasi-lengkap.jpg', 460, 19, 'Nasi putih, Tempe orek, Tahu, Tumis sayur', 'Kedelai', 'Lauk nabati kesukaan sehari-hari. Tempe orek dan tahu, dilengkapi sayuran yang berganti sesuai musim.'],
            ['Nasi Goreng Telur', 'Nasi rumahan', 10000, 20, 2, 'nasi-goreng.jpg', 570, 17, 'Nasi goreng, Telur, Sayuran, Mentimun', 'Telur, kedelai, gluten', 'Nasi goreng dengan bumbu sederhana, telur, dan sayuran. Pedasnya ringan, sambal bisa dipisah.'],
            ['Rice Bowl Ayam Sambal', 'Rice bowl', 13000, 12, 0, 'nasi-ayam.jpg', 550, 26, 'Nasi putih, Ayam suwir, Sayur, Tempe, Sambal terpisah', 'Kedelai', 'Ayam suwir berbumbu dan sambal terpisah. Teman makan siang yang pas setelah kelas atau kerja.'],
            ['Nasi Sayur Nusantara', 'Tanpa daging', 10000, 15, 2, 'nasi-lengkap.jpg', 450, 16, 'Nasi putih, Sayur lodeh, Tempe, Tahu', 'Kedelai, santan', 'Sayuran berkuah gurih, tahu dan tempe. Menu tanpa daging dengan rasa dapur Indonesia.'],
        ];
        foreach ($menus as [$name,$category,$price,$stock,$i,$image,$cal,$protein,$components,$allergens,$description]) {
            Meal::firstOrCreate(['slug' => Str::slug($name)], [
                'outlet_id' => $outlets[$i]->id, 'name' => $name, 'category' => $category, 'price' => $price, 'stock' => $stock,
                'available_date' => today(), 'pickup_start' => '10:00', 'pickup_end' => '20:00', 'image' => $image,
                'calories' => $cal, 'protein' => $protein, 'components' => explode(', ', $components), 'allergens' => $allergens, 'description' => $description, 'is_active' => true,
            ]);
        }
    }
}
