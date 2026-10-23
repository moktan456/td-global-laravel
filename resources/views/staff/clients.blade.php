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
                <h2 id="list-heading" class="font-display text-2xl font-semibold text-navy-900">All clients ({{ count($clients) }})</h2>
                <ul class="mt-6 grid gap-3">
                    @foreach ($clients as $c)
                        <li class="rounded-tile border border-line bg-white p-4">
                            <p class="font-semibold text-navy-900">{{ $c->full_name }}{{ $c->company ? ' · '.$c->company : '' }}</p>
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
            </section>
        </div>
    </x-container>
@endsection
