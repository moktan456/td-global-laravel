<?php

namespace App\Http\Controllers;

use App\Support\Content;

/** robots.txt and sitemap.xml (ported from src/app/robots.ts and sitemap.ts). */
class SeoController extends Controller
{
    public function robots()
    {
        // Staff pages and search results are private or low value; keep crawlers on the public pages.
        $body = "User-Agent: *\nAllow: /\nDisallow: /account\nDisallow: /staff\nDisallow: /search\n\nSitemap: ".absolute_url('/sitemap.xml')."\n";

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /**
     * Public, indexable pages only (not staff, search or photo credits). `lastmod` is left out because a wrong date is
     * worse than none; the article dates are still placeholders.
     */
    public function sitemap()
    {
        $urls = [
            ['/', 1], ['/clearing-agent-gelephu', 0.9], ['/services', 0.9], ['/contact', 0.8], ['/quote', 0.8],
            ['/pricing', 0.7], ['/about', 0.7], ['/calculator', 0.6], ['/blog', 0.6],
        ];
        foreach (Content::services() as $s) {
            $urls[] = ["/services/{$s['slug']}", 0.8];
        }
        $urls[] = ['/track', 0.5];
        foreach (Content::articles() as $a) {
            $urls[] = ["/blog/{$a['slug']}", 0.5];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as [$path, $priority]) {
            $xml .= '<url><loc>'.htmlspecialchars(absolute_url($path), ENT_XML1).'</loc><priority>'.$priority.'</priority></url>'."\n";
        }

        return response($xml.'</urlset>', 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
