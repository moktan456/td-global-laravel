@props(['current' => '', 'name', 'role'])
@php
    $links = [['href' => '/staff', 'label' => 'Shipments'], ['href' => '/staff/shipments/new', 'label' => 'New request'], ['href' => '/staff/clients', 'label' => 'Clients']];
    if ($role === 'admin') {
        $links[] = ['href' => '/staff/team', 'label' => 'Team'];
        $links[] = ['href' => '/staff/telegram', 'label' => 'Telegram'];
    }
@endphp
<div class="flex flex-wrap items-center justify-between gap-4 border-b border-line pb-4">
    <nav aria-label="Staff">
        <ul class="flex flex-wrap gap-2">
            @foreach ($links as $l)
                <li>
                    <a href="{{ $l['href'] }}" @if ($current === $l['href']) aria-current="page" @endif class="inline-flex min-h-11 items-center rounded-control px-4 font-semibold transition-colors duration-(--duration-hover) {{ $current === $l['href'] ? 'bg-navy-900 text-white' : 'text-navy-900 hover:bg-sand' }}">{{ $l['label'] }}</a>
                </li>
            @endforeach
        </ul>
    </nav>
    <div class="flex items-center gap-3 text-muted">
        <span>Signed in as {{ $name }}</span>
        <a href="/staff/password" class="inline-flex min-h-11 items-center font-semibold text-brand underline underline-offset-2">Change password</a>
        <form method="post" action="/staff/logout">
            @csrf
            <x-button type="submit" variant="outline">Sign out</x-button>
        </form>
    </div>
</div>
