<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Makes the page's whitespace behave like the original React/JSX: there, the line breaks and indentation between two
 * tags are never part of the page, but in a Blade file they are. Left in, they add stray gaps inside flex rows (a
 * button with a screen-reader span next to an icon came out 8px wider). So whitespace that contains a line break and
 * sits between two tags is removed from the finished HTML, except inside <pre>, <textarea> and <script>. As a bonus
 * every page gets a little smaller.
 */
class JsxWhitespace
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $type = $response->headers->get('Content-Type', '');
        if (! str_contains($type, 'text/html') || ! is_string($html = $response->getContent())) {
            return $response;
        }

        // Park the parts that must stay exactly as written, collapse, put them back.
        $kept = [];
        $html = preg_replace_callback('#<(pre|textarea|script)\b[^>]*>.*?</\1>#is', function ($m) use (&$kept) {
            $kept[] = $m[0];

            return "\x00".(count($kept) - 1)."\x00";
        }, $html);
        $html = preg_replace('/>[ \t]*\r?\n\s*</', '><', $html);
        $html = preg_replace_callback('/\x00(\d+)\x00/', fn ($m) => $kept[(int) $m[1]], $html);

        $response->setContent($html);

        return $response;
    }
}
