@props(['id', 'label', 'hint' => null, 'error' => null, 'tone' => 'light', 'optional' => false])
@php
    $labelTone = ['light' => 'text-navy-900', 'dark' => 'text-on-dark'][$tone];
    $hintTone = ['light' => 'text-muted', 'dark' => 'text-on-dark-muted'][$tone];
    $errorTone = ['light' => 'text-error', 'dark' => 'text-gold'][$tone];
@endphp
<div {{ $attributes }}>
    <label for="{{ $id }}" class="block text-base font-semibold {{ $labelTone }}">
        {{ $label }}
        @if ($optional)
            <span class="font-normal {{ $hintTone }}"> (optional)</span>
        @endif
    </label>
    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1 text-sm {{ $hintTone }}">{{ $hint }}</p>
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="mt-1 flex items-center gap-1.5 text-sm font-semibold {{ $errorTone }}">
            <x-lucide name="circle-alert" class="size-4 shrink-0" />
            {{ $error }}
        </p>
    @endif
    <div class="mt-2">{{ $slot }}</div>
</div>
