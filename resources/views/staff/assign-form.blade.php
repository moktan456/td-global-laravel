{{-- Staff: who is looking after this shipment. Anyone on staff can change it; "Nobody yet" clears it. --}}
@php
    $state = $state ?? form_state('assign');
    $current = $state->value('assigned_to', (string) ($s->assigned_to ?? ''));
@endphp
<form id="assign-form" method="post" action="/staff/shipments/{{ $s->tracking_number }}/assign" data-enhance data-refresh novalidate class="grid gap-3">
    @csrf
    <x-form-status :state="$state" :ids="['assigned_to' => 'assign-to']" />
    <x-field-shell id="assign-to" label="Looked after by" :error="$state->error('assigned_to')">
        <select id="assign-to" name="assigned_to" @if ($state->error('assigned_to')) aria-invalid="true" aria-describedby="assign-to-error" @endif class="block min-h-11 w-full rounded-control border border-control bg-surface px-3 py-2.5 text-base text-ink aria-[invalid=true]:border-2">
            <option value="">Nobody yet</option>
            @foreach ($assignees as $a)
                <option value="{{ $a['value'] }}" @selected($current === $a['value'])>{{ $a['label'] }}{{ $a['value'] === (string) auth()->id() ? ' (you)' : '' }}</option>
            @endforeach
        </select>
    </x-field-shell>
    <div><x-submit-button>Save</x-submit-button></div>
</form>
