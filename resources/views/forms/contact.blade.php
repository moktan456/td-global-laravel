@php
    // Contact form. Placeholders are realistic examples (prefixed "e.g.") and sit below visible labels; the email uses
    // the reserved example.com domain so it can never be a real address.
    $tone = $tone ?? 'light';
    $state = $state ?? form_state('contact');
    $ids = ['name' => 'contact-name', 'email' => 'contact-email', 'message' => 'contact-message'];
@endphp
<form id="contact-form" method="post" action="/contact" data-enhance novalidate class="grid gap-5">
    @csrf
    <div aria-hidden="true" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden">
        <label>Leave this field empty<input type="text" name="homepage_url" tabindex="-1" autocomplete="off"></label>
    </div>
    <input type="hidden" name="tone" value="{{ $tone }}">
    <x-form-status :state="$state" :ids="$ids" :tone="$tone" />
    <div class="grid gap-5 sm:grid-cols-2">
        <x-text-field :id="$ids['name']" name="name" :label="label('contactForm.nameLabel')" autocomplete="name" :placeholder="label('contactForm.namePlaceholder')" :value="$state->value('name')" :error="$state->error('name')" :tone="$tone" />
        <x-text-field :id="$ids['email']" name="email" type="email" :label="label('contactForm.emailLabel')" autocomplete="email" :placeholder="label('contactForm.emailPlaceholder')" :value="$state->value('email')" :error="$state->error('email')" :tone="$tone" />
    </div>
    <x-text-area :id="$ids['message']" name="message" :label="label('contactForm.messageLabel')" :hint="label('contactForm.messageHint')" :placeholder="label('contactForm.messagePlaceholder')" :rows="5" :value="$state->value('message')" :error="$state->error('message')" :tone="$tone" />
    <div>
        <x-submit-button class="w-full sm:w-auto">{{ label('contactForm.submit') }}</x-submit-button>
    </div>
</form>
