@props(['id', 'name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'error' => null, 'tone' => 'light', 'optional' => false, 'autocomplete' => null, 'inputmode' => null, 'placeholder' => null, 'wrapperClass' => ''])
@php
    $control = ['light' => 'border-control bg-white text-ink placeholder:text-muted', 'dark' => 'border-control-dark bg-navy-800 text-white placeholder:text-on-dark-muted'][$tone];
    $describedBy = trim(($hint ? "{$id}-hint " : '').($error ? "{$id}-error" : ''));
    $isPassword = $type === 'password';
@endphp
<x-field-shell :id="$id" :label="$label" :hint="$hint" :error="$error" :tone="$tone" :optional="$optional" class="{{ $wrapperClass }}">
    <div class="relative">
        <input
            id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
            @if ($value !== null) value="{{ $value }}" @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if ($inputmode) inputmode="{{ $inputmode }}" @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @unless ($optional) required @endunless
            @if ($error) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($isPassword) autocapitalize="none" spellcheck="false" @endif
            class="block w-full rounded-control border px-3 py-2.5 text-base transition-colors duration-200 aria-[invalid=true]:border-2 min-h-11 {{ $control }} {{ $isPassword ? 'pr-12' : '' }}"
        >
        @if ($isPassword)
            <button type="button" data-password-toggle aria-pressed="false" aria-label="Show password" aria-controls="{{ $id }}" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-control text-muted hover:text-navy-900">
                <span data-icon-show><x-lucide name="eye" class="size-5" /></span>
                <span data-icon-hide hidden><x-lucide name="eye-off" class="size-5" /></span>
            </button>
        @endif
    </div>
</x-field-shell>
