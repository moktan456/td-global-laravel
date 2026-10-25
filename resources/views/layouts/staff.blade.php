@php
    // The staff workspace: its own frame (sidebar + slim title bar), not the public website's header, banner and footer.
    // $title names the page (and the browser tab); $crumbs adds a small trail above it; $subtitle sits under the title.
    // Sections: "content" (the page) and "actions" (buttons on the right of the title bar).
    $me = auth()->user();
    $work = [
        ['href' => '/staff', 'label' => 'Shipments', 'icon' => 'package', 'on' => request()->is('staff') || (request()->is('staff/shipments/*') && ! request()->is('staff/shipments/new'))],
        ['href' => '/staff/shipments/new', 'label' => 'New request', 'icon' => 'package-plus', 'on' => request()->is('staff/shipments/new')],
        ['href' => '/staff/clients', 'label' => 'Clients', 'icon' => 'users', 'on' => request()->is('staff/clients*')],
    ];
    $admin = $me?->role === 'admin' ? [
        ['href' => '/staff/team', 'label' => 'Team', 'icon' => 'shield-user', 'on' => request()->is('staff/team*')],
        ['href' => '/staff/telegram', 'label' => 'Telegram', 'icon' => 'send', 'on' => request()->is('staff/telegram*')],
    ] : [];
    $groups = array_filter(['Work' => $work, 'Admin' => $admin]);
    $linkClass = fn (bool $on) => 'flex min-h-11 items-center gap-3 rounded-control px-3 font-semibold transition-colors duration-150 '.($on ? 'bg-white/12 text-white shadow-[inset_3px_0_0_var(--color-gold)]' : 'text-on-dark-muted hover:bg-white/8 hover:text-white');
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-pb-24">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · Staff · {{ site('shortName') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#14224a">
    <link rel="icon" href="/icon.png" type="image/png" sizes="512x512">
    <link rel="apple-touch-icon" href="/apple-icon.png" sizes="180x180">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-ivory antialiased lg:flex">
    <a href="#main" class="sr-only z-[100] rounded-control bg-brand px-4 py-3 font-semibold text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Skip to main content</a>

    {{-- Desktop: the sidebar. --}}
    <aside class="on-dark hidden shrink-0 flex-col bg-navy-900 text-white lg:sticky lg:top-0 lg:flex lg:h-dvh lg:w-64">
        <div class="px-4 pb-4 pt-5">
            <a href="/staff" class="inline-flex min-h-11 items-center gap-3 rounded-control">
                <span class="inline-flex rounded-tile bg-white px-2 py-1.5"><img src="/brand/logo.png" alt="" width="974" height="466" class="h-9 w-auto"></span>
                <span class="font-display text-lg font-semibold leading-tight">Staff<br>workspace</span>
            </a>
        </div>
<form method="get" action="/staff/go" role="search" aria-label="Find a shipment" class="px-4 pb-2">
                <label for="find-side" class="sr-only">Find a shipment by tracking number</label>
                <div class="relative">
                    <x-lucide name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-on-dark-muted" />
                    <input id="find-side" data-find type="search" name="n" placeholder="Find a shipment" autocomplete="off" class="min-h-11 w-full rounded-control border border-white/30 bg-white/10 pl-9 pr-8 text-white placeholder:text-on-dark-muted focus:bg-white/15">
                    <kbd aria-hidden="true" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 rounded border border-white/30 px-1.5 text-xs text-on-dark-muted max-lg:hidden">/</kbd>
                </div>
            </form>
        <nav aria-label="Staff" class="flex-1 overflow-y-auto px-3 pb-4">
            @foreach ($groups as $groupName => $items)
                <p class="mt-4 px-3 pb-2 text-xs font-bold uppercase tracking-widest text-on-dark-muted">{{ $groupName }}</p>
                <ul class="grid gap-1">
                    @foreach ($items as $l)
                        <li><a href="{{ $l['href'] }}" @if ($l['on']) aria-current="page" @endif class="{{ $linkClass($l['on']) }}"><x-lucide :name="$l['icon']" class="size-5 shrink-0" />{{ $l['label'] }}</a></li>
                    @endforeach
                </ul>
            @endforeach
        </nav>
        <div class="border-t border-white/15 p-3">
            <x-staff-account />
        </div>
    </aside>

    <div class="min-w-0 flex-1">
        {{-- Phone and tablet: a slim top bar with the same menu. --}}
        <header class="on-dark relative bg-navy-900 text-white lg:hidden">
            <div class="flex items-center justify-between gap-3 px-4 py-2">
                <a href="/staff" class="inline-flex min-h-11 items-center gap-3 rounded-control">
                    <span class="inline-flex rounded-tile bg-white px-2 py-1"><img src="/brand/logo.png" alt="" width="974" height="466" class="h-8 w-auto"></span>
                    <span class="font-display font-semibold">Staff workspace</span>
                </a>
                <details data-menu>
                    <summary class="inline-flex min-h-11 cursor-pointer list-none items-center gap-2 rounded-control border border-white/40 px-4 font-semibold [&::-webkit-details-marker]:hidden"><x-lucide name="menu" class="size-5" />Menu</summary>
                    <div class="absolute inset-x-0 top-full z-30 bg-navy-900 px-4 pb-4 shadow-lg">
