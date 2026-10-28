<?php

namespace App\Filament\Pages;

use App\Filament\Support\ContentPage;
use App\Filament\Support\Fields;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class ServicesPage extends ContentPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'Services & articles';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Services';

    protected static ?string $title = 'Services';

    protected static ?string $slug = 'services';

    protected static ?string $previewUrl = '/services';

    protected static ?string $intro = 'Each service has its own page on the website. Open one to edit it, add a new one at the bottom, or drag to reorder.';

    protected static function files(): array
    {
        return ['services' => null];
    }

    protected function toForm(array $state): array
    {
        return ['services' => ['list' => $state['services']]];
    }

    protected function fromForm(array $state): array
    {
        return ['services' => $state['services']['list'] ?? []];
    }

    protected function fields(): array
    {
        return [
            Repeater::make('services.list')->label('Services')->hiddenLabel()->reorderable()->collapsible()->collapsed()->cloneable()
                ->addActionLabel('Add a service')
                ->itemLabel(fn (array $state) => $state['title'] ?? 'New service')
                ->schema([
                    Section::make('The page')->columns(2)->schema([
                        Fields::text('title', 'Name of the service', null, true),
                        Fields::text('slug', 'Web address ending', 'Letters, numbers and dashes. The page lives at /services/this-ending. Changing it breaks old links, so leave it for existing services.', true)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->live(onBlur: true),
                        Fields::area('summary', 'One-line summary', 'Shown on the services list and the home page cards.', 2)->columnSpanFull(),
                        Fields::photo('image'),
                        Select::make('icon')->label('Icon')->options(Fields::icons(['import', 'export', 'forwarding', 'transport', 'storage', 'documents', 'tariff', 'food', 'transit']))->required(),
                    ]),
                    Section::make('Main text')->schema([
                        Repeater::make('body')->label('Paragraphs')->simple(Fields::area('text', 'Paragraph', null, 3)->required())->addActionLabel('Add a paragraph')->reorderable(),
                        Repeater::make('includes')->label('What is included')->simple(TextInput::make('text')->required()->maxLength(255))->addActionLabel('Add an item')->reorderable(),
                    ]),
                    Section::make('Common problems we solve')->collapsed()->schema([
                        Repeater::make('problems')->label('Problems')->hiddenLabel()->columns(2)->schema([
                            Fields::area('problem', 'The problem', null, 2)->required(),
                            Fields::area('fix', 'How we fix it', null, 2)->required(),
                        ])->addActionLabel('Add a problem')->reorderable()->itemLabel(fn (array $state) => $state['problem'] ?? 'Problem'),
                    ]),
                    Section::make('Google search listing')->description('How this page appears in search results.')->collapsed()->columns(1)->schema([
                        Fields::text('pageTitle', 'Heading at the top of the page', null, true),
                        Fields::text('metaTitle', 'Search result title', 'Aim for under 60 characters.', true),
                        Fields::area('metaDescription', 'Search result description', 'Aim for under 160 characters.', 2)->required(),
                    ]),
                ]),
        ];
    }
}
