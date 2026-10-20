@props(['name'])
@php
    // Same Lucide icons the Next.js site uses (copied to resources/svg/lucide). Same markup lucide-react renders:
    // a 24px box with a currentColor stroke; the size is set by the class on the element (size-4, size-5, ...).
    $path = resource_path("svg/lucide/{$name}.svg");
    $markup = is_file($path) ? file_get_contents($path) : '';
    $markup = preg_replace('/<!--.*?-->\s*/s', '', $markup);
    $attrs = trim((string) $attributes->merge(['class' => "lucide lucide-{$name}", 'aria-hidden' => 'true']));
    $markup = preg_replace('/<svg\s+class="[^"]*"/', '<svg '.$attrs, $markup, 1);
@endphp
{!! $markup !!}
