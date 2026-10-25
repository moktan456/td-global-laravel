{{-- Transport details on a shipment (carrier, vehicle, pickup, destination, expected date; driver is staff only). --}}
@php
    $state = $state ?? form_state('tdetails');
    $v = fn (string $f) => $state->status === 'error' ? ($state->value($f) ?? '') : ($f === 'expected_delivery' ? ($s->expected_delivery?->format('Y-m-d') ?? '') : ($s->{$f} ?? ''));
    $err = fn (string $f) => $state->error($f);
@endphp
<form id="tdetails-form" method="post" action="/staff/shipments/{{ $s->tracking_number }}/transport-details" data-enhance novalidate class="grid gap-5">
    @csrf
    <x-form-status :state="$state" :ids="['carrier' => 'td-carrier']" />
    @include('staff.transport-fields', ['prefix' => 'td', 'v' => $v, 'err' => $err])
    <div><x-submit-button>Save details</x-submit-button></div>
</form>
