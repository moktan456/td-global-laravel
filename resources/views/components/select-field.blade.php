@props(['id', 'name', 'label', 'options', 'value' => null, 'hint' => null, 'error' => null, 'tone' => 'light', 'optional' => false, 'placeholder' => 'Choose…', 'wrapperClass' => ''])
@php
    $control = ['light' => 'border-control bg-white text-ink placeholder:text-muted', 'dark' => 'border-control-dark bg-navy-800 text-white placeholder:text-on-dark-muted'][$tone];
    $describedBy = trim(($hint ? "{$id}-hint " : '').($error ? "{$id}-error" : ''));
@endphp
<x-field-shell :id="$id" :label="$label" :hint="$hint" :error="$error" :tone="$tone" :optional="$optional" class="{{ $wrapperClass }}">
    <select
        id="{{ $id }}" name="{{ $name }}"
        @unless ($optional) required @endunless
        @if ($error) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        class="block w-full rounded-control border px-3 py-2.5 text-base transition-colors duration-200 aria-[invalid=true]:border-2 min-h-11 {{ $control }}"
    >
        <option value="" disabled @selected(! $value)>{{ $placeholder }}</option>
        @foreach ($options as $o)
            <option value="{{ $o['value'] }}" @selected($value === $o['value'])>{{ $o['label'] }}</option>
        @endforeach
    </select>
</x-field-shell>
