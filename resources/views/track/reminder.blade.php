@php
    $state = $state ?? form_state('reminder');
    $ids = ['contact' => 'reminder-contact'];
@endphp
<form id="reminder-form" method="post" action="/track/reminder" data-enhance novalidate class="grid gap-5">
    @csrf
    <x-form-status :state="$state" :ids="$ids" />
    <x-text-field :id="$ids['contact']" name="contact" label="Phone number or email" hint="The one you gave us. We'll send your tracking numbers to the contact details we have on file; they aren't shown here." autocomplete="off" :value="$state->value('contact')" :error="$state->error('contact')" />
    <div>
        <x-submit-button>Send me my tracking number</x-submit-button>
    </div>
</form>
