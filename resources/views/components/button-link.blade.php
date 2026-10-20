@props(['href', 'variant' => 'primary'])
<a href="{{ $href }}" {{ $attributes->merge(['class' => ui('button', $variant)]) }}>{{ $slot }}</a>
