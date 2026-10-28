<?php

namespace App\Filament\Pages;

use App\Filament\Support\ContentPage;
use App\Filament\Support\Fields;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class PricingPage extends ContentPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Pages';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Pricing & calculator';

    protected static ?string $title = 'Pricing packs & cost calculator';

    protected static ?string $slug = 'pricing';

    protected static ?string $previewUrl = '/pricing';

    protected static ?string $intro = 'The price packs on the Home and Pricing pages, and the numbers behind the cost calculator.';

    protected static function files(): array
    {
        return ['site' => ['plans'], 'rates' => null];
    }

    protected function fields(): array
    {
        $nu = fn (string $name, string $label, ?string $help = null) => TextInput::make($name)->label($label)->helperText($help)->numeric()->minValue(0)->required()->prefix('Nu.');

        return [
            Section::make('Price packs')->schema([
                Repeater::make('site.plans')->hiddenLabel()->reorderable()->collapsible()->addActionLabel('Add a pack')
                    ->itemLabel(fn (array $state) => $state['name'] ?? 'Pack')
                    ->columns(2)->schema([
                        Fields::text('name', 'Name of the pack', null, true),
                        Fields::text('price', 'Price as shown', 'Written exactly as customers see it, like Nu. 2,000.', true),
                        Repeater::make('features')->label('What it includes')->simple(TextInput::make('text')->required()->maxLength(255))->addActionLabel('Add a line')->reorderable()->columnSpanFull(),
                        Toggle::make('highlighted')->label('Show as "most popular"'),
                    ]),
            ]),
            Section::make('Cost calculator')->description('An indicative estimate for visitors. The final price is always confirmed by a broker.')->columns(3)->schema([
                $nu('rates.base.standard', 'Standard clearance'),
                $nu('rates.base.complex', 'Complex clearance'),
                TextInput::make('rates.includedLines')->label('Item lines included')->numeric()->minValue(0)->required(),
                $nu('rates.perExtraLine', 'Per extra item line'),
                $nu('rates.mode.air', 'Air freight add-on'),
                $nu('rates.mode.road', 'Road freight add-on'),
                $nu('rates.mode.rail', 'Rail freight add-on'),
                TextInput::make('rates.exportDiscount')->label('Export discount')->numeric()->minValue(0)->maxValue(1)->step(0.01)->required()
                    ->helperText('As a fraction: 0.1 means 10% off.'),
            ]),
        ];
    }
}
