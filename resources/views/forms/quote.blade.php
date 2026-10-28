@php
    $state = $state ?? form_state('quote');
    $plan = $plan ?? null;
    $names = ['name', 'email', 'company', 'direction', 'mode', 'goods', 'details'];
    $ids = collect($names)->mapWithKeys(fn ($n) => [$n => "quote-{$n}"])->all();
@endphp
<form id="quote-form" method="post" action="/quote" data-enhance novalidate class="grid gap-6">
    @csrf
    <div aria-hidden="true" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden">
        <label>Leave this field empty<input type="text" name="homepage_url" tabindex="-1" autocomplete="off"></label>
    </div>
    <x-form-status :state="$state" :ids="$ids" />
    @if ($plan)
        <p class="rounded-tile bg-sand px-4 py-3">
            {{ label('quoteForm.planLabel') }} <strong>{{ $plan }}</strong>
            <input type="hidden" name="plan" value="{{ $plan }}">
        </p>
    @endif
    <fieldset class="grid gap-5 sm:grid-cols-2">
        <legend class="mb-4 font-display text-xl font-semibold text-navy-900">{{ label('quoteForm.detailsLegend') }}</legend>
        <x-text-field :id="$ids['name']" name="name" :label="label('quoteForm.nameLabel')" autocomplete="name" :placeholder="label('quoteForm.namePlaceholder')" :value="$state->value('name')" :error="$state->error('name')" />
        <x-text-field :id="$ids['email']" name="email" type="email" :label="label('quoteForm.emailLabel')" autocomplete="email" :placeholder="label('quoteForm.emailPlaceholder')" :value="$state->value('email')" :error="$state->error('email')" />
        <x-text-field :id="$ids['company']" name="company" :label="label('quoteForm.companyLabel')" :optional="true" autocomplete="organization" :placeholder="label('quoteForm.companyPlaceholder')" :value="$state->value('company')" wrapper-class="sm:col-span-2" />
    </fieldset>
    <fieldset class="grid gap-5 sm:grid-cols-2">
        <legend class="mb-4 font-display text-xl font-semibold text-navy-900">{{ label('quoteForm.shipmentLegend') }}</legend>
        <x-select-field :id="$ids['direction']" name="direction" :label="label('quoteForm.directionLabel')" :value="$state->value('direction')" :error="$state->error('direction')" :options="[['value' => 'import', 'label' => label('quoteForm.importOption')], ['value' => 'export', 'label' => label('quoteForm.exportOption')]]" />
        <x-select-field :id="$ids['mode']" name="mode" :label="label('quoteForm.modeLabel')" :value="$state->value('mode')" :error="$state->error('mode')" :options="[['value' => 'air', 'label' => label('quoteForm.airOption')], ['value' => 'road', 'label' => label('quoteForm.roadOption')], ['value' => 'rail', 'label' => label('quoteForm.railOption')]]" />
        <x-text-field :id="$ids['goods']" name="goods" :label="label('quoteForm.goodsLabel')" :hint="label('quoteForm.goodsHint')" :value="$state->value('goods')" :error="$state->error('goods')" wrapper-class="sm:col-span-2" />
        <x-text-area :id="$ids['details']" name="details" :label="label('quoteForm.detailsLabel')" :optional="true" :rows="4" :value="$state->value('details')" wrapper-class="sm:col-span-2" />
    </fieldset>
    <div>
        <x-submit-button class="w-full sm:w-auto">{{ label('quoteForm.submit') }}</x-submit-button>
    </div>
</form>
