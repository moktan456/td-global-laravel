{{-- New clearance request, by staff on a client's behalf. --}}
@php
    $state = $state ?? form_state('shipment');
    $ids = ['client_id' => 'shipment-client', 'direction' => 'shipment-dir', 'mode' => 'shipment-mode', 'goods' => 'shipment-goods', 'details' => 'shipment-details', 'assigned_to' => 'shipment-assignee', 'tracks' => 'shipment-track-customs'];
    $failed = $state->status === 'error';
    // The two switches: customs on and transport off to begin with; after a mistake they keep what was chosen.
    $trackCustoms = $failed ? (bool) $state->value('track_customs') : true;
    $trackTransport = $failed ? (bool) $state->value('track_transport') : false;
    $v = fn (string $f) => $state->value($f) ?? '';
    $err = fn (string $f) => $state->error($f);
@endphp
<form id="shipment-form" method="post" action="/staff/shipments" data-enhance novalidate class="grid gap-5">
    @csrf
    <x-form-status :state="$state" :ids="$ids" />
    <x-select-field :id="$ids['client_id']" name="client_id" label="Client" placeholder="Choose a client…" :value="$state->value('client_id', $chosenClient ?? null)" :error="$state->error('client_id')" :options="$clients" />
    <div class="grid gap-5 sm:grid-cols-2">
        <x-select-field :id="$ids['direction']" name="direction" label="Import or export" :value="$state->value('direction')" :error="$state->error('direction')" :options="content('statuses.directions')" />
        <x-select-field :id="$ids['mode']" name="mode" label="Shipping method" :value="$state->value('mode')" :error="$state->error('mode')" :options="content('statuses.modes')" />
    </div>
    <x-text-field :id="$ids['goods']" name="goods" label="What is being shipped?" hint="For example: 2 pallets of auto parts." :value="$state->value('goods')" :error="$state->error('goods')" />
    <fieldset class="grid gap-3">
        <legend class="mb-1 font-semibold text-navy-900">What should we track?</legend>
        <input type="hidden" name="tracks_chosen" value="1">
        <x-track-switch :id="$ids['tracks']" name="track_customs" label="Customs clearance" description="The 7 customs stages, from Declaration Submitted to Released." :checked="$trackCustoms" />
        {{-- Transport and its details sit together: the details show only while the switch is on (CSS :has, no script). --}}
        <div class="group/transport grid gap-3">
            <x-track-switch id="shipment-track-transport" name="track_transport" label="Transport" description="Optional. Pickup to delivery, in 6 stages, with proof of delivery." :checked="$trackTransport" />
            <div class="hidden gap-5 rounded-control border border-line bg-surface p-4 group-has-checked/transport:grid">
                <div>
                    <p class="font-semibold text-navy-900">Transport details</p>
                    <p class="text-sm text-muted">All optional. You can add or change them later.</p>
                </div>
                <div>@include('staff.transport-fields', ['prefix' => 'sf', 'v' => $v, 'err' => $err])</div>
            </div>
        </div>
        @if ($state->error('tracks'))
            <p id="shipment-tracks-error" class="font-semibold text-error">{{ $state->error('tracks') }}</p>
        @endif
    </fieldset>
    <x-field-shell :id="$ids['assigned_to']" label="Looked after by" hint="Who in the team handles this request. You can change it later.">
        <select id="{{ $ids['assigned_to'] }}" name="assigned_to" aria-describedby="{{ $ids['assigned_to'] }}-hint" class="block min-h-11 w-full rounded-control border border-control bg-surface px-3 py-2.5 text-base text-ink">
            <option value="">Nobody yet</option>
            @foreach ($assignees as $a)
                <option value="{{ $a['value'] }}" @selected($state->value('assigned_to', (string) auth()->id()) === $a['value'])>{{ $a['label'] }}{{ $a['value'] === (string) auth()->id() ? ' (you)' : '' }}</option>
            @endforeach
        </select>
    </x-field-shell>
    <x-text-area :id="$ids['details']" name="details" label="Anything else we should know?" :optional="true" :rows="4" :value="$state->value('details')" />
    <div>
        <x-submit-button>Create request and tracking number</x-submit-button>
    </div>
</form>
