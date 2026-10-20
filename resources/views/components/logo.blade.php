@props(['dark' => false])
{{-- The TD Global Clearing & Logistics logo (public/brand/logo.png, transparent). On dark backgrounds it sits on a white tile so the white khata stays visible. --}}
@php
    $img = '<img src="/brand/logo.png" alt="" width="974" height="466" '.($dark ? 'loading="lazy"' : 'fetchpriority="high"').' decoding="async" class="'.($dark ? 'h-16 w-auto' : 'h-14 w-auto lg:h-[4.5rem]').'">';
@endphp
<a href="/" class="group inline-flex min-h-11 items-center rounded-control">
    @if ($dark)
        <span class="inline-flex rounded-tile bg-white px-3 py-2">{!! $img !!}</span>
    @else
        {!! $img !!}
    @endif
    <span class="sr-only">{{ site('name') }} — home</span>
</a>
