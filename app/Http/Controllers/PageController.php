<?php

namespace App\Http\Controllers;

use App\Support\Content;
use App\Support\SearchIndex;
use Illuminate\Http\Request;

/** Public marketing pages (no closures in the routes, so `route:cache` stays safe on the server). */
class PageController extends Controller
{
    public function service(string $slug)
    {
        $service = Content::service($slug);
        abort_if($service === null, 404);

        return view('services.show', ['service' => $service]);
    }

    public function article(string $slug)
    {
        $article = Content::article($slug) ?? (auth()->user()?->isAdmin() ? Content::draft($slug) : null);
        abort_if($article === null, 404);

        return view('blog.show', ['article' => $article]);
    }

    public function search(Request $request)
    {
        $q = $request->query('q');
        $query = mb_substr(trim(is_array($q) ? (string) ($q[0] ?? '') : (string) $q), 0, 100);

        return view('pages.search', ['query' => $query, 'results' => $query !== '' ? SearchIndex::search($query) : []]);
    }

    public function quote(Request $request)
    {
        $plan = $request->query('plan');
        $selected = collect(content('site.plans'))->firstWhere('name', $plan)['name'] ?? null;

        return view('pages.quote', ['selected' => $selected]);
    }
}
