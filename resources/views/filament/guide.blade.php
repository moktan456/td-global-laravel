{{-- Filament's stylesheet only has its own classes, so the guide carries its own small stylesheet. It uses the current text colour and Filament's colour variables, so it follows light and dark mode. --}}
<x-filament-panels::page>
    <style>
        .guide { max-width: 52rem; line-height: 1.65; font-size: .95rem; }
        .guide h2 { font-size: 1.35rem; font-weight: 700; margin: 2.5rem 0 .75rem; padding-top: 1.25rem; border-top: 1px solid color-mix(in srgb, currentColor 15%, transparent); scroll-margin-top: 5rem; }
        .guide h3 { font-size: 1.05rem; font-weight: 600; margin: 1.5rem 0 .5rem; }
        .guide p, .guide ul, .guide ol, .guide dl { margin: .6rem 0; }
        .guide ul { list-style: disc; padding-left: 1.4rem; }
        .guide ol { padding-left: 1.4rem; }
        .guide ol.steps { list-style: decimal; }
        .guide li { margin: .3rem 0; padding-left: .2rem; }
        .guide code, .guide kbd { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .85em; padding: .1em .4em; border-radius: .3rem; background: color-mix(in srgb, currentColor 10%, transparent); }
        .guide kbd { border: 1px solid color-mix(in srgb, currentColor 25%, transparent); }
        .guide a { color: rgb(var(--primary-600)); text-decoration: underline; text-underline-offset: 2px; }
        .guide dt { font-weight: 600; margin-top: 1rem; }
        .guide dd { margin: .2rem 0 0; opacity: .9; }
        .guide article { margin: 1.25rem 0; padding: 1rem 1.25rem; border-radius: .75rem; border: 1px solid color-mix(in srgb, currentColor 15%, transparent); }
        .guide article h3 { margin-top: 0; }
        .guide .note, .guide .warn { padding: .6rem .9rem; border-radius: .5rem; font-size: .9rem; border-left: 4px solid rgb(var(--primary-500)); background: color-mix(in srgb, rgb(var(--primary-500)) 10%, transparent); }
        .guide .warn { border-left-color: rgb(var(--warning-500)); background: color-mix(in srgb, rgb(var(--warning-500)) 14%, transparent); }
        .guide .table-wrap { overflow-x: auto; border: 1px solid color-mix(in srgb, currentColor 15%, transparent); border-radius: .75rem; }
        .guide table { border-collapse: collapse; width: 100%; font-size: .88rem; }
        .guide th, .guide td { text-align: left; vertical-align: top; padding: .55rem .8rem; border-bottom: 1px solid color-mix(in srgb, currentColor 10%, transparent); }
        .guide th { font-weight: 600; background: color-mix(in srgb, currentColor 6%, transparent); }
        .guide tr:last-child td { border-bottom: 0; }
        .guide-toc { padding: 1rem 1.25rem; border-radius: .75rem; background: color-mix(in srgb, currentColor 6%, transparent); }
        .guide-toc-title { font-weight: 600; margin: 0 0 .25rem; }
        .guide-toc ol { list-style: decimal; margin: 0; columns: 2 14rem; }
    </style>
    <div class="guide">
        @include('filament.guide-body')
    </div>
</x-filament-panels::page>
