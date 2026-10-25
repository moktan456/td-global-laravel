{{-- The optional transport details (new request, and the details card on a shipment). $v reads a value ($v('carrier')), $err reads an error. --}}
@php($pre = $prefix ?? 'tf')
<div class="grid gap-5 sm:grid-cols-2">
    <x-text-field :id="$pre.'-carrier'" name="carrier" label="Carrier or transport company" :optional="true" :value="$v('carrier')" :error="$err('carrier')" />
    <x-text-field :id="$pre.'-vehicle'" name="vehicle_number" label="Vehicle number" :optional="true" hint="For example BP-1-A1234." :value="$v('vehicle_number')" :error="$err('vehicle_number')" autocomplete="off" />
    <x-text-field :id="$pre.'-pickup'" name="pickup_place" label="Pickup place" :optional="true" :value="$v('pickup_place')" :error="$err('pickup_place')" />
    <x-text-field :id="$pre.'-destination'" name="destination" label="Destination" :optional="true" :value="$v('destination')" :error="$err('destination')" />
    <x-text-field :id="$pre.'-expected'" name="expected_delivery" type="date" label="Expected delivery date" :optional="true" :value="$v('expected_delivery')" :error="$err('expected_delivery')" />
</div>
<div class="mt-5 rounded-control border border-dashed border-line bg-sand/50 p-4">
    <p class="font-semibold text-navy-900">Driver (staff only)</p>
    <p class="text-sm text-muted">Never shown to the client.</p>
    <div class="mt-4 grid gap-5 sm:grid-cols-2">
        <x-text-field :id="$pre.'-driver'" name="driver_name" label="Driver's name" :optional="true" :value="$v('driver_name')" :error="$err('driver_name')" autocomplete="off" />
        <x-text-field :id="$pre.'-driver-phone'" name="driver_phone" label="Driver's phone" :optional="true" inputmode="tel" :value="$v('driver_phone')" :error="$err('driver_phone')" autocomplete="off" />
    </div>
</div>
