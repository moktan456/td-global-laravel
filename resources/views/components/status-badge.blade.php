@props(['status'])
@php
    // Text label always present, so colour is never the only signal.
    $tone = [
        'declaration_submitted' => 'bg-sand text-navy-900',
        'pending_valuation' => 'bg-sand text-navy-900',
        'pending_risk' => 'bg-sand text-navy-900',
        'pending_physical_examination' => 'bg-gold/35 text-navy-900',
        'pending_payment' => 'bg-gold/35 text-navy-900',
        'pending_release' => 'bg-sand text-navy-900',
        'released' => 'bg-success/15 text-success',
][$status] ?? 'bg-sand text-navy-900';
@endphp
<span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold {{ $tone }}">{{ \App\Support\Statuses::info($status)['label'] }}</span>
