@php
    $linkBase = 'relative inline-flex min-h-11 items-center whitespace-nowrap px-1 text-base font-semibold text-navy-900 transition-colors duration-(--duration-hover) hover:text-brand '
        .'after:absolute after:inset-x-1 after:bottom-1.5 after:h-0.5 after:origin-left after:scale-x-0 after:bg-brand '
        .'after:transition-transform after:duration-(--duration-enter) after:ease-out-quart hover:after:scale-x-100 focus-visible:after:scale-x-100';
    $activeMark = 'after:scale-x-100';
    // Closed state of the popovers (the script swaps in the open state): fade + short drop, exit faster than enter.
    $popoverClosed = 'invisible opacity-0 -translate-y-1.5 duration-(--duration-exit) ease-in-quad pointer-events-none';
    $primaryNav = content('site.primaryNav');
    $companyNav = content('site.companyNav');
    $companyActive = collect($companyNav)->contains(fn ($i) => nav_active($i['href']));
@endphp
<header data-site-header class="sticky top-0 z-40 border-b border-line bg-ivory/95 backdrop-blur transition-shadow duration-(--duration-enter) supports-[backdrop-filter]:bg-ivory/85">
    <div class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:h-24 lg:px-16 xl:gap-6">
        <x-logo />

        <nav aria-label="Main" class="hidden lg:block">
            <ul class="flex items-center gap-4 xl:gap-7">
                @foreach ($primaryNav as $item)
                    <li>
                        <a href="{{ $item['href'] }}" @if (nav_active($item['href'])) aria-current="page" @endif class="{{ $linkBase }} {{ nav_active($item['href']) ? $activeMark : '' }}">{{ $item['label'] }}</a>
                    </li>
                @endforeach
                <li>
                    {{-- "Company" disclosure menu (APG disclosure navigation pattern). --}}
                    <div data-company-menu class="relative">
                        <button type="button" data-company-toggle aria-expanded="false" aria-controls="company-menu" class="{{ $linkBase }} gap-1 {{ $companyActive ? $activeMark : '' }}">
                            {{ content('site.companyMenuLabel') }}
                            <x-lucide name="chevron-down" data-chevron class="size-4 transition-transform duration-(--duration-enter) ease-out-quart" />
                        </button>
                        <div data-company-panel class="absolute left-1/2 top-full z-50 mt-2 w-48 -translate-x-1/2 transition-[opacity,translate,visibility] {{ $popoverClosed }}">
                            <ul id="company-menu" inert class="overflow-hidden rounded-tile border border-line bg-white p-1.5 shadow-lift">
                                @foreach ($companyNav as $item)
                                    @php($active = nav_active($item['href']))
                                    <li>
                                        <a href="{{ $item['href'] }}" @if ($active) aria-current="page" @endif class="block rounded-control px-4 py-2.5 text-base transition-[background-color,color,padding] duration-(--duration-hover) ease-out-quart hover:bg-sand hover:pl-5 hover:text-brand focus-visible:bg-sand {{ $active ? 'font-semibold text-brand' : 'text-navy-900' }}">{{ $item['label'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </li>
            </ul>
        </nav>

        <div class="hidden items-center gap-3 lg:flex">
            {{-- Header search that stays a single icon until opened. The field is inert while collapsed. --}}
            <div data-collapsible-search class="flex items-center">
                <div id="header-search-panel" data-search-panel inert class="overflow-hidden transition-[width,opacity] duration-200 ease-out-quart w-0 opacity-0">
                    <form role="search" aria-label="Site search" action="/search" class="relative w-60 p-1 pr-2">
                        <label for="header-search-input" class="sr-only">{{ label('a11y.searchLabel') }}</label>
                        <input id="header-search-input" data-search-input type="search" name="q" placeholder="{{ label('a11y.searchPlaceholder') }}" class="h-11 w-full rounded-control border border-control bg-white pl-3 pr-11 text-base text-ink placeholder:text-muted">
                        <button type="submit" class="absolute inset-y-1 right-2 inline-flex w-11 items-center justify-center text-navy-900 hover:text-brand">
                            <x-lucide name="search" class="size-5" />
                            <span class="sr-only">{{ label('a11y.searchButton') }}</span>
                        </button>
                    </form>
                </div>
                <button type="button" data-search-toggle aria-expanded="false" aria-controls="header-search-panel" class="inline-flex size-11 items-center justify-center rounded-control text-navy-900 transition-colors duration-200 hover:bg-sand">
                    <span data-icon-open><x-lucide name="search" class="size-5" /></span>
                    <span data-icon-close hidden><x-lucide name="x" class="size-5" /></span>
                    <span class="sr-only">{{ label('a11y.searchButton') }}</span>
                </button>
            </div>
            <x-button-link href="/track">Track shipment</x-button-link>
        </div>

        <button type="button" data-mobile-toggle aria-expanded="false" aria-controls="mobile-menu" class="inline-flex size-11 items-center justify-center rounded-control text-navy-900 transition-colors duration-(--duration-hover) hover:bg-sand lg:hidden">
            <span data-icon-open><x-lucide name="menu" class="size-6" /></span>
            <span data-icon-close hidden><x-lucide name="x" class="size-6" /></span>
            <span class="sr-only">{{ label('a11y.menu') }}</span>
        </button>
    </div>

    <div id="mobile-menu" data-mobile-panel inert class="absolute inset-x-0 top-full max-h-[calc(100dvh-5rem)] overflow-y-auto border-y border-line bg-ivory shadow-lift transition-[opacity,translate,visibility] lg:hidden {{ $popoverClosed }}">
        <nav aria-label="Main menu" class="mx-auto max-w-7xl px-4 py-4 sm:px-6">
            <form role="search" aria-label="Menu search" action="/search" class="relative mb-4">
                <label for="mobile-search" class="sr-only">{{ label('a11y.searchLabel') }}</label>
                <input id="mobile-search" type="search" name="q" placeholder="{{ label('a11y.searchPlaceholder') }}" class="h-11 w-full rounded-control border border-control bg-white pl-3 pr-11 text-base text-ink placeholder:text-muted">
                <button type="submit" class="absolute inset-y-0 right-0 inline-flex w-11 items-center justify-center text-navy-900 hover:text-brand">
                    <x-lucide name="search" class="size-5" />
                    <span class="sr-only">{{ label('a11y.searchButton') }}</span>
                </button>
            </form>
            <ul class="grid gap-1">
                @foreach (array_merge($primaryNav, $companyNav) as $item)
                    @php($active = nav_active($item['href']))
                    <li>
                        <a href="{{ $item['href'] }}" @if ($active) aria-current="page" @endif class="flex min-h-12 items-center rounded-control px-3 text-lg transition-colors duration-(--duration-hover) hover:bg-sand {{ $active ? 'bg-sand font-semibold text-brand' : 'text-navy-900' }}">{{ $item['label'] }}</a>
                    </li>
                @endforeach
            </ul>
            <x-button-link href="/track" class="mt-4 w-full">Track shipment</x-button-link>
        </nav>
    </div>
</header>
