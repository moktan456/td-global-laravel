{{-- Per-person controls on the Team page: new temporary password, switch off / on. --}}
@php
    $fid = "staffrow{$member->id}";
    $isSelf = $member->id === auth()->id();
    $min = \App\Support\StaffAccounts::MIN_PASSWORD_LENGTH;
    $state = $state ?? \App\Support\FormState::idle();
    $resetState = $state->status !== 'idle' && ($state->errors || str_contains($state->message, 'temporary password')) ? $state : \App\Support\FormState::idle();
    $toggleState = $state->status !== 'idle' && $resetState->status === 'idle' ? $state : \App\Support\FormState::idle();
@endphp
<div id="{{ $fid }}-form" data-enhance-root class="mt-4 grid gap-4">
    <x-form-status :state="$toggleState" :ids="[]" />
    <x-form-status :state="$resetState" :ids="['password' => $fid.'-password']" />
    <details class="rounded-control border border-line p-3">
        <summary class="min-h-11 cursor-pointer py-2 font-semibold text-brand">New temporary password for {{ $member->name }}</summary>
        <form method="post" action="/staff/team/{{ $member->id }}/password" data-enhance novalidate class="mt-3 grid gap-4">
            @csrf
            <x-text-field :id="$fid.'-password'" name="password" label="Temporary password" :hint="'At least '.$min.' characters. They are signed out and must choose their own next time.'" autocomplete="off" :error="$resetState->error('password')" />
            <div>
                <x-submit-button>Set password</x-submit-button>
            </div>
        </form>
    </details>
    @unless ($isSelf)
        <form method="post" action="/staff/team/{{ $member->id }}/active" data-enhance>
            @csrf
            <input type="hidden" name="active" value="{{ $member->active ? 'false' : 'true' }}">
            <x-button type="submit" variant="outline">{{ $member->active ? 'Switch off '.$member->name : 'Switch '.$member->name.' back on' }}</x-button>
        </form>
    @endunless
</div>
