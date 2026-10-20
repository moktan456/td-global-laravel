@props(['eyebrow' => null, 'title', 'id' => null, 'as' => 'h2', 'dark' => false, 'align' => 'left', 'intro' => null])
<div data-reveal {{ $attributes->merge(['class' => ($align === 'center' ? 'mx-auto text-center ' : '').'max-w-2xl']) }}>
    @if ($eyebrow)
        <p class="text-sm font-semibold uppercase tracking-[0.14em] {{ $dark ? 'text-gold' : 'text-brand' }}">{{ $eyebrow }}</p>
    @endif
    <{{ $as }} @if ($id) id="{{ $id }}" @endif class="mt-2 font-display text-3xl font-semibold leading-tight sm:text-4xl {{ $dark ? 'text-white' : 'text-navy-900' }}">{{ $title }}</{{ $as }}>
    @if ($intro)
        <div class="mt-4 text-lg {{ $dark ? 'text-on-dark-muted' : 'text-muted' }}">{{ $intro }}</div>
    @endif
</div>
