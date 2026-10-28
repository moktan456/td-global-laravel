<?php

namespace App\Filament\Pages;

use App\Filament\Support\ContentPage;
use App\Filament\Support\Fields;
use Filament\Forms\Components\Repeater;

class ProcessPage extends ContentPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    protected static string|\UnitEnum|null $navigationGroup = 'Pages';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'How it works (steps)';

    protected static ?string $title = 'How a clearance works';

    protected static ?string $slug = 'process';

    protected static ?string $previewUrl = '/services';

    protected static ?string $intro = 'The numbered steps shown on the Services page.';

    protected static function files(): array
    {
        return ['process' => null];
    }

    protected function toForm(array $state): array
    {
        return ['process' => ['list' => $state['process']]];
    }

    protected function fromForm(array $state): array
    {
        return ['process' => $state['process']['list'] ?? []];
    }

    protected function fields(): array
    {
        return [
            Repeater::make('process.list')->hiddenLabel()->reorderable()->collapsible()->addActionLabel('Add a step')
                ->itemLabel(fn (array $state) => $state['title'] ?? 'Step')
                ->schema([
                    Fields::text('title', 'Step title', null, true),
                    Fields::area('body', 'What happens', null, 3)->required(),
                ]),
        ];
    }
}
