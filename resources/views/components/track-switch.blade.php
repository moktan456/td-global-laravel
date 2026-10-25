@props(['name', 'label', 'description' => null, 'checked' => false])
{{-- An on/off switch (a real checkbox with role="switch"). The state is written as On / Off as well as shown by position and
     colour. The whole card is the click target. --}}
<label class="flex cursor-pointer items-start gap-3 rounded-control border border-line bg-white p-4 transition-colors duration-150 has-[:checked]:border-navy-900 has-[:focus-visible]:outline has-[:focus-visible]:outline-3 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-brand">
    <input type="checkbox" role="switch" name="{{ $name }}" value="1" @checked($checked) {{ $attributes->except('class') }} class="peer sr-only">
    <span aria-hidden="true" class="relative mt-0.5 inline-block h-6 w-11 shrink-0 rounded-full bg-control transition-colors duration-150 after:absolute after:left-0.5 after:top-0.5 after:size-5 after:rounded-full after:bg-white after:shadow-sm after:transition-transform after:duration-150 peer-checked:bg-navy-900 peer-checked:after:translate-x-5"></span>
    <span class="min-w-0 flex-1">
        <span class="block font-semibold text-navy-900">{{ $label }}</span>
        @if ($description)
            <span class="mt-0.5 block text-sm text-muted">{{ $description }}</span>
        @endif
    </span>
    <span class="shrink-0 text-sm font-semibold text-muted peer-checked:hidden">Off</span>
    <span class="hidden shrink-0 text-sm font-semibold text-navy-900 peer-checked:inline">On</span>
</label>
