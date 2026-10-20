<?php

namespace App\Support;

/** Site search over pages, services and articles (ported from src/lib/search.ts). */
class SearchIndex
{
    private static function entries(): array
    {
        $pages = [
            ['title' => 'Clearing agent in Gelephu & GMC', 'href' => '/clearing-agent-gelephu', 'description' => 'Custom clearing and logistics from our office in Rabdeyling, GMC.', 'text' => 'clearing agent gelephu gmc custom clearing logistics partner faq'],
            ['title' => 'Services', 'href' => '/services', 'description' => 'Import and export clearance, documentation and tariff classification.', 'text' => ''],
            ['title' => 'Track shipment', 'href' => '/track', 'description' => 'Check the status of your clearance.', 'text' => 'tracking status reference'],
            ['title' => 'Pricing', 'href' => '/pricing', 'description' => 'Clearance pricing plans.', 'text' => 'price cost plans packs'],
            ['title' => 'Cost calculator', 'href' => '/calculator', 'description' => 'Estimate your clearance fee.', 'text' => 'estimate price cost fee'],
            ['title' => 'Request a quote', 'href' => '/quote', 'description' => 'Get a free quote for your shipment.', 'text' => 'quote price'],
            ['title' => 'About us', 'href' => '/about', 'description' => 'Who we are and why clients choose us.', 'text' => 'team partners experience'],
            ['title' => 'Contact us', 'href' => '/contact', 'description' => 'Talk to a customs broker.', 'text' => 'email phone address office message'],
            ['title' => 'Blog & articles', 'href' => '/blog', 'description' => 'Guides and news.', 'text' => 'articles guides'],
        ];

        $services = array_map(fn ($s) => [
            'title' => $s['title'],
            'href' => "/services/{$s['slug']}",
            'description' => $s['summary'],
            'text' => implode(' ', array_merge($s['body'], $s['includes'])),
        ], Content::services());

        $articles = array_map(fn ($a) => [
            'title' => $a['title'],
            'href' => "/blog/{$a['slug']}",
            'description' => $a['excerpt'],
            'text' => implode(' ', array_merge(...array_map(fn ($b) => array_merge([$b['heading'] ?? ''], $b['paragraphs'], $b['list'] ?? []), $a['body']))),
        ], Content::articles());

        return array_merge($pages, $services, $articles);
    }

    /** @return array<int, array{title: string, href: string, description: string, score: int}> best match first */
    public static function search(string $query): array
    {
        $terms = array_values(array_filter(preg_split('/\s+/', mb_strtolower($query))));
        $results = [];
        foreach (self::entries() as $e) {
            $title = mb_strtolower($e['title']);
            $hay = mb_strtolower("{$title} {$e['description']} {$e['text']}");
            foreach ($terms as $t) {
                if (! str_contains($hay, $t)) {
                    continue 2;
                }
            }
            $score = array_sum(array_map(fn ($t) => str_contains($title, $t) ? 3 : 1, $terms));
            $results[] = $e + ['score' => $score];
        }
        usort($results, fn ($a, $b) => $b['score'] <=> $a['score']);

        return $results;
    }
}
