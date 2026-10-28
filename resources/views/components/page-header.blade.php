@props(['title', 'crumbs' => [], 'path' => null, 'intro' => null])
{{-- $path is the URL of the page itself. Pass it on public pages to also publish the breadcrumb trail as structured data. --}}
@php
    $trail = array_merge([['label' => 'Home', 'href' => '/']], $crumbs);
    $lastIndex = count($crumbs) - 1;
@endphp
<div class="on-dark bg-navy-900 text-white">
    @if ($path)
        <x-json-ld :data="\App\Support\Seo::breadcrumbs(array_map(fn ($c, $i) => ['name' => $c['label'], 'path' => $i === $lastIndex ? $path : ($c['href'] ?? $path)], $crumbs, array_keys($crumbs)))" />
    @endif
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-16 lg:py-20">
        <nav aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-1 text-sm text-on-dark-muted">
                @foreach ($trail as $i => $c)
                    @php($last = $i === count($trail) - 1)
                    <li class="flex items-center gap-1">
                        @if (! empty($c['href']) && ! $last)
                            <a href="{{ $c['href'] }}" class="inline-block py-2 underline-offset-4 hover:text-white hover:underline">{{ $c['label'] }}</a>
                        @else
                            <span @if ($last) aria-current="page" @endif class="py-2 text-on-dark">{{ $c['label'] }}</span>
                        @endif
                        @unless ($last)
                            <x-lucide name="chevron-right" class="size-4" />
                        @endunless
                    </li>
                @endforeach
            </ol>
        </nav>
        <h1 class="mt-4 text-pretty font-display text-3xl font-semibold leading-tight sm:text-4xl lg:text-5xl">{{ $title }}</h1>
        @if ($intro)
            <div class="mt-4 text-pretty text-lg text-on-dark-muted lg:text-xl">{{ $intro }}</div>
        @endif
    </div>
</div>
