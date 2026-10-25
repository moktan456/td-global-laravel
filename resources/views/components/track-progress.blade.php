@props(['position', 'total'])
{{-- "Step 3 of 7" as a row of small segments. The words carry the meaning; the bar is a picture of them. --}}
<span role="img" aria-label="Step {{ $position }} of {{ $total }}" class="flex gap-1">
    @for ($i = 1; $i <= $total; $i++)
        <span class="h-2 flex-1 rounded-full {{ $i <= $position ? 'bg-navy-900' : 'bg-line' }}"></span>
    @endfor
</span>
