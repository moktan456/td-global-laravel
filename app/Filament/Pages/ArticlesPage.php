<?php

namespace App\Filament\Pages;

use App\Filament\Support\ContentPage;
use App\Filament\Support\Fields;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class ArticlesPage extends ContentPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static string|\UnitEnum|null $navigationGroup = 'Services & articles';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Blog articles';

    protected static ?string $title = 'Blog articles';

    protected static ?string $slug = 'articles';

    protected static ?string $previewUrl = '/blog';

    protected static ?string $intro = 'Write and edit the articles on the blog. The newest date is shown first.';

    protected static function files(): array
    {
        return ['articles' => null];
    }

    protected function toForm(array $state): array
    {
        return ['articles' => ['list' => $state['articles']]];
    }

    protected function fromForm(array $state): array
    {
        return ['articles' => $state['articles']['list'] ?? []];
    }

    protected function fields(): array
    {
        return [
            Repeater::make('articles.list')->label('Articles')->hiddenLabel()->collapsible()->collapsed()->reorderable()
                ->addActionLabel('Write a new article')
                ->itemLabel(fn (array $state) => $state['title'] ?? 'New article')
                ->schema([
                    Section::make('About the article')->columns(2)->schema([
                        Fields::text('title', 'Title', null, true)->columnSpanFull(),
                        Fields::text('slug', 'Web address ending', 'Letters, numbers and dashes. The article lives at /blog/this-ending. Leave it alone on published articles.', true)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/'),
                        Fields::text('category', 'Category', 'For example Documentation, Compliance or Planning.', true),
                        DatePicker::make('date')->label('Date')->native(false)->displayFormat('j F Y')->format('Y-m-d')->required(),
                        Fields::text('author', 'Author', 'A name in [square brackets] is not shown on the site.'),
                        Fields::photo('image', 'Cover photo')->columnSpanFull(),
                        Fields::area('excerpt', 'Short summary', 'Shown on the blog list and in search results.', 2)->required()->columnSpanFull(),
                    ]),
                    Section::make('The article')->description('Build it from sections. A section can have a heading, paragraphs and a bullet list.')->schema([
                        Repeater::make('body')->hiddenLabel()->reorderable()->collapsible()->addActionLabel('Add a section')
                            ->itemLabel(fn (array $state) => filled($state['heading'] ?? null) ? $state['heading'] : 'Section')
                            ->schema([
                                Fields::text('heading', 'Heading', 'Optional. Leave empty for an opening section.'),
                                Repeater::make('paragraphs')->label('Paragraphs')->simple(Fields::area('text', 'Paragraph', null, 4)->required())->addActionLabel('Add a paragraph')->reorderable(),
                                Repeater::make('list')->label('Bullet list (optional)')->simple(TextInput::make('text')->required()->maxLength(500))->addActionLabel('Add a bullet')->reorderable(),
                            ]),
                    ]),
                ]),
        ];
    }
}