<form method="get" action="/staff/go" role="search" aria-label="Find a shipment" class="pt-1">
                    <label for="find-menu" class="sr-only">Find a shipment by tracking number</label>
                    <div class="relative">
                        <x-lucide name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-on-dark-muted" />
                        <input id="find-menu" data-find type="search" name="n" placeholder="Find a shipment" autocomplete="off" class="min-h-11 w-full rounded-control border border-white/30 bg-white/10 pl-9 pr-8 text-white placeholder:text-on-dark-muted focus:bg-white/15">
                        <kbd aria-hidden="true" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 rounded border border-white/30 px-1.5 text-xs text-on-dark-muted max-lg:hidden">/</kbd>
                    </div>
            </form>
                        <nav aria-label="Staff (menu)">
                            @foreach ($groups as $groupName => $items)
                                <p class="mt-3 px-3 pb-2 text-xs font-bold uppercase tracking-widest text-on-dark-muted">{{ $groupName }}</p>
                                <ul class="grid gap-1">
                                    @foreach ($items as $l)
                                        <li><a href="{{ $l['href'] }}" @if ($l['on']) aria-current="page" @endif class="{{ $linkClass($l['on']) }}"><x-lucide :name="$l['icon']" class="size-5 shrink-0" />{{ $l['label'] }}</a></li>
                                    @endforeach
                                </ul>
                            @endforeach
                        </nav>
                        <div class="mt-4 border-t border-white/15 pt-3">
                            <x-staff-account variant="menu" />
                        </div>
                    </div>
                </details>
            </div>
        </header>

        <div class="border-b border-line bg-white">
            <div class="mx-auto flex max-w-7xl flex-wrap items-end justify-between gap-x-6 gap-y-3 px-4 py-5 sm:px-6 lg:px-8">
                <div class="min-w-0">
                    @if (! empty($crumbs))
                        <nav aria-label="Breadcrumb">
                            <ol class="flex flex-wrap items-center gap-1 text-sm text-muted">
                                @foreach ($crumbs as $c)
                                    <li class="flex items-center gap-1">
                                        @if (! empty($c['href']))<a href="{{ $c['href'] }}" class="inline-block py-1 underline-offset-4 hover:text-navy-900 hover:underline">{{ $c['label'] }}</a><x-lucide name="chevron-right" class="size-4" />@else<span aria-current="page" class="py-1">{{ $c['label'] }}</span>@endif
                                    </li>
                                @endforeach
                            </ol>
                        </nav>
                    @endif
                    <h1 class="break-words font-display text-2xl font-semibold text-navy-900 sm:text-3xl">{{ $heading ?? $title }}</h1>
                    @if (! empty($subtitle))
                        <p class="mt-1 text-muted">{{ $subtitle }}</p>
                    @endif
                </div>
                @hasSection('actions')
                    <div data-live="actions" class="flex flex-wrap items-center gap-3">@yield('actions')</div>
                @endif
            </div>
        </div>

        <main id="main" tabindex="-1" class="mx-auto max-w-7xl px-4 py-6 @hasSection('mobilebar') pb-24 lg:pb-6 @endif text-[0.9375rem] focus:outline-none sm:px-6 lg:px-8">
            @yield('content')
        </main>
    </div>
    @hasSection('mobilebar')
        <nav aria-label="Jump to a section" class="on-dark fixed inset-x-0 bottom-0 z-20 border-t border-white/15 bg-navy-900 pb-[env(safe-area-inset-bottom)] lg:hidden">
            <ul class="mx-auto grid max-w-md grid-cols-3">@yield('mobilebar')</ul>
        </nav>
    @endif
</body>
</html>
