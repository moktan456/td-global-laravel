@props(['id', 'label' => 'Site search'])
<form role="search" aria-label="{{ $label }}" action="/search" {{ $attributes->merge(['class' => 'relative']) }}>
    <label for="{{ $id }}" class="sr-only">Search the site</label>
    <input id="{{ $id }}" type="search" name="q" placeholder="Search…" class="h-11 w-full rounded-control border border-control bg-white pl-3 pr-11 text-base text-ink placeholder:text-muted">
    <button type="submit" class="absolute inset-y-0 right-0 inline-flex w-11 items-center justify-center text-navy-900 hover:text-brand">
        <x-lucide name="search" class="size-5" />
        <span class="sr-only">Search</span>
    </button>
</form>
