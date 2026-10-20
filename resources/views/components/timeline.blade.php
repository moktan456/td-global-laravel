@props(['events', 'audience' => 'client'])
@if (count($events) === 0)
    <p class="text-muted">No updates yet.</p>
@else
    <ol class="grid gap-5 border-l-2 border-line pl-6">
        @foreach (array_reverse($events) as $i => $e)
            @php
                $info = \App\Support\Statuses::info($e['status']);
                $at = \Carbon\Carbon::parse($e['at'])->timezone('Asia/Thimphu');
            @endphp
            <li class="relative">
                <x-lucide name="circle-check" class="absolute -left-[2.2rem] top-0.5 size-5 rounded-full bg-ivory text-green-dark" />
                <p class="font-semibold text-navy-900">{{ $info['label'] }}</p>
                <p class="text-sm text-muted"><time datetime="{{ $e['at'] }}">{{ $at->format('j M Y, H:i') }}</time></p>
                @if ($audience === 'client' && $i === 0)
                    <p class="mt-1 text-ink">{{ $info['clientText'] }}</p>
                @endif
                @if (! empty($e['note']))
                    <p class="mt-1 text-ink">{{ $e['note'] }}</p>
                @endif
            </li>
        @endforeach
    </ol>
@endif
