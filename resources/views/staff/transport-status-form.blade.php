{{-- Update the transport stage. Choosing Delivered shows the proof-of-delivery fields (CSS :has, no script). --}}
@php
    $state = $state ?? form_state('transport');
    $ids = ['status' => 'tstatus-stage', 'note' => 'tstatus-note', 'delivered_to' => 'tstatus-receiver', 'delivered_at' => 'tstatus-when'];
    $current = $state->value('status', (string) $s->transport_status);
    $failed = $state->status === 'error';
    $receiver = $failed ? ($state->value('delivered_to') ?? '') : (string) $s->delivered_to;
    $whenDefault = $s->delivered_at ?? now();
    $when = $failed ? ($state->value('delivered_at') ?? '') : $whenDefault->copy()->timezone('Asia/Thimphu')->format('Y-m-d\TH:i');
@endphp
<form id="transport-form" method="post" action="/staff/shipments/{{ $s->tracking_number }}/transport-status" data-enhance data-refresh novalidate class="group/pod grid gap-5">
    @csrf
    <x-form-status :state="$state" :ids="$ids" />
    <x-select-field :id="$ids['status']" name="status" label="Transport stage" :value="$current" :error="$state->error('status')" :options="array_map(fn ($x) => ['value' => $x['key'], 'label' => $x['label']], \App\Support\Statuses::transport())" />
    <fieldset class="hidden gap-5 rounded-control border border-line bg-sand/50 p-4 group-has-[option[value=delivered]:checked]/pod:grid">
        <legend class="px-1 font-semibold text-navy-900">Proof of delivery</legend>
        <x-text-field :id="$ids['delivered_to']" name="delivered_to" label="Received by" :optional="true" hint="The name of the person who took the delivery." :value="$receiver" :error="$state->error('delivered_to')" autocomplete="off" />
        <x-text-field :id="$ids['delivered_at']" name="delivered_at" type="datetime-local" label="Delivered on" :optional="true" hint="Bhutan time. Today and now if you leave it." :value="$when" :error="$state->error('delivered_at')" />
    </fieldset>
    <x-text-area :id="$ids['note']" name="note" label="Note for the client" :optional="true" :rows="3" hint="Anyone with the tracking number can read this note on the Track page, and it goes in the message. Don't put personal or financial details in it." />
    <label class="flex min-h-11 items-center gap-3">
        <input type="checkbox" name="notify" checked class="size-5 accent-brand">
        Notify the client about this update (email, and Telegram if connected)
    </label>
    <div>
        <x-submit-button>Save update</x-submit-button>
    </div>
</form>
