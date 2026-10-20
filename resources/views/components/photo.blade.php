@props(['name', 'sizes' => '100vw', 'priority' => false, 'decorative' => false, 'zoom' => false])
{{-- A photo from the image manifest that fills its (relatively positioned, sized) parent. --}}
<img
    src="{{ image_src($name) }}"
    @if ($srcset = image_srcset($name)) srcset="{{ $srcset }}" @endif
    alt="{{ $decorative ? '' : image_alt($name) }}"
    sizes="{{ $sizes }}"
    @if ($priority) fetchpriority="high" loading="eager" @else loading="lazy" @endif
    decoding="async"
    style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent"
    {{ $attributes->merge(['class' => 'object-cover '.($zoom ? 'transition-transform duration-700 ease-out-quart group-hover:scale-[1.04] group-focus-within:scale-[1.04]' : '')]) }}
>
