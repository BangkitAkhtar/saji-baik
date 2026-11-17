@props(['name'=>'arrow'])
<svg {{ $attributes->merge(['class'=>'icon','viewBox'=>'0 0 24 24','fill'=>'none','stroke'=>'currentColor','stroke-width'=>'1.7','stroke-linecap'=>'round','stroke-linejoin'=>'round','aria-hidden'=>'true']) }}>
@switch($name)
@case('bowl')<path d="M3 12h18a9 9 0 0 1-18 0ZM7 21h10M8 3c-3 3 3 3 0 6M13 2c-3 3 3 3 0 6M18 3c-3 3 3 3 0 6"/>@break
@case('arrow')<path d="M5 12h14m-6-6 6 6-6 6"/>@break
@case('pin')<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>@break
@case('clock')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
@case('leaf')<path d="M20 3C7 1 1 9 7 17c8 6 16 0 13-14ZM5 21 17 9"/>@break
@case('check')<path d="m5 12 4 4L19 6"/>@break
@case('shield')<path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6l8-3Z"/><path d="m8 12 3 3 5-6"/>@break
@case('bag')<path d="M5 7h14l2 14H3L5 7ZM8 8V6a4 4 0 0 1 8 0v2"/>@break
@case('search')<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>@break
@case('menu')<path d="M4 6h16M4 12h16M4 18h16"/>@break
@case('heart')<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0l-1 1-1-1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/>@break
@case('sun')<circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/>@break
@case('wallet')<path d="M20 7H4V4h14v3M4 7v13h17V7H4ZM16 12h5v4h-5z"/>@break
@case('people')<circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6m2 3c3 0 4 2 4 4v3"/>@break
@default<path d="M5 12h14m-6-6 6 6-6 6"/>
@endswitch
</svg>