@props(['status'])
@php
    // Text label always present, so colour is never the only signal.
    $tone = [
        'received' => 'bg-sand text-navy-900',
        'documents_pending' => 'bg-gold/35 text-navy-900',
        'documents_checked' => 'bg-sand text-navy-900',
        'declaration_lodged' => 'bg-sand text-navy-900',
        'duties_due' => 'bg-gold/35 text-navy-900',
        'cleared' => 'bg-success/15 text-success',
        'delivered' => 'bg-success/15 text-success',
        'on_hold' => 'bg-error/10 text-error',
    ][$status] ?? 'bg-sand text-navy-900';
@endphp
<span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold {{ $tone }}">{{ \App\Support\Statuses::info($status)['label'] }}</span>
