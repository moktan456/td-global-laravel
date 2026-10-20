@props(['variant' => 'primary', 'type' => 'button'])
<button type="{{ $type }}" {{ $attributes->merge(['class' => ui('button', $variant)]) }}>{{ $slot }}</button>
