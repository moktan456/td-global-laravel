{{-- Tracking lookup against the live system: the form (left) and, on the right, the result (or the example card until there is one). The wrapper takes no box of its own (it is "contents"), so its two children sit in the page grid; the whole wrapper is swapped in place after a lookup. --}}
@php
    $state = $state ?? form_state('track');
    $ids = ['tracking' => 'track-tracking'];
    $result = $state->result;
@endphp
<div id="track-form" data-enhance-root class="contents">
    <form method="post" action="/track" data-enhance novalidate class="grid max-w-lg content-start gap-5 lg:col-start-1 lg:row-start-1">
        @csrf
        @if ($state->status === 'error')
            <x-form-status :state="$state" :ids="$ids" />
        @endif
        <x-text-field :id="$ids['tracking']" name="tracking" label="Tracking number" hint="From your confirmation email or message, for example TD-2610-K7QX4M." autocomplete="off" :value="$state->value('tracking')" :error="$state->error('tracking')" />
        <div>
            <x-submit-button>Track shipment</x-submit-button>
        </div>
    </form>

    <div class="order-last lg:order-none lg:col-start-2 lg:row-span-2 lg:row-start-1">
        @if ($result)
            <section aria-labelledby="result-heading" role="status" class="rounded-card border border-line bg-white p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 id="result-heading" class="font-mono text-xl font-semibold text-navy-900">{{ $result['tracking_number'] }}</h2>
                    <x-status-badge :status="$result['status']" />
                </div>
                <div class="mt-6">
                    <x-timeline :events="$result['events']" />
                </div>
            </section>
        @else
            @include('track.sample')
        @endif
    </div>
</div>
