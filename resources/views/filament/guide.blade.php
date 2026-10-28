{{-- Filament's stylesheet only has its own classes, so the guide carries its own small stylesheet. It uses the current text colour and Filament's colour variables, so it follows light and dark mode. --}}
<x-filament-panels::page>
    <style>
        .guide-layout { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem; align-items: start; }
        .guide { max-width: 52rem; line-height: 1.65; font-size: .95rem; min-width: 0; }
        .guide-side { display: none; }
        @media (min-width: 64rem) {
            .guide-layout { grid-template-columns: 15rem minmax(0, 1fr); }
            .guide-side { display: block; position: sticky; top: 5.5rem; max-height: calc(100vh - 7rem); overflow-y: auto; padding-right: .5rem; font-size: .88rem; }
            .guide .guide-toc { display: none; }
        }
        .guide-side-title { font-size: .72rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; opacity: .6; margin: 0 0 .6rem .5rem; }
        .guide-side ol, .guide-side ul { list-style: none; margin: 0; padding: 0; }
        .guide-side a { display: flex; gap: .55rem; padding: .35rem .5rem; border-radius: .4rem; text-decoration: none; color: inherit; opacity: .8; line-height: 1.35; }
        .guide-side a:hover { opacity: 1; background: color-mix(in srgb, currentColor 8%, transparent); }
        .guide-side a[aria-current] { opacity: 1; font-weight: 600; color: rgb(var(--primary-600)); background: color-mix(in srgb, rgb(var(--primary-500)) 12%, transparent); }
        .guide-side ul { display: none; margin: .1rem 0 .4rem 1.55rem; border-left: 1px solid color-mix(in srgb, currentColor 18%, transparent); }
        .guide-side li[data-open] > ul { display: block; }
        .guide-side ul a { padding: .25rem .6rem; font-size: .84rem; }
        .gn-num { flex: none; width: 1.3rem; height: 1.3rem; display: inline-flex; align-items: center; justify-content: center; font-size: .72rem; border-radius: 999px; background: color-mix(in srgb, currentColor 12%, transparent); }
        .guide h3 { scroll-margin-top: 5rem; }
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
    <div class="guide-layout">
        <nav class="guide-side" aria-label="Chapters" data-guide-side>
            <p class="guide-side-title">Chapters</p>
        </nav>
        <div class="guide">
            @include('filament.guide-body')
        </div>
    </div>
    @include('filament.guide-nav-script')
</x-filament-panels::page>
