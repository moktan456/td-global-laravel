@props(['steps'])
{{-- Numbered "how it works" cards. The number is decorative; screen readers hear "Step n:" in the heading. --}}
<ol data-reveal-group class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ($steps as $i => $s)
        <li class="rounded-card border border-line bg-white p-6 {{ ui('cardLift') }}">
            <span aria-hidden="true" class="inline-flex size-10 items-center justify-center rounded-full bg-brand font-display text-lg font-semibold text-white">{{ $i + 1 }}</span>
            <h3 class="mt-4 font-display text-xl font-semibold text-navy-900"><span class="sr-only">Step {{ $i + 1 }}: </span>{{ $s['title'] }}</h3>
            <p class="mt-2 text-muted">{{ $s['body'] }}</p>
        </li>
    @endforeach
</ol>
