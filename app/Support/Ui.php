<?php

namespace App\Support;

/** Class-string recipes shared by the Blade components (ported from components/ui/Button.tsx and motion.ts). */
class Ui
{
    /*
     * Hover and keyboard focus share the same feedback: a 1px lift and a soft shadow; pressing sinks the button
     * back down. Transform/colour/shadow only. Arrow icons opt in to a nudge with the global `arrow-nudge` class.
     */
    private const BUTTON_BASE = 'inline-flex min-h-11 items-center justify-center gap-2 rounded-control px-5 py-2.5 text-base font-semibold '
        .'transition-[background-color,color,border-color,box-shadow,translate] duration-(--duration-hover) ease-out-quart '
        .'hover:-translate-y-px focus-visible:-translate-y-px active:translate-y-0 active:duration-75 '
        .'disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0';

    private const BUTTON_VARIANTS = [
        'primary' => 'bg-brand text-white hover:bg-brand-dark hover:shadow-lift focus-visible:shadow-lift',
        'outline' => 'border border-navy-900 text-navy-900 hover:bg-action hover:text-white hover:shadow-lift',
        'outline-light' => 'border border-white text-white hover:bg-white hover:text-navy-900',
        'ghost' => 'text-navy-900 underline-offset-4 hover:underline',
    ];

    /** Card lifts 4px with a soft shadow. Hover effects also apply on keyboard focus inside the card. */
    private const CARD_LIFT = 'transition-[translate,box-shadow,border-color,background-color] duration-(--duration-enter) ease-out-quart '
        .'hover:-translate-y-1 hover:shadow-lift focus-within:-translate-y-1 focus-within:shadow-lift';

    /** Filter chips (stage filters on the staff Shipments page): on = the chosen one, off = another choice, empty = a choice with nothing in it. */
    private const CHIP_BASE = 'inline-flex min-h-11 items-center gap-2 rounded-full border px-4 text-[0.9375rem] font-semibold transition-colors duration-150';

    private const CHIP_VARIANTS = [
        'on' => 'border-navy-900 bg-action text-white',
        'off' => 'border-line bg-surface text-navy-900 hover:border-navy-900',
        'empty' => 'border-line bg-surface text-muted hover:border-navy-900 hover:text-navy-900',
    ];

    private const PHOTO_FRAME = 'group relative overflow-hidden rounded-card';

    public static function class(string $name, string $variant = 'primary'): string
    {
        return match ($name) {
            'button' => self::BUTTON_BASE.' '.(self::BUTTON_VARIANTS[$variant] ?? self::BUTTON_VARIANTS['primary']),
            'chip' => self::CHIP_BASE.' '.(self::CHIP_VARIANTS[$variant] ?? self::CHIP_VARIANTS['off']),
            'cardLift' => self::CARD_LIFT,
            'photoFrame' => self::PHOTO_FRAME,
            default => '',
        };
    }
}
