@php
    // Contact form. Placeholders are realistic examples (prefixed "e.g.") and sit below visible labels; the email uses
    // the reserved example.com domain so it can never be a real address.
    $tone = $tone ?? 'light';
    $state = $state ?? form_state('contact');
    $ids = ['name' => 'contact-name', 'email' => 'contact-email', 'message' => 'contact-message'];
@endphp
<form id="contact-form" method="post" action="/contact" data-enhance novalidate class="grid gap-5">
    @csrf
    <input type="hidden" name="tone" value="{{ $tone }}">
    <x-form-status :state="$state" :ids="$ids" :tone="$tone" />
    <div class="grid gap-5 sm:grid-cols-2">
        <x-text-field :id="$ids['name']" name="name" label="Name" autocomplete="name" placeholder="e.g. Karma Wangchuk" :value="$state->value('name')" :error="$state->error('name')" :tone="$tone" />
        <x-text-field :id="$ids['email']" name="email" type="email" label="Email" autocomplete="email" placeholder="e.g. karma@example.com" :value="$state->value('email')" :error="$state->error('email')" :tone="$tone" />
    </div>
    <x-text-area :id="$ids['message']" name="message" label="Message" hint="Tell us about your shipment or question." placeholder="e.g. We're importing two pallets of hardware by road through Gelephu next week. Which documents do you need from us?" :rows="5" :value="$state->value('message')" :error="$state->error('message')" :tone="$tone" />
    <div>
        <x-submit-button class="w-full sm:w-auto">Send message</x-submit-button>
    </div>
</form>
