<?php

namespace App\Filament\Pages;

use App\Filament\Support\ContentPage;
use App\Filament\Support\Fields;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class HomePage extends ContentPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static string|\UnitEnum|null $navigationGroup = 'Pages';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Home page';

    protected static ?string $title = 'Home page';

    protected static ?string $slug = 'home';

    protected static ?string $previewUrl = '/';

    protected static ?string $intro = 'The banner, the headings of each section, and the lists shown on the home page. Some of these also appear on the About page.';

    protected static function files(): array
    {
        return ['home' => null, 'site' => ['whyUs', 'docTips', 'team', 'testimonials', 'partners', 'trustBadges']];
    }

    private function heading(string $key, string $label, bool $eyebrow = true, bool $intro = true): Section
    {
        $fields = [];
        if ($eyebrow) {
            $fields[] = Fields::text("home.sections.$key.eyebrow", 'Small label above the heading');
        }
        $fields[] = Fields::text("home.sections.$key.title", 'Heading', null, true)->columnSpanFull();
        if ($intro) {
            $fields[] = Fields::area("home.sections.$key.intro", 'Text under the heading', null, 3)->columnSpanFull();
        }

        return Section::make($label)->columns(2)->collapsible()->collapsed()->schema($fields);
    }

    protected function fields(): array
    {
        $placeholder = 'Text in [square brackets] is treated as a placeholder and stays hidden on the site until you replace it.';

        return [
            Tabs::make('Home')->persistTabInQueryString()->tabs([
                Tab::make('Banner')->icon('heroicon-o-photo')->schema([
                    Repeater::make('home.slides')->hiddenLabel()->reorderable()->collapsible()->addActionLabel('Add a slide')->minItems(1)->maxItems(5)
                        ->itemLabel(fn (array $state) => $state['title'] ?? 'Slide')->columns(2)->schema([
                            Fields::text('eyebrow', 'Small label', null, true),
                            Fields::text('title', 'Big headline', null, true),
                            Fields::area('body', 'Text', null, 3)->required()->columnSpanFull(),
                            Fields::text('cta.label', 'Button text', null, true),
                            Fields::link('cta.href', 'Button links to'),
                            Fields::photo('photos.0', 'Large photo'),
                            Fields::photo('photos.1', 'Top small photo'),
                            Fields::photo('photos.2', 'Bottom small photo'),
                        ]),
                ]),
                Tab::make('Section headings')->icon('heroicon-o-bars-3-bottom-left')->schema([
                    $this->heading('about', 'About us'),
                    $this->heading('services', 'Services'),
                    $this->heading('why', 'Why choose us'),
                    $this->heading('tips', 'Document tips'),
                    $this->heading('team', 'Team', true, false),
                    $this->heading('partners', 'Partners', false, false),
                    $this->heading('calculator', 'Cost calculator'),
                    $this->heading('pricing', 'Pricing', true, false),
                    $this->heading('testimonials', 'Testimonials', false, false),
                    $this->heading('trust', 'Closing banner', false, false),
                    $this->heading('blog', 'Blog', true, false),
                ]),
                Tab::make('Why choose us')->icon('heroicon-o-star')->schema([
                    Repeater::make('site.whyUs')->hiddenLabel()->reorderable()->collapsible()->addActionLabel('Add a reason')->columns(2)
                        ->itemLabel(fn (array $state) => $state['title'] ?? 'Reason')->schema([
                            Fields::text('title', 'Title', null, true),
                            Select::make('icon')->label('Icon')->options(Fields::icons(['expertise', 'regulatory', 'tracking']))->required(),
                            Fields::area('body', 'Text', null, 2)->required()->columnSpanFull(),
                        ]),
                    Repeater::make('site.docTips')->label('Document tips')->simple(Fields::area('text', 'Tip', null, 2)->required())->addActionLabel('Add a tip')->reorderable(),
                ]),
                Tab::make('Team')->icon('heroicon-o-users')->schema([
                    Repeater::make('site.team')->hiddenLabel()->reorderable()->collapsible()->addActionLabel('Add a team member')->columns(3)
                        ->helperText($placeholder)->itemLabel(fn (array $state) => $state['name'] ?? 'Team member')->schema([
                            Fields::text('name', 'Name', null, true),
                            Fields::text('role', 'Role', null, true),
                            Fields::photo('image', 'Photo'),
                        ]),
                ]),
                Tab::make('Testimonials')->icon('heroicon-o-chat-bubble-left-right')->schema([
                    Repeater::make('site.testimonials')->hiddenLabel()->reorderable()->collapsible()->addActionLabel('Add a testimonial')->columns(2)
                        ->helperText($placeholder.' Add a real quote only with the client\'s permission.')
                        ->itemLabel(fn (array $state) => $state['name'] ?? 'Testimonial')->schema([
                            Fields::text('name', 'Client name', null, true),
                            Fields::text('company', 'Company', null, true),
                            Fields::area('quote', 'What they said', null, 3)->required()->columnSpanFull(),
                        ]),
                ]),
                Tab::make('Partners & badges')->icon('heroicon-o-shield-check')->schema([
                    Repeater::make('site.partners')->label('Partner names')->simple(TextInput::make('text')->required()->maxLength(120))
                        ->addActionLabel('Add a partner')->reorderable()->helperText($placeholder),
                    Repeater::make('site.trustBadges')->label('Trust badges')->reorderable()->addActionLabel('Add a badge')->columns(2)
                        ->itemLabel(fn (array $state) => $state['label'] ?? 'Badge')->schema([
                            Fields::text('label', 'Text', null, true),
                            Select::make('icon')->label('Icon')->options(Fields::icons(['badge', 'clock', 'radar', 'headset', 'shield', 'award', 'lock', 'savings']))->required(),
                        ]),
                ]),
            ]),
        ];
    }
}
