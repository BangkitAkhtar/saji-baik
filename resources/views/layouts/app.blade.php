<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#344a35">
    <meta name="description" content="Makan enak, bergizi, dan lebih terjangkau untuk mahasiswa, pekerja, dan orang dewasa yang membutuhkan. Pesan, lalu ambil di outlet.">
    <title>@yield('title', 'Makan baik, hari lebih baik') — Saji Baik</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
<a class="skip-link" href="#main">Lewati ke konten</a>
<div class="announcement">Seporsi kebaikan untuk hari yang lebih ringan. <span>Mulai Rp8.000 ✦ Ambil di outlet</span></div>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="{{ route('home') }}"><span class="brand-mark"><x-icon name="bowl"/></span>saji<span class="brand-light">baik</span><span class="brand-dot">.</span></a>
        <button class="menu-toggle icon-button" aria-label="Buka navigasi" aria-expanded="false" aria-controls="navigation"><x-icon name="menu"/></button>
        <nav id="navigation" aria-label="Navigasi utama">
            <a class="{{ request()->routeIs('home')?'active':'' }}" href="{{ route('home') }}">Beranda</a>
            <a class="{{ request()->routeIs('menu.*')?'active':'' }}" href="{{ route('menu.index') }}">Menu hari ini</a>
            <a class="{{ request()->routeIs('outlets')?'active':'' }}" href="{{ route('outlets') }}">Outlet</a>
            <a class="{{ request()->routeIs('about')?'active':'' }}" href="{{ route('about') }}">Tentang kami</a>
        </nav>
        <div class="nav-actions">
            @auth
                <a class="orders-link" href="{{ route('orders.index') }}"><x-icon name="bag"/><span>Pesanan saya</span></a>
                @if(auth()->user()->is_admin)<a class="button small" href="{{ route('admin.index') }}">Kelola</a>@else<a class="avatar" href="{{ route('orders.index') }}" aria-label="Akun {{ auth()->user()->name }}">{{ mb_substr(auth()->user()->name,0,1) }}</a>@endif
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-button logout-button">Keluar</button></form>
            @else
                <a class="login-link" href="{{ route('login') }}">Masuk</a><a class="button small" href="{{ route('register') }}">Jadi teman Saji <x-icon name="arrow"/></a>
            @endauth
        </div>
    </div>
</header>
<main id="main">
    @if(session('success'))<div class="container"><div class="alert success" role="status"><x-icon name="check"/>{{ session('success') }}</div></div>@endif
    @if($errors->any())<div class="container"><div class="alert error" role="alert"><strong>Periksa kembali, ya.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
    @yield('content')
</main>
<footer>
    <div class="container footer-main">
        <div><a class="brand" href="{{ route('home') }}"><span class="brand-mark"><x-icon name="bowl"/></span>saji<span class="brand-light">baik</span>.</a><p>Perut kenyang. Pikiran tenang.<br>Kebaikan yang bisa kamu jangkau.</p></div>
        <div><h4>Jelajahi</h4><a href="{{ route('menu.index') }}">Menu hari ini</a><a href="{{ route('outlets') }}">Temukan outlet</a><a href="{{ route('orders.index') }}">Pesanan saya</a></div>
        <div><h4>Kenalan dulu</h4><a href="{{ route('about') }}">Cerita Saji Baik</a><a href="{{ route('about') }}#cara-kerja">Cara pesan</a><a href="{{ route('about') }}#pertanyaan">Pertanyaan umum</a></div>
        <div class="footer-note"><span class="eyebrow">DARI DAPUR, UNTUK SESAMA</span><p>Untuk mahasiswa, pekerja, dan orang dewasa yang merasa terbantu dengan makanan lebih terjangkau.</p><span class="sdg-mini">SDG 2 · Tanpa Kelaparan</span></div>
    </div>
    <div class="container footer-creators"><div><span class="eyebrow">DIBUAT DENGAN KEPEDULIAN</span><h4>Tim creator Saji Baik</h4><a class="link-arrow" href="{{ route('about') }}#tim">Kenalan dengan kami <x-icon name="arrow"/></a></div><div class="footer-creator-list">@foreach($creators as $creator)<div><strong>{{ $creator->name }}</strong><span>{{ $creator->student_id }}</span></div>@endforeach</div></div>
    <div class="container footer-bottom"><span>© {{ date('Y') }} Saji Baik. Seporsi untuk sesama.</span><span>Versi demo · Menu, harga, dan outlet adalah ilustrasi.</span></div>
</footer>
</body></html>