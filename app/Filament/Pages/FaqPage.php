<?php

namespace App\Filament\Pages;

use App\Filament\Support\ContentPage;
use App\Filament\Support\Fields;
use Filament\Forms\Components\Repeater;

class FaqPage extends ContentPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static string|\UnitEnum|null $navigationGroup = 'Pages';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Questions & answers';

    protected static ?string $title = 'Questions & answers (FAQ)';

    protected static ?string $slug = 'faq';

    protected static ?string $previewUrl = '/clearing-agent-gelephu';

    protected static ?string $intro = 'Shown on the "Clearing agent in Gelephu" page and sent to Google as rich answers.';

    protected static function files(): array
    {
        return ['faq' => null];
    }

    protected function toForm(array $state): array
    {
        return ['faq' => ['list' => $state['faq']]];
    }

    protected function fromForm(array $state): array
    {
        return ['faq' => $state['faq']['list'] ?? []];
    }

    protected function fields(): array
    {
        return [
            Repeater::make('faq.list')->hiddenLabel()->reorderable()->collapsible()->addActionLabel('Add a question')
                ->itemLabel(fn (array $state) => $state['question'] ?? 'New question')
                ->schema([
                    Fields::text('question', 'Question', null, true),
                    Fields::area('answer', 'Answer', null, 4)->required(),
                ]),
        ];
    }
}
