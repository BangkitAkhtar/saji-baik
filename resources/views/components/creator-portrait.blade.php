@props(['creator'])
@php($defaultPhoto = config('creators.photos.'.$creator->student_id))
@php($photoUrl = $creator->photo_path ? route('creators.photo',['creator'=>$creator,'v'=>$creator->updated_at->timestamp]) : ($defaultPhoto ? asset($defaultPhoto) : null))
<div {{ $attributes->merge(['class'=>'creator-portrait']) }}>
@if($photoUrl)<img src="{{ $photoUrl }}" alt="Foto {{ $creator->name }}" width="400" height="400" loading="lazy">
@else<div class="creator-placeholder" role="img" aria-label="Foto {{ $creator->name }} belum ditambahkan"><span>{{ $creator->initials() }}</span><small>Foto belum ditambahkan</small><i>✳</i></div>@endif
</div>
