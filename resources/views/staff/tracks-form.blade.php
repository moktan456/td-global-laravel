{{-- What we track on one shipment: customs clearance, transport, or both. Switching a track off hides it from the client and keeps its history. --}}
@php
    $state = $state ?? form_state('tracks');
    $customs = $state->status === 'error' ? (bool) $state->value('track_customs') : $s->customs_enabled;
    $transport = $state->status === 'error' ? (bool) $state->value('track_transport') : $s->transport_enabled;
@endphp
<form id="tracks-form" method="post" action="/staff/shipments/{{ $s->tracking_number }}/tracks" data-enhance data-refresh novalidate class="grid gap-3">
    @csrf
    <x-form-status :state="$state" :ids="['tracks' => 'tracks-customs']" />
    <x-track-switch id="tracks-customs" name="track_customs" label="Customs clearance" description="The 7 customs stages." :checked="$customs" />
    <x-track-switch id="tracks-transport" name="track_transport" label="Transport" description="Pickup to delivery, in 6 stages." :checked="$transport" />
    <p class="text-sm text-muted">Switching one off hides it from the client. Its history is kept.</p>
    <div><x-submit-button>Save</x-submit-button></div>
</form>
