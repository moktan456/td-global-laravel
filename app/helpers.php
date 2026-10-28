<?php

use App\Support\Content;
use App\Support\FormState;
use App\Support\Ui;
use Carbon\Carbon;

if (! function_exists('site')) {
    /** The `site` block from resources/content/site.php. Pass a dotted key, e.g. site('contact.email'). */
    function site(?string $key = null): mixed
    {
        return $key === null ? Content::site() : data_get(Content::site(), $key);
    }
}

if (! function_exists('content')) {
    /** A content file, or one key of it: content('site.plans'), content('faq'). */
    function content(string $key): mixed
    {
        [$file, $rest] = array_pad(explode('.', $key, 2), 2, null);

        return $rest === null ? Content::load($file) : data_get(Content::load($file), $rest);
    }
}

if (! function_exists('is_filled')) {
    /** True when a content value is set and is not a "[PLACEHOLDER]". */
    function is_filled(?string $value): bool
    {
        return $value !== null && trim($value) !== '' && ! preg_match('/^\[.*\]$/', trim($value));
    }
}

if (! function_exists('whatsapp_url')) {
    /** wa.me link for a WhatsApp number in international format, or null when none is set. */
    function whatsapp_url(?string $number): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $number);

        return $digits ? "https://wa.me/{$digits}" : null;
    }
}

if (! function_exists('absolute_url')) {
    /** Absolute URL for a site path, on the canonical origin. */
    function absolute_url(string $path = '/'): string
    {
        return site('url').'/'.ltrim($path, '/');
    }
}

if (! function_exists('image_src')) {
    /** URL of a photo from the image manifest (resources/content/images.php). */
    function image_src(string $key): string
    {
        $img = content('images')[$key];

        if (! empty($img['upload'])) {
            return '/media/'.$img['upload']; // a photo added in the admin panel
        }

        return ! empty($img['local']) ? "/photos/{$img['local']}.jpg" : "/images/{$key}-{$img['unsplashId']}.jpg";
    }
}

if (! function_exists('image_alt')) {
    function image_alt(string $key): string
    {
        return content('images')[$key]['alt'];
    }
}

if (! function_exists('format_date')) {
    function format_date(string $iso): string
    {
        // Same as Intl.DateTimeFormat('en', {day:'numeric', month:'long', year:'numeric'}): "September 1, 2026".
        return Carbon::parse($iso, 'UTC')->format('F j, Y');
    }
}

if (! function_exists('nav_active')) {
    /** Is this nav link the current page (or a parent of it)? Same rule as the Next.js header. */
    function nav_active(string $href): bool
    {
        $path = '/'.ltrim(request()->path(), '/');
        $path = $path === '/' ? '/' : rtrim($path, '/');

        return $href === '/' ? $path === '/' : ($path === $href || str_starts_with($path, $href.'/'));
    }
}

if (! function_exists('ui')) {
    /** Shared class recipes (buttons, card lift, photo frame), identical to the Next.js components. */
    function ui(string $name, string $variant = 'primary'): string
    {
        return Ui::class($name, $variant);
    }
}

if (! function_exists('site_plans')) {
    function site_plans(): array
    {
        return content('site.plans');
    }
}

if (! function_exists('form_state')) {
    /** The state a form was left in by the last submit (flashed to the session), or idle. */
    function form_state(string $name): FormState
    {
        $flashed = session("forms.{$name}");

        return $flashed ? FormState::fromArray($flashed) : FormState::idle();
    }
}

if (! function_exists('image_srcset')) {
    /** `srcset` of resized WebP copies for a local photo (made by `npm run photos`), or null when there are none. */
    function image_srcset(string $key): ?string
    {
        $img = content('images')[$key] ?? null;
        if (! $img || ! empty($img['upload']) || empty($img['local'])) {
            return null;
        }
        static $manifest = null;
        $manifest ??= json_decode((string) @file_get_contents(resource_path('content/photo-widths.json')), true) ?: [];
        $widths = $manifest[$img['local']]['widths'] ?? [];

        return $widths ? implode(', ', array_map(fn ($w) => "/photos/w/{$img['local']}-{$w}.webp {$w}w", $widths)) : null;
    }
}
