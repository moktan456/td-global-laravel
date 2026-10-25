{{-- Shown in the Update card for a track that is switched off: say so calmly and offer to switch it on. --}}
@php
    $label = \App\Support\Statuses::trackLabel($track);
    $needs = $track === 'transport' ? 'track_transport' : 'track_customs';
@endphp
<div class="rounded-control border border-dashed border-line bg-sand/40 p-5">
    <p class="font-semibold text-navy-900">{{ $label }} is not tracked for this shipment.</p>
    <p class="mt-1 text-muted">{{ $track === 'transport' ? 'Some customers do not need transport updates. Switch it on if this one does.' : 'Switch it on to follow the customs clearance stages.' }}</p>
    <form method="post" action="/staff/shipments/{{ $s->tracking_number }}/tracks" class="mt-4">
        @csrf
        <input type="hidden" name="track_customs" value="1">
        <input type="hidden" name="track_transport" value="1">
        <x-button type="submit" variant="outline">Switch on {{ strtolower($label) }} tracking</x-button>
    </form>
</div>
