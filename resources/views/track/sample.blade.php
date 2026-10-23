{{-- Track page, right column: an example shipment that ticks through the real clearance stages (animation is CSS only). --}}
@php
    $keys = \App\Support\Statuses::keys();
@endphp
<aside aria-labelledby="sample-heading" class="on-dark rounded-card bg-navy-900 p-6 text-white sm:p-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm font-semibold uppercase tracking-wide text-gold">Example</p>
        <p class="rounded-full border border-white/25 px-3 py-1 font-mono text-sm text-on-dark">TD-2610-SAMPLE</p>
    </div>
    <h2 id="sample-heading" class="mt-3 font-display text-2xl font-semibold sm:text-3xl">What you&apos;ll see</h2>
    <p class="mt-2 text-on-dark-muted">Every shipment moves through clear stages. Each time we update one, your tracking page shows it.</p>

    <ol class="mt-8 grid gap-5 border-l-2 border-white/20 pl-6">
        @foreach ($keys as $i => $key)
            @php($info = \App\Support\Statuses::info($key))
            <li class="sample-step-{{ $i }} relative">
                <span class="absolute -left-[2.2rem] top-0.5 block size-5 rounded-full border-2 border-white/35 bg-navy-900"></span>
                <x-lucide name="circle-check" class="sample-done absolute -left-[2.2rem] top-0.5 size-5 rounded-full bg-navy-900 text-gold" />
                <p class="sample-label font-semibold">{{ $info['label'] }}</p>
                <p class="sample-text text-sm text-on-dark-muted">{{ $info['clientText'] }}</p>
            </li>
        @endforeach
    </ol>

    <ul class="mt-8 grid gap-3 border-t border-white/15 pt-6 text-sm text-on-dark">
        <li class="flex items-start gap-3"><x-lucide name="shield-check" class="mt-0.5 size-5 shrink-0 text-gold" /><span>No account or sign-in. Your tracking number is all you need.</span></li>
        <li class="flex items-start gap-3"><x-lucide name="mail" class="mt-0.5 size-5 shrink-0 text-gold" /><span>Updates also reach you by email or Telegram when you choose.</span></li>
        <li class="flex items-start gap-3"><x-lucide name="message-circle" class="mt-0.5 size-5 shrink-0 text-gold" /><span>A note from our team appears here when something needs your attention.</span></li>
    </ul>
</aside>
