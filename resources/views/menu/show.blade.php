@extends('layouts.app')
@section('title',$meal->name)
@section('content')
<div class="container section compact"><a class="back-link" href="{{ route('menu.index') }}">← Kembali ke menu</a>
<div class="detail-grid">
    <div><div class="detail-image"><img src="{{ asset('images/'.$meal->image) }}" alt="Ilustrasi {{ $meal->name }}" width="700" height="700"><span class="image-tag">{{ $meal->category }}</span></div><p class="photo-note">Foto ilustrasi. Penyajian dan sayur dapat berbeda sesuai ketersediaan.</p></div>
    <div class="detail-copy"><span class="eyebrow"><x-icon name="sun"/> DIMASAK UNTUK HARI INI</span><h1>{{ $meal->name }}</h1><p class="lead">{{ $meal->description }}</p><div class="detail-price">Rp{{ number_format($meal->price,0,',','.') }} <span>/ porsi</span></div>
    <div class="nutrition"><div><strong>{{ $meal->calories }}</strong><span>kkal / porsi</span></div><div><strong>{{ $meal->protein }}g</strong><span>protein / porsi</span></div><div><x-icon name="leaf"/><span>Lauk + sayur</span></div></div><p class="photo-note">Nilai gizi merupakan estimasi, bukan hasil uji laboratorium.</p>
    <h3>Ada apa di dalamnya?</h3><div class="component-tags">@foreach($meal->components as $component)<span><x-icon name="check"/>{{ $component }}</span>@endforeach</div>
    <div class="allergen"><strong>Informasi alergen</strong><p>Mengandung: {{ $meal->allergens }}. Dapur bersama dapat memiliki kontak silang. Jika punya alergi, konfirmasi ke outlet sebelum membeli.</p></div>
    <div class="pickup-summary"><x-icon name="pin"/><div><strong>{{ $meal->outlet->name }}</strong><p>{{ $meal->outlet->address }}</p><span><x-icon name="clock"/> {{ $meal->available_date->translatedFormat('d F Y') }} · {{ $meal->pickup_start }}–{{ $meal->pickup_end }} WIB</span></div></div>
    @if($meal->canOrder())<div class="availability"><span class="live-dot"></span> Masih ada {{ $meal->stock }} porsi</div><a class="button full" href="{{ route('orders.create',$meal->slug) }}">Pesan & ambil di outlet <x-icon name="arrow"/></a>@else<div class="alert">Menu ini sedang tidak tersedia untuk dipesan. Stok habis atau waktu pengambilan sudah lewat.</div><a class="button full" href="{{ route('menu.index') }}">Cari menu lain <x-icon name="arrow"/></a>@endif
    <x-pickup-notice :outlet="$meal->outlet"/><p class="payment-note"><x-icon name="shield"/> Bayar di outlet. Tidak ada biaya layanan atau pengantaran.</p>
    </div>
</div></div>
<section class="container section"><div class="section-heading"><div><span class="eyebrow">PILIHAN BAIK LAINNYA</span><h2>Mungkin kamu <em>juga suka.</em></h2></div></div><div class="meal-grid">@foreach($related as $meal)<x-meal-card :meal="$meal"/>@endforeach</div></section>
@endsection