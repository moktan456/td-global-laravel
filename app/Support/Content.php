<?php

namespace App\Support;

use App\Models\ContentOverride;

/**
 * Business facts and copy. The defaults are plain PHP arrays under resources/content (ported from the Next.js site's
 * src/content); anything edited in the admin panel is saved in `content_overrides` and laid over them, so a site with
 * no saved edits (or no database yet) reads exactly the files.
 */
class Content
{
    /** @var array<string, array> */
    private static array $cache = [];

    public static function load(string $name): array
    {
        if (isset(self::$cache[$name])) {
            return self::$cache[$name];
        }
        $saved = self::saved();

        return self::$cache[$name] = array_key_exists($name, $saved) ? self::merge(self::defaults($name), $saved[$name]) : self::defaults($name);
    }

    /** The file as it ships, ignoring any admin edits. */
    public static function defaults(string $name): array
    {
        return require resource_path("content/{$name}.php");
    }

    /** Forget what was read, so the next read sees fresh edits (called after every save). */
    public static function flush(): void
    {
        self::$cache = [];
    }

    /**
     * Keys of a record are merged one by one (so a field added to the files later still shows up); lists are replaced
     * as a whole (so an item deleted in the admin stays deleted).
     */
    public static function merge(array $defaults, array $saved): array
    {
        if (array_is_list($defaults)) {
            return $saved; // a list the admin emptied stays empty
        }
        if ($saved === []) {
            return $defaults;
        }
        foreach ($saved as $key => $value) {
            $defaults[$key] = is_array($value) && isset($defaults[$key]) && is_array($defaults[$key])
                ? self::merge($defaults[$key], $value)
                : $value;
        }

        return $defaults;
    }

    /** @return array<string, array> every saved edit, by file name; empty when there are none or no table yet */
    private static function saved(): array
    {
        if (isset(self::$cache['_saved'])) {
            return self::$cache['_saved'];
        }
        try {
            $rows = ContentOverride::query()->get()->mapWithKeys(fn ($row) => [$row->name => (array) $row->data])->all();
        } catch (\Throwable) {
            $rows = []; // not migrated yet, or the database is down: the files are always a safe answer
        }

        return self::$cache['_saved'] = $rows;
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

    /** The articles visitors can read: drafts (`published` switched off in the admin) are left out. */
    public static function articles(): array
    {
        return array_values(array_filter(self::load('articles'), fn ($a) => $a['published'] ?? true));
    }

    /** A draft article by its address, for admins previewing it. */
    public static function draft(string $slug): ?array
    {
        foreach (self::load('articles') as $article) {
            if ($article['slug'] === $slug && ! ($article['published'] ?? true)) {
                return $article;
            }
        }

        return null;
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
