@php
    $rates = content('rates');
    $initial = \App\Support\Rates::estimate('standard', 'road', 'import', 1);
    $radioCard = 'flex min-h-11 cursor-pointer items-center gap-3 rounded-control border border-control bg-white px-4 py-3 transition-[border-color,box-shadow] duration-(--duration-hover) hover:border-navy-900 hover:shadow-sm has-[:checked]:border-2 has-[:checked]:border-brand has-[:focus-visible]:outline has-[:focus-visible]:outline-3 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-brand';
    $nu = fn (int $n) => \App\Support\Rates::nu($n);
@endphp
<div data-calculator data-rates='@json($rates)' class="grid gap-10 lg:grid-cols-[1.2fr_1fr]">
    <form class="grid gap-8" aria-label="Cost calculator" onsubmit="return false">
        <fieldset>
            <legend class="font-semibold text-navy-900">{{ label('calculatorForm.directionLegend') }}</legend>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                @foreach (['import', 'export'] as $d)
                    <label class="{{ $radioCard }}">
                        <input type="radio" name="direction" value="{{ $d }}" @checked($d === 'import') class="size-5 shrink-0 accent-brand focus-visible:outline-none">
                        <span class="capitalize">{{ label('calculatorForm.'.$d.'Option') }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset>
            <legend class="font-semibold text-navy-900">{{ label('calculatorForm.typeLegend') }}</legend>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                @foreach ([['standard', label('calculatorForm.standardOption'), label('calculatorForm.standardHint')], ['complex', label('calculatorForm.complexOption'), label('calculatorForm.complexHint')]] as [$value, $label, $hint])
                    <label class="{{ $radioCard }} items-start">
                        <input type="radio" name="type" value="{{ $value }}" @checked($value === 'standard') class="mt-0.5 size-5 shrink-0 accent-brand focus-visible:outline-none">
                        <span>
                            <span class="block font-semibold">{{ $label }}</span>
                            <span class="block text-sm text-muted">{{ $hint }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div>
            <label for="calc-mode" class="font-semibold text-navy-900">{{ label('calculatorForm.modeLabel') }}</label>
            <select id="calc-mode" name="mode" class="mt-2 block min-h-11 w-full rounded-control border border-control bg-white px-3 text-base">
                <option value="air">{{ label('calculatorForm.airOption') }}</option>
                <option value="road" selected>{{ label('calculatorForm.roadOption') }}</option>
                <option value="rail">{{ label('calculatorForm.railOption') }}</option>
            </select>
        </div>

        <div>
            <label for="calc-lines" class="font-semibold text-navy-900">{{ label('calculatorForm.linesLabel') }}</label>
            <p id="calc-lines-hint" class="mt-1 text-sm text-muted">{{ label('calculatorForm.linesHint', ['included' => $rates['includedLines']]) }}</p>
            <p id="calc-lines-error" data-lines-error hidden class="mt-1 text-sm font-semibold text-error">Enter a whole number from 1 to 500.</p>
            <input id="calc-lines" name="lines" type="number" inputmode="numeric" min="1" max="500" step="1" value="1" aria-describedby="calc-lines-hint" class="mt-2 block min-h-11 w-40 rounded-control border border-control bg-white px-3 text-base aria-[invalid=true]:border-2 aria-[invalid=true]:border-error">
        </div>
    </form>

    <div class="on-dark self-start rounded-card bg-navy-900 p-6 text-white sm:p-8 lg:sticky lg:top-28">
        <h2 class="font-display text-2xl font-semibold">{{ label('calculatorForm.estimateTitle') }}</h2>
        <output for="calc-mode calc-lines" aria-live="polite" class="mt-4 block">
            <span data-result-ok>
                <span data-total class="block font-display text-5xl font-semibold">{{ $nu($initial['total']) }}</span>
                <span class="mt-1 block text-on-dark-muted">{{ label('calculatorForm.estimateCaption') }}</span>
            </span>
            <span data-result-bad hidden class="block text-gold">Fix the number of tariff lines to see an estimate.</span>
        </output>
        <dl data-breakdown class="mt-6 grid grid-cols-[1fr_auto] gap-x-4 gap-y-2 border-t border-line-dark pt-6 text-on-dark-muted">
            <dt>Base fee (<span data-type>standard</span>)</dt>
            <dd data-base class="text-right text-white">{{ $nu($initial['base']) }}</dd>
            <dt>Extra tariff lines (<span data-extra-lines>{{ $initial['extraLines'] }}</span>)</dt>
            <dd data-line-fee class="text-right text-white">{{ $nu($initial['lineFee']) }}</dd>
            <dt>Handling (<span data-mode>road</span>)</dt>
            <dd data-handling class="text-right text-white">{{ $nu($initial['handling']) }}</dd>
            <dt data-discount-label hidden>Export discount</dt>
            <dd data-discount hidden class="text-right text-white"></dd>
        </dl>
        <p class="mt-6 text-sm text-on-dark-muted">{{ label('calculatorForm.disclaimer') }}</p>
        <x-button-link href="/quote" class="mt-6 w-full">{{ label('buttons.exactQuote') }}</x-button-link>
    </div>
</div>
