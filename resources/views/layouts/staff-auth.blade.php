@php
    // Sign-in and "choose your password": a plain centred card, no site frame and no banner. Needs $title; optional $lead.
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · Staff · {{ site('shortName') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#14224a">
    <link rel="icon" href="/icon.png" type="image/png" sizes="512x512">
    <link rel="apple-touch-icon" href="/apple-icon.png" sizes="180x180">
    <script>
        // Light or dark: the person's choice (account menu), else the device's. Set early so there is no flash.
        (function () {
            var t = null;
            try { t = localStorage.getItem('staff-theme'); } catch (e) {}
            if (t !== 'light' && t !== 'dark') t = window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.dataset.theme = t;
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-ivory antialiased">
    <a href="#main" class="sr-only z-[100] rounded-control bg-brand px-4 py-3 font-semibold text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Skip to main content</a>
    <main id="main" tabindex="-1" class="mx-auto flex min-h-dvh max-w-md flex-col justify-center px-4 py-10 focus:outline-none sm:px-0">
        <a href="/" class="mx-auto inline-flex min-h-11 items-center rounded-control">
            <span class="inline-flex rounded-tile bg-white px-3 py-2"><img src="/brand/logo.png" alt="" width="974" height="466" class="h-14 w-auto"></span>
            <span class="sr-only">{{ site('name') }} — website</span>
        </a>
        <div class="mt-6 rounded-card border border-line bg-surface p-6 shadow-sm sm:p-8">
            <h1 class="font-display text-2xl font-semibold text-navy-900">{{ $title }}</h1>
            @if (! empty($lead))
                <p class="mt-2 text-muted">{{ $lead }}</p>
            @endif
            <div class="mt-6">
                @yield('content')
            </div>
        </div>
        @auth
            <form method="post" action="/staff/logout" class="mt-4 text-center">
                @csrf
                <button type="submit" class="min-h-11 rounded-control px-3 text-sm font-semibold text-navy-900 underline underline-offset-4 hover:text-brand">Not you? Sign out</button>
            </form>
        @endauth
        <p class="mt-6 text-center text-sm text-muted">TD Global staff area · <a href="/" class="underline underline-offset-4 hover:text-navy-900">Back to the website</a></p>
    </main>
</body>
</html>
