<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

/** The guide to editing the website, inside the admin so it is always at hand and always matches this version. */
class UserGuide extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string|\UnitEnum|null $navigationGroup = 'Help';

    protected static ?string $navigationLabel = 'User guide';

    protected static ?string $title = 'Website and tracking guide';

    protected static ?string $slug = 'guide';

    protected string $view = 'filament.guide';

    public function getSubheading(): ?string
    {
        return 'How to edit the website content, and how the shipment tracking system works.';
    }
}
