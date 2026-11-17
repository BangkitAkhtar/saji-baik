@props(['creator'])
<div {{ $attributes->merge(['class'=>'creator-portrait']) }}>
@if($creator->photo_path)<img src="{{ route('creators.photo',['creator'=>$creator,'v'=>$creator->updated_at->timestamp]) }}" alt="Foto {{ $creator->name }}" width="400" height="400" loading="lazy">
@else<div class="creator-placeholder" role="img" aria-label="Foto {{ $creator->name }} belum ditambahkan"><span>{{ $creator->initials() }}</span><small>Foto belum ditambahkan</small><i>✳</i></div>@endif
</div>