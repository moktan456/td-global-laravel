<?php

namespace App\Filament\Pages;

use App\Filament\Support\ContentPage;
use App\Filament\Support\Fields;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;

class Menus extends ContentPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bars-3';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Menus & footer links';

    protected static ?string $title = 'Menus & footer links';

    protected static ?string $slug = 'menus';

    protected static ?string $previewUrl = '/';

    protected static ?string $intro = 'The links in the top menu and at the bottom of every page. Drag the handle to change the order.';

    protected static function files(): array
    {
        return ['site' => ['primaryNav', 'companyMenuLabel', 'companyNav', 'footerLinks', 'footerSecondary']];
    }

    private function links(string $name, string $label): Repeater
    {
        return Repeater::make($name)->label($label)->schema([
            Fields::text('label', 'Text shown', null, true),
            Fields::link(),
        ])->columns(2)->reorderable()->addActionLabel('Add a link')->itemLabel(fn (array $state) => $state['label'] ?? 'Link')->collapsible();
    }

    protected function fields(): array
    {
        return [
            Section::make('Top menu')->schema([
                $this->links('site.primaryNav', 'Main links'),
                Fields::text('site.companyMenuLabel', 'Name of the drop-down menu', null, true),
                $this->links('site.companyNav', 'Links inside the drop-down'),
            ]),
            Section::make('Footer')->schema([
                $this->links('site.footerLinks', 'First column'),
                $this->links('site.footerSecondary', 'Second column'),
            ]),
        ];
    }
}
