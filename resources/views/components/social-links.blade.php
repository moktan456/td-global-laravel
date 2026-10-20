@props(['linkClass' => ''])
{{-- Renders only the profiles that have a URL in site.social. --}}
@php
    $items = [['facebook', 'Facebook'], ['x', 'X (Twitter)'], ['linkedin', 'LinkedIn']];
    $links = array_values(array_filter($items, fn ($i) => site('social.'.$i[0])));
@endphp
@if ($links)
    <ul {{ $attributes }} aria-label="Social media">
        @foreach ($links as [$key, $label])
            <li>
                <a href="{{ site('social.'.$key) }}" class="inline-flex size-11 items-center justify-center rounded-full transition-colors duration-200 {{ $linkClass }}" target="_blank" rel="noopener noreferrer">
                    <x-brand-icon :name="$key" class="size-5" />
                    <span class="sr-only">{{ site('name') }} on {{ $label }} (opens in a new tab)</span>
                </a>
            </li>
        @endforeach
    </ul>
@endif
