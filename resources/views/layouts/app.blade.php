@php
    // $title is the short page title; the layout appends the brand, like the Next.js title template. The home page passes none.
    $fullTitle = isset($title) && $title ? $title.' · '.site('shortName') : \App\Support\Seo::defaultTitle();
    $metaDescription = $description ?? site('description');
    $ogTitle = isset($title) && $title ? $title.' · '.site('shortName') : \App\Support\Seo::defaultTitle();
    $ogImage = absolute_url(image_src('heroRoad'));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="application-name" content="{{ site('name') }}">
    <meta name="keywords" content="{{ implode(', ', site('seo.keywords')) }}">
    <meta name="theme-color" content="#14224a">
    @if (! empty($robots))
        <meta name="robots" content="{{ $robots }}">
    @elseif (! empty($noindex))
        <meta name="robots" content="noindex">
    @endif
    @if (isset($canonical))
        <link rel="canonical" href="{{ absolute_url($canonical) }}">
    @endif
    @if (env('GOOGLE_SITE_VERIFICATION'))
        <meta name="google-site-verification" content="{{ env('GOOGLE_SITE_VERIFICATION') }}">
    @endif
    @if (env('BING_SITE_VERIFICATION'))
        <meta name="msvalidate.01" content="{{ env('BING_SITE_VERIFICATION') }}">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ site('name') }}">
    <meta property="og:locale" content="en">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    @if (isset($canonical))
        <meta property="og:url" content="{{ absolute_url($canonical) }}">
    @endif
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="{{ image_alt('heroRoad') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    <link rel="icon" href="/icon.png" type="image/png" sizes="512x512">
    <link rel="apple-touch-icon" href="/apple-icon.png" sizes="180x180">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-dvh flex-col antialiased">
    <a href="#main" class="sr-only z-[100] rounded-control bg-brand px-4 py-3 font-semibold text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4">{{ label('a11y.skip') }}</a>
    <div data-scroll-progress aria-hidden="true" class="fixed inset-x-0 top-0 z-[90] h-1 origin-left scale-x-0 bg-gold print:hidden"></div>
    @include('partials.utility-bar')
    @include('partials.site-header')
    <main id="main" tabindex="-1" class="flex-1 focus:outline-none">
        {{ $slot ?? '' }}
        @yield('content')
    </main>
    @include('partials.site-footer')
    @include('partials.quick-contact')
    @include('partials.back-to-top')
    <x-json-ld :data="[\App\Support\Seo::organization(), \App\Support\Seo::website()]" />
    @stack('scripts')
</body>
</html>
