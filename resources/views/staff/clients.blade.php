@extends('layouts.app', ['title' => 'Clients', 'robots' => 'noindex, nofollow'])

@section('content')
    <x-page-header title="Clients" :crumbs="[['label' => 'Staff', 'href' => '/staff'], ['label' => 'Clients']]" />
    <x-container class="py-10">
        <x-staff-nav current="/staff/clients" :name="auth()->user()->name" :role="auth()->user()->role" />
        <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_1.2fr]">
            <section aria-labelledby="register-heading" class="self-start rounded-card border border-line bg-white p-6">
                <h2 id="register-heading" class="font-display text-2xl font-semibold text-navy-900">Register a client</h2>
                <p class="mt-2 text-muted">For clients who call in. Their tracking numbers are sent to this email.</p>
                <div class="mt-6">
                    @include('staff.client-form', ['state' => $state])
                </div>
            </section>
            <section aria-labelledby="list-heading">
                <h2 id="list-heading" class="font-display text-2xl font-semibold text-navy-900">Clients ({{ $total }})</h2>
                <form method="get" action="/staff/clients" role="search" aria-label="Search clients" class="mt-4 flex flex-wrap items-end gap-3">
                    <div class="min-w-56 flex-1 basis-56">
                        <label for="client-q" class="block font-semibold text-navy-900">Search</label>
                        <input id="client-q" type="search" name="q" value="{{ $q }}" placeholder="Name, company, email or phone" autocomplete="off" class="mt-2 min-h-11 w-full rounded-control border border-control bg-white px-3">
                    </div>
                    @if ($telegram)
                        <div>
                            <label for="client-telegram" class="block font-semibold text-navy-900">Telegram</label>
                            <select id="client-telegram" name="telegram" class="mt-2 min-h-11 rounded-control border border-control bg-white px-3">
                                <option value="">All clients</option>
                                <option value="connected" @selected($telegramFilter === 'connected')>Connected</option>
                                <option value="not" @selected($telegramFilter === 'not')>Not connected</option>
                            </select>
                        </div>
                    @endif
                    <button type="submit" class="min-h-11 rounded-control bg-navy-900 px-5 font-semibold text-white hover:bg-navy-800">Search</button>
                    @if ($q !== '' || $telegramFilter !== '')
                        <a href="/staff/clients" class="inline-flex min-h-11 items-center px-2 font-semibold text-brand underline underline-offset-2">Clear</a>
                    @endif
                </form>
                <p role="status" class="mt-4 text-muted">
                    @if ($clients->total() === 0)
                        No clients match{{ $q !== '' ? ' “'.$q.'”' : '' }}.
                    @elseif ($q !== '' || $telegramFilter !== '')
                        {{ $clients->total() }} {{ $clients->total() === 1 ? 'client matches' : 'clients match' }}{{ $q !== '' ? ' “'.$q.'”' : '' }}. Showing {{ $clients->firstItem() }}–{{ $clients->lastItem() }}.
                    @else
                        Showing {{ $clients->firstItem() }}–{{ $clients->lastItem() }} of {{ $clients->total() }}, newest first.
                    @endif
                </p>
                <ul class="mt-4 grid gap-3">
                    @foreach ($clients as $c)
                        <li class="rounded-tile border border-line bg-white p-4">
                            <p class="font-semibold text-navy-900"><a href="/staff/clients/{{ $c->id }}" class="text-brand underline underline-offset-2">{{ $c->full_name }}</a>{{ $c->company ? ' · '.$c->company : '' }}</p>
                            <p class="text-muted">{{ $c->email }} · {{ $c->phone }}</p>
                            <p class="mt-1 text-sm text-muted">Registered by {{ $c->registered_by === 'staff' ? 'staff' : 'the client' }}</p>
                            @if ($telegram)
                                <p class="mt-1 text-sm text-muted">
                                    Telegram: {{ $c->telegram_chat_id ? ($c->notify_telegram ? 'connected' : 'connected, switched off by the client') : 'not connected' }}{{ $c->notify_email ? '' : ' · Email switched off by the client' }}
                                </p>
                                @if ($c->telegram_chat_id)
                                    @include('staff.telegram-bot-link', ['client' => $c])
                                @endif
                                @include('staff.telegram-link-form', ['client' => $c, 'state' => form_state('tglink'.$c->id)])
                            @endif
                        </li>
                    @endforeach
                </ul>
                <x-pager :paginator="$clients" label="clients" />
            </section>
        </div>
    </x-container>
@endsection
