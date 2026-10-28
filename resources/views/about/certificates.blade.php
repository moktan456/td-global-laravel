{{-- "Registered in Gelephu Mindfulness City": the company's certificates. --}}
@php
    $legal = site('legal');
    $issued = \Carbon\Carbon::parse($legal['incorporated'], 'UTC')->format('j F Y');
    $upper = strtoupper($legal['name']);
    $certificates = [
        [
            'id' => 'incorporation',
            'title' => 'Certificate of Incorporation',
            'summary' => "Private company limited by shares, incorporated on {$issued} under the laws of {$legal['jurisdiction']}.",
            'image' => '/brand/certificate-of-incorporation.jpg', 'w' => 1264, 'h' => 868,
            'alt' => "Certificate of Incorporation presented to {$upper}, UEN {$legal['uen']}. The company is a private company limited by shares incorporated on and from {$issued} under the laws of Gelephu Mindfulness City. Issued by the Gelephu Corporate Registration Office, Gelephu Mindfulness City Authority, dated {$issued}.",
        ],
        [
            'id' => 'business-name',
            'title' => 'Certificate Confirming Registration of Business Name',
            'summary' => "Business name registered on {$issued}.",
            'image' => '/brand/certificate-of-business-name-registration.jpg', 'w' => 1264, 'h' => 861,
            'alt' => "Certificate Confirming Registration of Business Name presented to {$upper}, UEN {$legal['uen']}. The business name was registered on and from {$issued}. Issued by the Gelephu Corporate Registration Office, Gelephu Mindfulness City Authority, dated {$issued}.",
        ],
    ];
@endphp
<section aria-labelledby="certificates-heading" class="bg-sand py-20 lg:py-28">
    <x-container>
        <x-section-heading id="certificates-heading" :eyebrow="page_text('certificates.eyebrow')" :title="page_text('certificates.title')" class="max-w-none!">
            <x-slot:intro><p>{{ page_text('certificates.intro') }}</p></x-slot:intro>
        </x-section-heading>
        <ul data-reveal-group class="mt-12 grid gap-8 lg:grid-cols-2">
            @foreach ($certificates as $cert)
                @php($titleId = "cert-{$cert['id']}-title")
                <li class="group flex flex-col overflow-hidden rounded-card border border-line bg-white {{ ui('cardLift') }}">
                    <button type="button" data-open-dialog class="relative block overflow-hidden bg-sand text-left">
                        <img src="{{ $cert['image'] }}" alt="" width="{{ $cert['w'] }}" height="{{ $cert['h'] }}" sizes="(min-width: 1024px) 40vw, 100vw" loading="lazy" decoding="async" class="h-auto w-full transition-transform duration-700 ease-out-quart group-hover:scale-[1.03] group-focus-within:scale-[1.03]">
                        <span class="absolute right-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-navy-900/85 px-3 py-1.5 text-sm font-semibold text-white">
                            <x-lucide name="maximize-2" class="size-4" />
                            View full size<span class="sr-only"> of {{ $cert['title'] }}</span>
                        </span>
                    </button>
                    <div class="flex flex-1 flex-col p-6">
                        <h3 id="{{ $titleId }}" class="font-display text-xl font-semibold text-navy-900">{{ $cert['title'] }}</h3>
                        <p class="mt-2 text-muted">{{ $cert['summary'] }}</p>
                        <dl class="mt-4 grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-sm">
                            <dt class="text-muted">Company</dt>
                            <dd class="font-semibold text-ink">{{ $legal['name'] }}</dd>
                            <dt class="text-muted">UEN</dt>
                            <dd class="font-mono font-semibold text-ink">{{ $legal['uen'] }}</dd>
                            <dt class="text-muted">Issued by</dt>
                            <dd class="text-ink">{{ $legal['registry'] }}</dd>
                        </dl>
                    </div>

                    <dialog aria-labelledby="{{ $titleId }}-dialog" class="m-auto w-[min(64rem,calc(100vw-2rem))] max-h-[calc(100dvh-2rem)] overflow-auto rounded-card bg-white p-0 shadow-lift backdrop:bg-navy-950/75">
                        <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-3">
                            <h2 id="{{ $titleId }}-dialog" class="font-display text-lg font-semibold text-navy-900">{{ $cert['title'] }}</h2>
                            <form method="dialog">
                                <button type="submit" class="inline-flex size-11 items-center justify-center rounded-control text-navy-900 transition-colors duration-(--duration-hover) hover:bg-sand">
                                    <x-lucide name="x" class="size-5" />
                                    <span class="sr-only">Close</span>
                                </button>
                            </form>
                        </div>
                        <img src="{{ $cert['image'] }}" alt="{{ $cert['alt'] }}" width="{{ $cert['w'] }}" height="{{ $cert['h'] }}" sizes="(min-width: 1024px) 64rem, 100vw" loading="lazy" decoding="async" class="h-auto w-full">
                    </dialog>
                </li>
            @endforeach
        </ul>
        <p class="mt-8 flex items-center gap-2 text-muted">
            <x-lucide name="badge-check" class="size-5 shrink-0 text-green-dark" />
            {{ page_text('certificates.footnote') }}
        </p>
    </x-container>
</section>
