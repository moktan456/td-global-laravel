@php
    $state = $state ?? form_state('quote');
    $plan = $plan ?? null;
    $names = ['name', 'email', 'company', 'direction', 'mode', 'goods', 'details'];
    $ids = collect($names)->mapWithKeys(fn ($n) => [$n => "quote-{$n}"])->all();
@endphp
<form id="quote-form" method="post" action="/quote" data-enhance novalidate class="grid gap-6">
    @csrf
    <x-form-status :state="$state" :ids="$ids" />
    @if ($plan)
        <p class="rounded-tile bg-sand px-4 py-3">
            Selected plan: <strong>{{ $plan }}</strong>
            <input type="hidden" name="plan" value="{{ $plan }}">
        </p>
    @endif
    <fieldset class="grid gap-5 sm:grid-cols-2">
        <legend class="mb-4 font-display text-xl font-semibold text-navy-900">Your details</legend>
        <x-text-field :id="$ids['name']" name="name" label="Name" autocomplete="name" placeholder="e.g. Karma Wangchuk" :value="$state->value('name')" :error="$state->error('name')" />
        <x-text-field :id="$ids['email']" name="email" type="email" label="Email" autocomplete="email" placeholder="e.g. karma@example.com" :value="$state->value('email')" :error="$state->error('email')" />
        <x-text-field :id="$ids['company']" name="company" label="Company" :optional="true" autocomplete="organization" placeholder="e.g. Wangchuk Traders" :value="$state->value('company')" wrapper-class="sm:col-span-2" />
    </fieldset>
    <fieldset class="grid gap-5 sm:grid-cols-2">
        <legend class="mb-4 font-display text-xl font-semibold text-navy-900">Your shipment</legend>
        <x-select-field :id="$ids['direction']" name="direction" label="Import or export" :value="$state->value('direction')" :error="$state->error('direction')" :options="[['value' => 'import', 'label' => 'Import'], ['value' => 'export', 'label' => 'Export']]" />
        <x-select-field :id="$ids['mode']" name="mode" label="Shipping method" :value="$state->value('mode')" :error="$state->error('mode')" :options="[['value' => 'air', 'label' => 'Air freight'], ['value' => 'road', 'label' => 'Road freight'], ['value' => 'rail', 'label' => 'Rail freight']]" />
        <x-text-field :id="$ids['goods']" name="goods" label="What are you shipping?" hint="For example: 2 pallets of auto parts." :value="$state->value('goods')" :error="$state->error('goods')" wrapper-class="sm:col-span-2" />
        <x-text-area :id="$ids['details']" name="details" label="Anything else we should know?" :optional="true" :rows="4" :value="$state->value('details')" wrapper-class="sm:col-span-2" />
    </fieldset>
    <div>
        <x-submit-button class="w-full sm:w-auto">Request my free quote</x-submit-button>
    </div>
</form>
