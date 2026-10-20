{{-- Staff registering a client on their behalf (e.g. after a phone call). --}}
@php
    $state = $state ?? form_state('client');
    $ids = ['full_name' => 'client-name', 'company' => 'client-company', 'email' => 'client-email', 'phone' => 'client-phone'];
@endphp
<form id="client-form" method="post" action="/staff/clients" data-enhance novalidate class="grid gap-5">
    @csrf
    <x-form-status :state="$state" :ids="$ids" />
    <div class="grid gap-5 sm:grid-cols-2">
        <x-text-field :id="$ids['full_name']" name="full_name" label="Client's name" placeholder="e.g. Karma Wangchuk" autocomplete="off" :value="$state->value('full_name')" :error="$state->error('full_name')" />
        <x-text-field :id="$ids['company']" name="company" label="Company" :optional="true" placeholder="e.g. Wangchuk Traders" autocomplete="off" :value="$state->value('company')" />
        <x-text-field :id="$ids['email']" name="email" type="email" label="Email" placeholder="e.g. karma@example.com" autocomplete="off" :value="$state->value('email')" :error="$state->error('email')" />
        <x-text-field :id="$ids['phone']" name="phone" type="tel" label="Phone" hint="For example 17975757" autocomplete="off" :value="$state->value('phone')" :error="$state->error('phone')" />
    </div>
    <label class="flex min-h-11 items-center gap-3">
        <input type="checkbox" name="notify" checked class="size-5 accent-brand">
        Email the client a welcome message
    </label>
    <div>
        <x-submit-button>Register client</x-submit-button>
    </div>
</form>
