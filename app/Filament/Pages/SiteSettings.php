<?php

namespace App\Filament\Pages;

use App\Filament\Support\ContentPage;
use App\Filament\Support\Fields;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class SiteSettings extends ContentPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Company & contact';

    protected static ?string $title = 'Company & contact details';

    protected static ?string $slug = 'company';

    protected static ?string $previewUrl = '/contact';

    protected static ?string $intro = 'The name, phone, email, address and registration details used across the whole website.';

    protected static function files(): array
    {
        return ['site' => ['site']];
    }

    protected function fields(): array
    {
        return [
            Tabs::make('Settings')->persistTabInQueryString()->tabs([
                Tab::make('Company')->icon('heroicon-o-building-office-2')->schema([
                    Section::make('Names')->columns(2)->schema([
                        Fields::text('site.site.name', 'Full company name', null, true),
                        Fields::text('site.site.shortName', 'Short name', 'Used in the header and in page titles.', true),
                        Fields::text('site.site.tagline', 'Tagline', 'The line under the name, e.g. in the top bar.', true)->columnSpanFull(),
                        Fields::area('site.site.description', 'Short description', 'Shown in search results and when the site is shared. About one or two sentences.')->columnSpanFull(),
                    ]),
                    Section::make('Registration')->columns(2)->schema([
                        Fields::text('site.site.legal.name', 'Registered name', null, true),
                        Fields::text('site.site.legal.uen', 'UEN (registration number)', null, true),
                        TextInput::make('site.site.legal.incorporated')->label('Date of incorporation')->type('date')->required(),
                        Fields::text('site.site.legal.jurisdiction', 'Jurisdiction', null, true),
                        Fields::text('site.site.legal.registry', 'Registry that issued it', null, true)->columnSpanFull(),
                    ]),
                ]),
                Tab::make('Contact')->icon('heroicon-o-phone')->schema([
                    Section::make('How people reach you')->columns(2)->schema([
                        Fields::text('site.site.contact.phone', 'Phone (as shown)', 'For example +975 17975757.', true),
                        Fields::text('site.site.contact.phoneHref', 'Phone (for tapping)', 'Digits with the country code and a +, no spaces: +97517975757.'),
                        Fields::text('site.site.contact.whatsapp', 'WhatsApp number', 'Digits only, with the country code: 97517975757. Leave empty to hide WhatsApp.'),
                        TextInput::make('site.site.contact.email')->label('Email')->email()->required(),
                        Fields::text('site.site.contact.address', 'Address', null, true)->columnSpanFull(),
                        Fields::text('site.site.contact.hours', 'Opening hours', 'Shown as written. While it is in [square brackets] it stays hidden on the site.')->columnSpanFull(),
                    ]),
                    Section::make('Map')->columns(2)->schema([
                        Fields::text('site.site.contact.mapQuery', 'Map position', 'Latitude and longitude, like 26.88,90.49.'),
                        TextInput::make('site.site.contact.mapUrl')->label('Google Maps link')->url()->maxLength(1000),
                    ]),
                ]),
                Tab::make('Search & social')->icon('heroicon-o-magnifying-glass')->schema([
                    Section::make('Where you are')->description('Helps Google show the business for local searches.')->columns(2)->schema([
                        Fields::text('site.site.seo.locality', 'Town'),
                        Fields::text('site.site.seo.area', 'Area'),
                        Fields::text('site.site.seo.region', 'Region'),
                        Fields::text('site.site.seo.country', 'Country code', 'Two letters, like BT.'),
                        TextInput::make('site.site.seo.geo.lat')->label('Latitude')->numeric(),
                        TextInput::make('site.site.seo.geo.lng')->label('Longitude')->numeric(),
                    ]),
                    Section::make('Search words')->schema([
                        TagsInput::make('site.site.seo.alternateNames')->label('Other names people know you by')->placeholder('Type a name and press Enter'),
                        TagsInput::make('site.site.seo.keywords')->label('Search phrases')->placeholder('Type a phrase and press Enter')
                            ->helperText('Phrases your customers might search for.'),
                    ]),
                    Section::make('Social media')->description('Leave empty to hide an icon.')->columns(3)->schema([
                        TextInput::make('site.site.social.facebook')->label('Facebook')->url(),
                        TextInput::make('site.site.social.x')->label('X (Twitter)')->url(),
                        TextInput::make('site.site.social.linkedin')->label('LinkedIn')->url(),
                    ]),
                    Section::make('Website credit')->columns(2)->collapsed()->schema([
                        Fields::text('site.site.designer.name', 'Built by'),
                        TextInput::make('site.site.designer.url')->label('Link')->url(),
                    ]),
                ]),
            ]),
        ];
    }
}
