@extends('layouts.app')
@section('title','Menu hari ini')
@section('content')
<section class="page-intro"><div class="container"><span class="eyebrow">DARI DAPUR HARI INI</span><h1>Pilih menu. <em>Isi energimu.</em></h1><p>Masakan sehari-hari, harga yang lebih bersahabat. Ambil di outlet pilihanmu.</p></div></section>
<section class="container catalog-section">
    <form class="filter-form" method="GET" action="{{ route('menu.index') }}">
        <div class="search-field"><x-icon name="search"/><input type="search" name="q" value="{{ request('q') }}" placeholder="Lagi ingin makan apa?" aria-label="Cari nama menu"></div>
        <select name="outlet" aria-label="Pilih outlet"><option value="">Semua outlet</option>@foreach($outlets as $outlet)<option value="{{ $outlet->id }}" @selected(request('outlet')==$outlet->id)>{{ $outlet->area }}</option>@endforeach</select>
        <select name="category" aria-label="Kategori"><option value="">Semua kategori</option>@foreach(\App\Models\Meal::CATEGORIES as $category)<option @selected(request('category')===$category)>{{ $category }}</option>@endforeach</select>
        <select name="max_price" aria-label="Batas harga"><option value="">Semua harga</option>@foreach([10000,12000,15000] as $price)<option value="{{ $price }}" @selected(request('max_price')==$price)>≤ Rp{{ number_format($price,0,',','.') }}</option>@endforeach</select>
        <button class="button">Cari menu <x-icon name="arrow"/></button>
        <div class="filter-bottom"><p><strong>{{ $meals->total() }} menu</strong> ditemukan untuk hari ini</p><div><label for="sort">Urutkan:</label><select id="sort" name="sort"><option value="recommended" @selected(request('sort')==='recommended')>Rekomendasi</option><option value="price_asc" @selected(request('sort')==='price_asc')>Harga terendah</option><option value="price_desc" @selected(request('sort')==='price_desc')>Harga tertinggi</option></select><button class="text-button">Terapkan</button></div></div>
    </form>
    @if(request()->hasAny(['q','category','outlet','max_price','sort']))<a class="reset-link" href="{{ route('menu.index') }}">× Reset semua filter</a>@endif
    <div class="meal-grid">@forelse($meals as $meal)<x-meal-card :meal="$meal"/>@empty<div class="empty-state"><x-icon name="bowl"/><h2>Belum ketemu yang pas?</h2><p>Coba kata kunci atau filter lain. Menu hari ini mungkin belum disiapkan pengelola.</p><a class="button" href="{{ route('menu.index') }}">Lihat semua menu</a></div>@endforelse</div>
    {{ $meals->links() }}
    <div class="small-notice"><x-icon name="heart"/><p>Harga khusus untuk yang merasa membutuhkan. Pesan secukupnya, maksimal 2 porsi per pesanan, supaya kebaikannya bisa sampai ke lebih banyak orang.</p></div>
    <p class="photo-note">Foto ilustrasi. Nilai gizi adalah estimasi per porsi, bukan hasil uji laboratorium. Periksa informasi alergen sebelum memesan.</p>
</section>
@endsection