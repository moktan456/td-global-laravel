@extends('layouts.staff', ['title' => 'Clients'])

@section('content')

        {{-- Registering is occasional, the list is daily: the form folds away (and opens by itself after a submit, to show the result). --}}
        <details id="register-client" @if ($state->status !== 'idle') open @endif class="rounded-card border border-line bg-white p-5">
            <summary class="min-h-11 cursor-pointer py-2 font-display text-xl font-semibold text-navy-900">Register a new client</summary>
            <p class="mt-2 text-muted">For clients who call in. Their tracking numbers are sent to this email.</p>
            <div class="mt-5 max-w-3xl">
                @include('staff.client-form', ['state' => $state])
            </div>
        </details>

        <section aria-labelledby="list-heading" class="mt-10">
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

            @if ($clients->count())
                {{-- One quiet row per client; the Telegram tools live in a menu on the row. (No overflow clipping here: the menu hangs below its row.) --}}
                <div class="mt-4 rounded-card border border-line bg-white">
                    <div aria-hidden="true" class="hidden gap-x-6 border-b border-line bg-sand px-5 py-3 text-sm font-semibold text-navy-900 md:grid {{ $telegram ? 'md:grid-cols-[minmax(0,1.2fr)_minmax(0,1.4fr)_9rem_8.5rem]' : 'md:grid-cols-[minmax(0,1.2fr)_minmax(0,1.4fr)]' }} rounded-t-card">
                        <span>Client</span><span>Contact</span>@if ($telegram)<span>Telegram</span><span></span>@endif
                    </div>
                    <ul class="divide-y divide-line">
                        @foreach ($clients as $c)
                            <li class="grid items-center gap-x-6 gap-y-3 px-5 py-4 {{ $telegram ? 'md:grid-cols-[minmax(0,1.2fr)_minmax(0,1.4fr)_9rem_8.5rem]' : 'md:grid-cols-[minmax(0,1.2fr)_minmax(0,1.4fr)]' }}">
                                <div class="min-w-0">
                                    <p class="font-semibold text-navy-900"><a href="/staff/clients/{{ $c->id }}" class="break-words text-brand underline underline-offset-2">{{ $c->company ?: $c->full_name }}</a></p>
                                    @if ($c->company)
                                        <p class="text-muted">{{ $c->full_name }}</p>
                                    @endif
                                </div>
                                <div class="min-w-0 text-muted">
                                    <p class="break-all">{{ $c->email }}</p>
                                    <p>{{ $c->phone }}</p>
                                </div>
                                @if ($telegram)
                                    <div>
                                        @if ($c->telegram_chat_id && $c->notify_telegram)
                                            <span class="inline-flex items-center rounded-full bg-success/15 px-3 py-1 text-sm font-semibold text-success">Connected</span>
                                        @elseif ($c->telegram_chat_id)
                                            <span class="inline-flex items-center rounded-full bg-gold/35 px-3 py-1 text-sm font-semibold text-navy-900">Connected, off</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-sand px-3 py-1 text-sm font-semibold text-navy-900">Not connected</span>
                                        @endif
                                        @unless ($c->notify_email)
                                            <p class="mt-1 text-sm text-muted">Email off</p>
                                        @endunless
                                    </div>
                                    <details data-menu class="relative md:justify-self-end">
                                        <summary class="inline-flex min-h-11 cursor-pointer list-none items-center gap-2 rounded-control border border-navy-900 px-4 font-semibold text-navy-900 hover:bg-sand [&::-webkit-details-marker]:hidden">
                                            Telegram<x-lucide name="chevron-down" class="size-4" /><span class="sr-only"> options for {{ $c->company ?: $c->full_name }}</span>
                                        </summary>
                                        <div class="absolute right-0 z-20 mt-2 w-[min(26rem,calc(100vw-2rem))] rounded-card border border-line bg-white p-5 shadow-lg max-md:left-0 max-md:right-auto">
                                            @if ($c->telegram_chat_id)
                                                <p class="font-semibold text-navy-900">{{ $c->full_name }} is connected</p>
                                                @include('staff.telegram-bot-link', ['client' => $c])
                                                <div class="mt-4 border-t border-line pt-1">
                                                    @include('staff.telegram-link-form', ['client' => $c, 'state' => form_state('tglink'.$c->id)])
                                                </div>
                                            @else
                                                <p class="font-semibold text-navy-900">Connect {{ $c->full_name }} on Telegram</p>
                                                <p class="mt-1 text-sm text-muted">Make a one-time link. The client opens it and presses Start.</p>
                                                @include('staff.telegram-link-form', ['client' => $c, 'state' => form_state('tglink'.$c->id)])
                                            @endif
                                        </div>
                                    </details>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <x-pager :paginator="$clients" label="clients" />
        </section>
@endsection
