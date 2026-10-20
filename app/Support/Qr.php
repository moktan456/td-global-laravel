<?php

namespace App\Support;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/** Draws a QR code as inline SVG (pure PHP: no image extension and no JavaScript needed). */
class Qr
{
    /** Navy modules on white with a clear border, so phone cameras read it easily. */
    public static function svg(string $text, int $size = 176): string
    {
        $style = new RendererStyle($size, 4, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(20, 34, 74)));
        $svg = (new Writer(new ImageRenderer($style, new SvgImageBackEnd)))->writeString($text);

        // Drop the XML prolog so the markup can sit inside a page.
        return trim(preg_replace('/^<\?xml[^>]*\?>\s*/', '', $svg));
    }
}
