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
            @php
                $tracks = $result['tracks'];
                $two = count($tracks) > 1;
                $delivery = $result['delivery'];
            @endphp
            <section aria-labelledby="result-heading" role="status" class="rounded-card border border-line bg-white p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 id="result-heading" class="font-mono text-xl font-semibold text-navy-900">{{ $result['tracking_number'] }}</h2>
                    @unless ($two)
                        <x-status-badge :status="$result['status']" />
                    @endunless
                </div>

                @if ($two)
                    {{-- Where each part of the journey is, at a glance. --}}
                    <div class="mt-5 grid gap-5 rounded-control bg-sand/60 p-4">
                        @foreach ($tracks as $t)
                            <div>
                                <p class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="font-semibold text-navy-900">{{ $t['label'] }}</span>
                                    <x-status-badge :status="$t['status']" />
                                </p>
                                <div class="mt-2"><x-track-progress :position="$t['position']" :total="$t['total']" /></div>
                                <p class="mt-1 text-sm text-muted">
                                    @if ($t['finished']) Complete @else Step {{ $t['position'] }} of {{ $t['total'] }} @endif
                                    @if ($t['track'] === 'transport' && ! $t['finished'] && ! empty($delivery['Expected delivery'])) · Expected {{ $delivery['Expected delivery'] }} @endif
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif

                @foreach ($tracks as $t)
                    <div class="mt-6">
                        @if ($two)
                            <h3 class="font-display text-lg font-semibold text-navy-900">{{ $t['label'] }}</h3>
                        @endif
                        @if ($t['track'] === 'transport' && $delivery)
                            <dl class="mt-3 grid grid-cols-[auto_1fr] gap-x-6 gap-y-1 rounded-control border border-line p-4 text-[0.9375rem]">
                                @foreach ($delivery as $name => $value)
                                    <dt class="text-muted">{{ $name }}</dt>
                                    <dd class="min-w-0 break-words font-semibold text-navy-900">{{ $value }}</dd>
                                @endforeach
                            </dl>
                        @endif
                        <div class="mt-5">
                            <x-timeline :events="$t['events']" />
                        </div>
                    </div>
                @endforeach
            </section>
        @else
            @include('track.sample')
        @endif
    </div>
</div>
