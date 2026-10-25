@php
    $state = $state ?? form_state('status');
    $ids = ['status' => 'status-stage', 'note' => 'status-note'];
@endphp
<form id="status-form" method="post" action="/staff/shipments/{{ $s->tracking_number }}/status" data-enhance data-refresh novalidate class="grid gap-5">
    @csrf
    <x-form-status :state="$state" :ids="$ids" />
    <x-select-field :id="$ids['status']" name="status" label="Stage" :value="$state->value('status', $s->status)" :error="$state->error('status')" :options="array_map(fn ($x) => ['value' => $x['key'], 'label' => $x['label']], \App\Support\Statuses::all())" />
    <x-text-area :id="$ids['note']" name="note" label="Note for the client" :optional="true" :rows="3" hint="Anyone with the tracking number can read this note on the Track page, and it goes in the email. Don't put personal or financial details in it." />
    <label class="flex min-h-11 items-center gap-3">
        <input type="checkbox" name="notify" checked class="size-5 accent-link">
        Notify the client about this update (email, and Telegram if connected)
    </label>
    <div>
        <x-submit-button>Save update</x-submit-button>
    </div>
</form>
