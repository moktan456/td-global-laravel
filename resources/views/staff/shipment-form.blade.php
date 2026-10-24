{{-- New clearance request, by staff on a client's behalf. --}}
@php
    $state = $state ?? form_state('shipment');
    $ids = ['client_id' => 'shipment-client', 'direction' => 'shipment-dir', 'mode' => 'shipment-mode', 'goods' => 'shipment-goods', 'details' => 'shipment-details', 'assigned_to' => 'shipment-assignee'];
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
    <x-field-shell :id="$ids['assigned_to']" label="Looked after by" hint="Who in the team handles this request. You can change it later.">
        <select id="{{ $ids['assigned_to'] }}" name="assigned_to" aria-describedby="{{ $ids['assigned_to'] }}-hint" class="block min-h-11 w-full rounded-control border border-control bg-white px-3 py-2.5 text-base text-ink">
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
