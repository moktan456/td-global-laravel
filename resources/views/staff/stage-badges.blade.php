{{-- A shipment's stage(s): one badge for each track it follows. With two tracks each badge says which track it is. --}}
@php($tracks = $s->tracks())
<span class="grid gap-1.5">
    @foreach ($tracks as $t)
        <span class="flex flex-wrap items-center gap-x-2 gap-y-1">
            @if (count($tracks) > 1)<span class="w-[4.75rem] shrink-0 text-xs text-muted">{{ $t === 'customs' ? 'Customs' : 'Transport' }}</span>@endif
            <x-status-badge :status="$s->stageOn($t)" />
        </span>
    @endforeach
</span>
