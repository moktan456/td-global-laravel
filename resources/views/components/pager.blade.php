@props(['paginator', 'label' => 'items'])
{{-- Previous / Next with "Page 2 of 4". Keeps the search and filters in the links (the controller calls withQueryString()). --}}
@if ($paginator->hasPages())
    <nav aria-label="Pagination" class="mt-6 flex flex-wrap items-center justify-between gap-3">
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" class="inline-flex min-h-11 items-center rounded-control border border-line px-4 font-semibold text-muted">Previous</span>
        @else
            <x-button-link :href="$paginator->previousPageUrl()" variant="outline" rel="prev">Previous<span class="sr-only"> page of {{ $label }}</span></x-button-link>
        @endif
        <p class="text-muted">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</p>
        @if ($paginator->hasMorePages())
            <x-button-link :href="$paginator->nextPageUrl()" variant="outline" rel="next">Next<span class="sr-only"> page of {{ $label }}</span></x-button-link>
        @else
            <span aria-disabled="true" class="inline-flex min-h-11 items-center rounded-control border border-line px-4 font-semibold text-muted">Next</span>
        @endif
    </nav>
@endif
