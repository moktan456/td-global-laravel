{{-- Staff: send the tracking number to the client again (by Telegram and email, following their settings). --}}
@php($state = $state ?? form_state('resend'))
<form id="resend-form" method="post" action="/staff/shipments/{{ $s->tracking_number }}/resend" data-enhance novalidate class="grid gap-3">
    @csrf
    <x-form-status :state="$state" :ids="[]" />
    <div>
        <x-submit-button>Send tracking number again</x-submit-button>
        <p class="mt-2 text-sm text-muted">Goes to {{ $s->client?->full_name }} on Telegram (if connected) and by email, following their settings.</p>
    </div>
</form>
