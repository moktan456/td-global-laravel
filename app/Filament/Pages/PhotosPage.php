<?php

namespace App\Filament\Pages;

use App\Filament\Support\ContentPage;
use App\Filament\Support\Fields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;

class PhotosPage extends ContentPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Photos';

    protected static ?string $title = 'Photos';

    protected static ?string $slug = 'photos';

    protected static ?string $previewUrl = '/';

    protected static ?string $intro = 'Every photo slot on the website. Upload your own picture to replace one, and describe it for people who cannot see it.';

    protected static function files(): array
    {
        return ['images' => null];
    }

    protected function toForm(array $state): array
    {
        $list = [];
        foreach ($state['images'] as $key => $img) {
            $list[] = ['key' => $key, 'alt' => $img['alt'] ?? '', 'local' => $img['local'] ?? null, 'upload' => $img['upload'] ?? null];
        }

        return ['images' => ['list' => $list]];
    }

    protected function fromForm(array $state): array
    {
        $out = [];
        foreach ($state['images']['list'] ?? [] as $row) {
            $out[$row['key']] = ['alt' => $row['alt'] ?? '', 'local' => $row['local'] ?? '', 'upload' => $row['upload'] ?? ''];
        }

        return ['images' => $out];
    }

    /** @return array<string, string> the photos that come with the website */
    private function library(): array
    {
        $names = [];
        foreach (glob(public_path('photos/*.jpg')) ?: [] as $file) {
            $name = basename($file, '.jpg');
            $names[$name] = str_replace('-', ' ', $name);
        }

        return $names;
    }

    protected function fields(): array
    {
        $labels = [
            'heroRoad' => 'Home banner: photo 1', 'heroAir' => 'Home banner: photo 2', 'heroDock' => 'Home banner: photo 3',
            'aboutTruck' => 'About us: first photo', 'aboutMountainRoad' => 'About us: second photo',
            'svcImport' => 'Import clearance', 'svcExport' => 'Export clearance', 'svcDocs' => 'Documents', 'svcTariff' => 'Tariff',
            'docsReview' => 'Document tips', 'team1' => 'Team member 1', 'team2' => 'Team member 2', 'team3' => 'Team member 3', 'team4' => 'Team member 4',
        ];

        return [
            Repeater::make('images.list')->hiddenLabel()->addable(false)->deletable(false)->reorderable(false)->collapsible()->collapsed()
                ->itemLabel(fn (array $state) => $labels[$state['key'] ?? ''] ?? ($state['key'] ?? 'Photo'))
                ->schema([
                    Hidden::make('key'),
                    Section::make()->columns(2)->schema([
                        Select::make('local')->label('Photo from the website library')->options($this->library())->searchable()->placeholder('None')
                            ->helperText('Used when you have not uploaded your own.'),
                        FileUpload::make('upload')->label('Your own photo')->image()->disk('public')->directory('uploads')->visibility('public')
                            ->maxSize(8192)->imageResizeMode('contain')->imageResizeTargetWidth(2000)->imageResizeTargetHeight(2000)
                            ->helperText('JPG, PNG or WebP, up to 8 MB. It replaces the library photo. Remove it to go back.'),
                        Fields::area('alt', 'Describe the photo', 'Read aloud to people using a screen reader, and shown if the photo cannot load. Say what is in the picture.', 2)->required()->columnSpanFull(),
                    ]),
                ]),
        ];
    }
}
