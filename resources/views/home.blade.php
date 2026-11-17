@extends('layouts.app')
@section('content')
<section class="hero container">
    <div class="hero-copy">
        <div class="eyebrow pill"><span class="live-dot"></span> MAKAN BAIK, UNTUK SEMUA YANG MEMBUTUHKAN</div>
        <h1>Isi piringmu.<br><em>Ringankan</em><br>harimu.</h1>
        <p>Makan enak dan bergizi nggak harus mahal. Menu rumahan untuk mahasiswa, pekerja, dan siapa pun yang butuh sedikit keringanan.</p>
        <div class="hero-buttons"><a class="button" href="{{ route('menu.index') }}">Lihat menu hari ini <x-icon name="arrow"/></a><a class="underlined-link" href="{{ route('about') }}#cara-kerja">Gimana cara pesannya?</a></div>
        <div class="hero-points"><span><x-icon name="check"/>Dimasak hari ini</span><span><x-icon name="check"/>Bayar di outlet</span><span><x-icon name="check"/>Tanpa biaya antar</span></div>
    </div>
    <div class="hero-visual">
        <div class="hero-circle"></div><span class="hero-scribble">seporsi baik,<br>buat kamu ♡</span>
        <img class="hero-photo" src="{{ asset('images/nasi-ayam.jpg') }}" alt="Sepiring nasi, ayam, tempe, dan sayuran" width="500" height="500" fetchpriority="high">
        <div class="floating-price"><span>BAIK DI PERUT & DI KANTONG</span><strong>Mulai Rp8.000</strong><small>Porsi lengkap, hati pun hangat.</small></div>
        <div class="floating-leaf"><x-icon name="leaf"/><span>Lauk + sayur<br><strong>Seporsi seimbang</strong></span></div>
        <span class="hero-star">✳</span>
    </div>
</section>
<div class="values-strip"><div class="container"><span><x-icon name="bowl"/> Rasa rumahan</span><i>✦</i><span><x-icon name="wallet"/> Harga bersahabat</span><i>✦</i><span><x-icon name="leaf"/> Lauk & sayur setiap porsi</span><i>✦</i><span><x-icon name="people"/> Dari dapur untuk sesama</span></div></div>
<section class="container section">
    <div class="section-heading"><div><span class="eyebrow">MAU MAKAN APA HARI INI?</span><h2>Enak, cukup, <em>terjangkau.</em></h2><p>Menu familiar yang bikin kenyang, tanpa bikin dompet tegang.</p></div><a class="link-arrow" href="{{ route('menu.index') }}">Semua menu <x-icon name="arrow"/></a></div>
    <div class="category-links"><a class="chip selected" href="{{ route('menu.index') }}"><x-icon name="bowl"/> Semua menu</a>@foreach(\App\Models\Meal::CATEGORIES as $category)<a class="chip" href="{{ route('menu.index',['category'=>$category]) }}">{{ $category }}</a>@endforeach</div>
    <div class="meal-grid home-grid">@forelse($meals as $meal)<x-meal-card :meal="$meal"/>@empty<div class="empty-state"><x-icon name="sun"/><h3>Dapur sedang menyiapkan kebaikan.</h3><p>Menu hari ini belum tersedia. Cek kembali nanti, ya.</p><a class="button" href="{{ route('outlets') }}">Lihat outlet</a></div>@endforelse</div>
    <p class="photo-note">Foto adalah ilustrasi penyajian. Menu dan porsi mengikuti keterangan masing-masing outlet.</p>
</section>
<section class="container">
    <div class="kindness-panel"><div class="kindness-icon"><x-icon name="heart"/></div><div><span class="eyebrow">TANPA LABEL. TANPA RASA SUNGKAN.</span><h2>Merasa terbantu? <em>Kamu dipersilakan.</em></h2><p>Saji Baik ditujukan untuk mahasiswa, pekerja, pencari kerja, dan orang dewasa yang membutuhkan makanan terjangkau. Tidak perlu surat keterangan. Cukup jujur pada kebutuhanmu, dan ambil secukupnya agar yang lain juga kebagian.</p></div><a class="round-arrow light" href="{{ route('about') }}" aria-label="Tentang penerima manfaat"><x-icon name="arrow"/></a></div>
</section>
<section class="container section" id="cara-kerja">
    <div class="section-heading"><div><span class="eyebrow">DARI LAYAR, KE PIRINGMU</span><h2>Tiga langkah. <em>Satu perut kenyang.</em></h2></div><span class="soft-label">Sesederhana makan di warung favoritmu.</span></div>
    <div class="steps"><article><span class="step-number">01</span><div class="step-icon"><x-icon name="bowl"/></div><h3>Pilih yang kamu suka</h3><p>Cari menu sesuai selera dan kantongmu. Cek isi porsi, stok, dan outletnya.</p></article><article><span class="step-number">02</span><div class="step-icon"><x-icon name="clock"/></div><h3>Tentukan waktu ambil</h3><p>Pesan maksimal 2 porsi, pilih jam ambil, lalu simpan kode pesananmu.</p></article><article><span class="step-number">03</span><div class="step-icon"><x-icon name="bag"/></div><h3>Ambil, bayar, nikmati</h3><p>Datang ke outlet, tunjukkan kode, dan bayar langsung. Selamat makan!</p></article></div>
</section>
<section class="impact-section"><div class="container impact-inner"><div><span class="eyebrow">KEBAIKAN KECIL, TUJUAN BERSAMA</span><h2>Karena makan layak<br>layak <em>diusahakan.</em></h2><p>Kami berfokus pada SDG 2 — Tanpa Kelaparan, dengan membuka akses makanan enak, bergizi, dan lebih terjangkau bagi mahasiswa, pekerja, dan orang dewasa yang membutuhkan.</p><a class="button cream" href="{{ route('about') }}">Kenalan dengan Saji Baik <x-icon name="arrow"/></a></div><div class="sdg-cards sdg-focus"><div class="sdg-card mustard"><strong>02</strong><span>TANPA<br>KELAPARAN</span><x-icon name="bowl"/></div><p>Fokus kontribusi kami: SDG 2 — Tanpa Kelaparan.<br>Program mandiri, bukan bagian dari program MBG pemerintah.</p></div></div></section>
<x-impact-stats :stats="$impactStats"/><section class="container section faq-section"><div><span class="eyebrow">BIAR MAKIN YAKIN</span><h2>Yang sering<br><em>ditanyakan.</em></h2><p>Ada ruang untuk kamu di meja kami.</p><a class="link-arrow" href="{{ route('about') }}#pertanyaan">Baca selengkapnya <x-icon name="arrow"/></a></div><div class="faq-list"><details open><summary>Siapa yang boleh membeli?</summary><p>Mahasiswa, pekerja, pencari kerja, dan orang dewasa yang merasa membutuhkan harga lebih terjangkau. Tidak ada pemeriksaan penghasilan atau surat keterangan.</p></details><details><summary>Apakah makanannya sisa?</summary><p>Bukan. Menu disiapkan sebagai makanan harian dengan harga terjangkau, mengikuti jadwal dan ketersediaan tiap outlet.</p></details><details><summary>Bisa diantar ke rumah?</summary><p>Saat ini hanya ambil langsung di outlet. Kamu bisa memilih waktu pengambilan saat memesan.</p></details><details><summary>Bagaimana cara membayarnya?</summary><p>Pembayaran dilakukan langsung di outlet saat pengambilan. Siapkan uang pas; pilihan pembayaran lain dikonfirmasi ke petugas.</p></details></div></section>
@endsection