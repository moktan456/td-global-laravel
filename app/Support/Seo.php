<?php

namespace App\Support;

/** Page metadata and schema.org JSON-LD (ported from the Next.js site's src/lib/seo.ts). */
class Seo
{
    public static function orgId(): string
    {
        return absolute_url('/#organization');
    }

    public static function siteId(): string
    {
        return absolute_url('/#website');
    }

    /** Home page title: leads with the search term, then the brand. */
    public static function defaultTitle(): string
    {
        return 'Clearing Agent in Gelephu, GMC | '.site('shortName').' Clearing & Logistics';
    }

    private static function planPriceRange(): ?string
    {
        $nums = [];
        foreach (site_plans() as $plan) {
            preg_match_all('/[\d,]+/', $plan['price'], $m);
            foreach ($m[0] as $n) {
                $nums[] = (int) str_replace(',', '', $n);
            }
        }
        if (! $nums) {
            return null;
        }
        $fmt = fn (int $n) => 'Nu. '.number_format($n);

        return $fmt(min($nums)).' - '.$fmt(max($nums));
    }

    /** The business itself: a local clearing agent. Referenced from every other node by @id. */
    public static function organization(): array
    {
        $contact = site('contact');
        $seo = site('seo');
        $legal = site('legal');
        $sameAs = array_values(array_filter(site('social'), fn ($u) => is_filled($u) && preg_match('#^https?://#i', trim($u))));

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'ProfessionalService',
            '@id' => self::orgId(),
            'name' => site('name'),
            'legalName' => $legal['name'],
            'alternateName' => $seo['alternateNames'],
            'url' => absolute_url('/'),
            'logo' => absolute_url('/brand/logo.png'),
            'image' => absolute_url(image_src('heroRoad')),
            'description' => site('description'),
            'slogan' => site('tagline'),
            'email' => $contact['email'],
        ];
        if (is_filled($contact['phoneHref'])) {
            $data['telephone'] = $contact['phoneHref'];
        }
        $data += [
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $seo['area'],
                'addressLocality' => $seo['locality'],
                'addressRegion' => $seo['region'],
                'addressCountry' => $seo['country'],
            ],
            'geo' => ['@type' => 'GeoCoordinates', 'latitude' => $seo['geo']['lat'], 'longitude' => $seo['geo']['lng']],
            'hasMap' => $contact['mapUrl'],
            'areaServed' => [
                ['@type' => 'City', 'name' => $seo['locality']],
                ['@type' => 'AdministrativeArea', 'name' => $seo['region']],
                ['@type' => 'Country', 'name' => 'Bhutan'],
            ],
            'knowsAbout' => array_column(Content::services(), 'title'),
            'priceRange' => self::planPriceRange(),
            'foundingDate' => $legal['incorporated'],
            'identifier' => ['@type' => 'PropertyValue', 'propertyID' => 'UEN', 'value' => $legal['uen']],
        ];
        if ($sameAs) {
            $data['sameAs'] = $sameAs;
        }

        return $data;
    }

    public static function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => self::siteId(),
            'name' => site('name'),
            'alternateName' => site('seo.alternateNames'),
            'url' => absolute_url('/'),
            'inLanguage' => 'en',
            'publisher' => ['@id' => self::orgId()],
        ];
    }

    /** `$trail` excludes Home, which is always first. Each item: ['name' => ..., 'path' => ...]. */
    public static function breadcrumbs(array $trail): array
    {
        $items = array_merge([['name' => 'Home', 'path' => '/']], $trail);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn ($it, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $it['name'],
                'item' => absolute_url($it['path']),
            ], $items, array_keys($items)),
        ];
    }

    public static function service(array $s): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $s['title'],
            'serviceType' => $s['title'],
            'description' => implode(' ', array_merge([$s['summary']], $s['body'])),
            'provider' => ['@id' => self::orgId()],
            'areaServed' => [
                ['@type' => 'City', 'name' => site('seo.locality')],
                ['@type' => 'AdministrativeArea', 'name' => site('seo.region')],
            ],
            'url' => absolute_url("/services/{$s['slug']}"),
        ];
    }

    /**
     * Publish dates are left out on purpose: the article dates are placeholders, and a wrong date in structured
     * data is worse than none.
     */
    public static function article(array $a): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $a['title'],
            'description' => $a['excerpt'],
            'image' => absolute_url(image_src($a['image'])),
            'mainEntityOfPage' => absolute_url("/blog/{$a['slug']}"),
            'author' => is_filled($a['author']) ? ['@type' => 'Person', 'name' => $a['author']] : ['@id' => self::orgId()],
            'publisher' => ['@id' => self::orgId()],
        ];
    }

    /** @param  array<int, array{question: string, answer: string}>  $faqs */
    public static function faq(array $faqs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['answer']],
            ], $faqs),
        ];
    }
}
