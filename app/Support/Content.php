<?php

namespace App\Support;

/**
 * Business facts and copy, kept in plain PHP arrays under resources/content (ported from the Next.js site's
 * src/content). Edit those files to change text, services, plans or articles; nothing here touches a database.
 */
class Content
{
    /** @var array<string, array> */
    private static array $cache = [];

    public static function load(string $name): array
    {
        return self::$cache[$name] ??= require resource_path("content/{$name}.php");
    }

    /** The `site` block, with the canonical origin filled in from the environment (SITE_URL, else APP_URL). */
    public static function site(): array
    {
        return self::$cache['site.resolved'] ??= array_merge(self::load('site')['site'], [
            'url' => rtrim((string) (env('SITE_URL') ?: config('app.url')), '/'),
        ]);
    }

    public static function services(): array
    {
        return self::load('services');
    }

    public static function service(string $slug): ?array
    {
        foreach (self::services() as $service) {
            if ($service['slug'] === $slug) {
                return $service;
            }
        }

        return null;
    }

    public static function articles(): array
    {
        return self::load('articles');
    }

    public static function article(string $slug): ?array
    {
        foreach (self::articles() as $article) {
            if ($article['slug'] === $slug) {
                return $article;
            }
        }

        return null;
    }
}
