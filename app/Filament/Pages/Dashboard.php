<?php

namespace App\Filament\Pages;

use App\Filament\Support\ContentPage;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

/** The admin home: a short guide and a button for every part of the website that can be edited. */
class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Start here';

    protected static ?string $title = 'Edit your website';

    protected string $view = 'filament.dashboard';

    /** @return array<string, array<int, array{label: string, hint: string, url: string, icon: string}>> */
    public function sections(): array
    {
        $cards = [];
        foreach (Filament::getPanel('admin')->getPages() as $page) {
            if (! is_subclass_of($page, ContentPage::class)) {
                continue;
            }
            $group = (string) $page::getNavigationGroup();
            $cards[$group][] = [
                'sort' => $page::getNavigationSort() ?? 99,
                'label' => $page::getNavigationLabel(),
                'hint' => (string) (new \ReflectionClass($page))->getStaticPropertyValue('intro'),
                'url' => $page::getUrl(),
                'icon' => (string) $page::getNavigationIcon(),
            ];
        }
        $order = ['Pages', 'Services & articles', 'Settings'];
        $out = [];
        foreach ($order as $group) {
            if (isset($cards[$group])) {
                usort($cards[$group], fn ($a, $b) => $a['sort'] <=> $b['sort']);
                $out[$group] = $cards[$group];
            }
        }

        return $out;
    }
}
