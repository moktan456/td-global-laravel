{{-- Tracking lookup against the live system: the form and, below it, the result. The whole wrapper is swapped in place after a lookup. --}}
@php
    $state = $state ?? form_state('track');
    $ids = ['tracking' => 'track-tracking'];
    $result = $state->result;
@endphp
<div id="track-form" data-enhance-root class="grid gap-10">
    <form method="post" action="/track" data-enhance novalidate class="grid gap-5">
        @csrf
        @if ($state->status === 'error')
            <x-form-status :state="$state" :ids="$ids" />
        @endif
        <x-text-field :id="$ids['tracking']" name="tracking" label="Tracking number" hint="From your confirmation email or message, for example TD-2610-K7QX4M." autocomplete="off" :value="$state->value('tracking')" :error="$state->error('tracking')" />
        <div>
            <x-submit-button>Track shipment</x-submit-button>
        </div>
    </form>

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
    @endif
</div>
