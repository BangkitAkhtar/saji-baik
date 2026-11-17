@props(['meal'])
<article class="meal-card">
    <a class="meal-image" href="{{ route('menu.show',$meal->slug) }}">
        <img src="{{ asset('images/'.$meal->image) }}" alt="Ilustrasi {{ $meal->name }}" loading="lazy" width="700" height="500">
        <span class="image-tag">{{ $meal->category }}</span>
        @if($meal->stock===0)<span class="sold-out">Habis hari ini</span>@else<span class="stock-badge">{{ $meal->stock }} porsi</span>@endif
    </a>
    <div class="meal-content">
        <div class="meal-location"><x-icon name="pin"/>{{ $meal->outlet->area }}</div>
        <h3><a href="{{ route('menu.show',$meal->slug) }}">{{ $meal->name }}</a></h3>
        <p class="meal-meta">{{ $meal->calories }} kkal <span>·</span> {{ $meal->protein }}g protein <span class="estimate">estimasi</span></p>
        <div class="card-divider"></div>
        <div class="meal-bottom"><div class="price">Rp{{ number_format($meal->price,0,',','.') }}<small>/ porsi</small></div><a class="round-arrow" href="{{ route('menu.show',$meal->slug) }}" aria-label="Lihat {{ $meal->name }}"><x-icon name="arrow"/></a></div>
    </div>
</article>