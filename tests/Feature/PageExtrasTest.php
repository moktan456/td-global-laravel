<?php

namespace Tests\Feature;

use App\Models\ContentOverride;
use App\Support\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageExtrasTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_shows_when_it_was_last_updated_falling_back_to_its_date(): void
    {
        $slug = 'invoice-and-packing-list-must-match';
        $article = collect(Content::load('articles'))->firstWhere('slug', $slug);

        $this->get("/blog/$slug")->assertOk()->assertSee('Last updated:')->assertSee(format_date($article['date']));

        $articles = collect(Content::load('articles'))->map(fn ($a) => $a['slug'] === $slug ? [...$a, 'updated' => '2026-10-01'] : $a)->all();
        ContentOverride::create(['name' => 'articles', 'data' => $articles]);
        Content::flush();

        $this->get("/blog/$slug")->assertOk()->assertSee('October 1, 2026');
    }

    public function test_progress_bar_and_back_to_top_are_on_public_pages(): void
    {
        $this->get('/')->assertOk()->assertSee('data-scroll-progress', false)->assertSee('data-back-to-top', false)->assertSee('Back to top');
    }
}
