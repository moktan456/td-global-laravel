@props(['id', 'name', 'label', 'value' => null, 'rows' => 5, 'hint' => null, 'error' => null, 'tone' => 'light', 'optional' => false, 'placeholder' => null, 'wrapperClass' => ''])
@php
    $control = ['light' => 'border-control bg-surface text-ink placeholder:text-muted', 'dark' => 'border-control-dark bg-navy-800 text-white placeholder:text-on-dark-muted'][$tone];
    $describedBy = trim(($hint ? "{$id}-hint " : '').($error ? "{$id}-error" : ''));
@endphp
<x-field-shell :id="$id" :label="$label" :hint="$hint" :error="$error" :tone="$tone" :optional="$optional" class="{{ $wrapperClass }}">
    <textarea
        id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @unless ($optional) required @endunless
        @if ($error) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        class="block w-full rounded-control border px-3 py-2.5 text-base transition-colors duration-200 aria-[invalid=true]:border-2 {{ $control }}"
    >{{ $value }}</textarea>
</x-field-shell>
