@props(['state', 'ids' => [], 'tone' => 'light'])
{{--
    Announces the result of a submission. On error it lists each problem as a link to its field and moves focus here
    (GOV.UK error-summary pattern). Nothing to announce yet: renders nothing, so the form doesn't start with a gap.
--}}
@if ($state->status !== 'idle')
    @php
        $ok = $state->status === 'success';
        $box = $ok
            ? ($tone === 'dark' ? 'border-success bg-navy-800 text-white' : 'border-success bg-surface text-ink')
            : ($tone === 'dark' ? 'border-gold bg-navy-800 text-white' : 'border-error bg-surface text-ink');
        $errorTone = ['light' => 'text-error', 'dark' => 'text-gold'][$tone];
    @endphp
    <div data-form-status tabindex="-1" role="{{ $ok ? 'status' : 'alert' }}" class="rounded-tile border-2 p-4 focus:outline-none {{ $box }}">
        <p class="flex items-center gap-2 font-semibold">
            @if ($ok)
                <x-lucide name="circle-check" class="size-5 shrink-0 {{ $tone === 'dark' ? 'text-gold' : 'text-success' }}" />
            @else
                <x-lucide name="circle-alert" class="size-5 shrink-0 {{ $errorTone }}" />
            @endif
            {{ $state->message }}
        </p>
        @if ($state->mailto)
            <a href="{{ $state->mailto }}" class="mt-3 inline-flex min-h-11 items-center font-semibold underline decoration-2 underline-offset-4 {{ $tone === 'dark' ? 'text-gold' : 'text-link' }}">Open the email in your mail app</a>
        @endif
        @if (count($state->errors))
            <ul class="mt-2 list-disc pl-10">
                @foreach ($state->errors as $name => $msg)
                    <li><a href="#{{ $ids[$name] ?? $name }}" class="underline underline-offset-2">{{ $msg }}</a></li>
                @endforeach
            </ul>
        @endif
    </div>
@endif
