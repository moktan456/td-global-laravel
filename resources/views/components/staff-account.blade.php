@props(['variant' => 'sidebar'])
{{-- The signed-in person, as one quiet row. Their account links (change password, view website, sign out) stay folded away
     until the row is opened: on the sidebar as a small menu that opens upward (closes on Escape or a click elsewhere, see
     resources/js/menus.js); inside the phone menu as a plain fold-out. --}}
@php
    $me = auth()->user();
    $initials = collect(preg_split('/\s+/', trim((string) $me?->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';
    $role = $me?->role === 'admin' ? 'Super admin' : 'Staff';
    $item = 'flex min-h-11 w-full items-center gap-3 rounded-control px-3 text-left font-semibold transition-colors duration-150';
    $sidebar = $variant === 'sidebar';
    $itemTone = $sidebar ? 'text-navy-900 hover:bg-sand' : 'text-on-dark-muted hover:bg-white/8 hover:text-white';
@endphp
<details @if ($sidebar) data-menu @endif class="group relative">
    <summary class="flex min-h-14 cursor-pointer list-none items-center gap-3 rounded-control px-3 py-2 text-left transition-colors duration-150 hover:bg-white/8 [&::-webkit-details-marker]:hidden">
        <span aria-hidden="true" class="inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-gold text-sm font-bold text-on-gold">{{ $initials }}</span>
        <span class="min-w-0 flex-1">
            <span class="block truncate font-semibold">{{ $me?->name }}</span>
            <span class="block text-sm text-on-dark-muted">{{ $role }}</span>
        </span>
        <x-lucide name="chevron-down" class="size-4 shrink-0 text-on-dark-muted transition-transform duration-150 group-open:rotate-180" />
        <span class="sr-only">Account menu</span>
    </summary>
    <div class="{{ $sidebar ? 'absolute inset-x-0 bottom-full z-30 mb-2 rounded-card bg-surface p-2 shadow-lg ring-1 ring-black/10' : 'mt-1 grid gap-1 px-1 pb-1' }}">
        @if ($me?->isAdmin())
            <a href="/admin" class="{{ $item }} {{ $itemTone }}"><x-lucide name="square-pen" class="size-5 shrink-0" />Edit website content</a>
        @endif
        <a href="/staff/password" class="{{ $item }} {{ $itemTone }}"><x-lucide name="key-round" class="size-5 shrink-0" />Change password</a>
        <button type="button" data-theme-toggle class="{{ $item }} {{ $itemTone }}"><x-lucide name="moon" class="size-5 shrink-0" /><span data-theme-label>Dark mode</span></button>
        <a href="/" target="_blank" rel="noopener" class="{{ $item }} {{ $itemTone }}"><x-lucide name="external-link" class="size-5 shrink-0" />View website<span class="sr-only"> (opens in a new tab)</span></a>
        <form method="post" action="/staff/logout" class="{{ $sidebar ? 'mt-1 border-t border-line pt-1' : 'mt-1 border-t border-white/15 pt-1' }}">
            @csrf
            <button type="submit" class="{{ $item }} {{ $itemTone }}"><x-lucide name="log-out" class="size-5 shrink-0" />Sign out</button>
        </form>
    </div>
</details>
