@extends('layouts.app', [
    'title' => 'Get a Clearing Quote in Gelephu, GMC',
    'description' => 'Tell us about your import or export and get a free customs clearing quote from a clearing agent in Gelephu, GMC.',
    'canonical' => '/quote',
])

@section('content')
    <x-page-header title="Request a free quote" path="/quote" :crumbs="[['label' => 'Request a quote']]">
        <x-slot:intro><p>Tell us about your shipment. A broker will reply with a quote by email.</p></x-slot:intro>
    </x-page-header>
    <x-container class="grid gap-12 py-16 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] lg:items-start lg:gap-16 lg:py-24">
        <div>
            @include('forms.quote', ['plan' => $selected])
        </div>

        {{-- Beside the form: what happens after sending it, and another way to reach us. --}}
        <aside aria-label="About your quote" class="grid gap-6 lg:sticky lg:top-28">
            <section aria-labelledby="quote-next-heading" class="on-dark rounded-card bg-navy-900 p-6 text-white sm:p-8">
                <h2 id="quote-next-heading" class="font-display text-2xl font-semibold">What happens next</h2>
                <ol class="mt-5 grid gap-5">
                    @foreach ([
                        ['Send your details', 'Tell us what you are shipping and how. It takes about two minutes.'],
                        ['We reply with a quote', 'A broker reads your request and emails you a clear quote.'],
                        ['We clear it and keep you posted', 'Once you agree, you get a tracking number and an update at each stage, by email and Telegram.'],
                    ] as $i => [$title, $body])
                        <li class="flex gap-4">
                            <span aria-hidden="true" class="inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-gold font-display text-lg font-semibold text-navy-900">{{ $i + 1 }}</span>
                            <div>
                                <h3 class="font-semibold"><span class="sr-only">Step {{ $i + 1 }}: </span>{{ $title }}</h3>
                                <p class="mt-1 text-on-dark-muted">{{ $body }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>

            <section aria-labelledby="quote-talk-heading" class="rounded-card border border-line bg-white p-6 sm:p-8">
                <h2 id="quote-talk-heading" class="font-display text-xl font-semibold text-navy-900">Prefer to talk?</h2>
                <p class="mt-2 text-muted">Call or message us and we will take the details over the phone.</p>
                <ul class="mt-4 grid gap-1">
                    @if (is_filled(site('contact.phone')) && site('contact.phoneHref'))
                        <li><a href="tel:{{ site('contact.phoneHref') }}" class="inline-flex min-h-11 items-center gap-3 font-semibold text-brand underline-offset-4 hover:underline"><x-lucide name="phone" class="size-5" />{{ site('contact.phone') }}</a></li>
                    @endif
                    <li><a href="mailto:{{ site('contact.email') }}" class="inline-flex min-h-11 items-center gap-3 break-all font-semibold text-brand underline-offset-4 hover:underline"><x-lucide name="mail" class="size-5 shrink-0" />{{ site('contact.email') }}</a></li>
                </ul>
                <p class="mt-4 border-t border-line pt-4 text-muted">Already have a tracking number? <a href="/track" class="font-semibold text-brand underline underline-offset-4">Track your shipment</a>.</p>
            </section>
        </aside>
    </x-container>
@endsection
