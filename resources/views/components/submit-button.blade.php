{{-- While the form is sending, the script disables this button and swaps in a spinner and "Sending…" (like useFormStatus did). --}}
<button type="submit" data-submit {{ $attributes->merge(['class' => ui('button')]) }}>
    <span data-submit-label>{{ $slot }}</span>
    <span data-submit-pending hidden class="inline-flex items-center gap-2">
        <x-lucide name="loader-circle" class="size-5 animate-spin motion-reduce:animate-none" />
        Sending…
    </span>
</button>
