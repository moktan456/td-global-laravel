<?php

namespace App\Filament\Pages;

use App\Filament\Support\ContentPage;
use App\Filament\Support\Fields;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class PageText extends ContentPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Pages';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Page text';

    protected static ?string $title = 'Page text';

    protected static ?string $slug = 'page-text';

    protected static ?string $previewUrl = '/about';

    protected static ?string $intro = 'The headings, introductions and search-result text of the About, Services, Pricing, Contact, Quote and Blog pages, plus the footer.';

    protected static function files(): array
    {
        return ['pages' => null];
    }

    /** The heading and intro at the top of a page, and how it appears in Google. */
    private function top(string $page, string $titleHelp = 'The big heading at the top of the page.', ?string $tokens = null): array
    {
        return [
            Section::make('Top of the page')->columns(1)->schema([
                Fields::text("pages.$page.title", 'Heading', $titleHelp, true),
                Fields::area("pages.$page.intro", 'Text under the heading', null, 2),
            ]),
            Section::make('Google search listing')->description('How the page appears in search results and when shared.'.($tokens ? ' '.$tokens : ''))->collapsed()->schema([
                Fields::text("pages.$page.metaTitle", 'Search result title', 'Aim for under 60 characters. The company name is added after it.', true),
                Fields::area("pages.$page.metaDescription", 'Search result description', 'Aim for under 160 characters.', 2)->required(),
            ]),
        ];
    }

    private function field(string $path, string $label, bool $long = false, ?string $help = null)
    {
        return $long ? Fields::area($path, $label, $help, 3) : Fields::text($path, $label, $help);
    }

    protected function fields(): array
    {
        $tokens = 'Words in {curly brackets}, like {name}, are filled in by the website.';

        return [
            Tabs::make('Pages')->persistTabInQueryString()->tabs([
                Tab::make('About')->icon('heroicon-o-information-circle')->schema([
                    ...$this->top('about', tokens: 'You can use {name}, {jurisdiction} and {uen}.'),
                    Section::make('Certificates')->description('The registration section. {name}, {date}, {registry} and {uen} are filled in from the Company details.')->columns(1)->schema([
                        Fields::text('pages.certificates.eyebrow', 'Small label'),
                        Fields::text('pages.certificates.title', 'Heading', null, true),
                        Fields::area('pages.certificates.intro', 'Text', null, 3),
                        Fields::text('pages.certificates.footnote', 'Line under the certificates'),
                    ]),
                ]),
                Tab::make('Services')->icon('heroicon-o-truck')->schema($this->top('services')),
                Tab::make('Pricing')->icon('heroicon-o-banknotes')->schema($this->top('pricing', tokens: 'Use {price} for the first pack\'s price.')),
                Tab::make('Calculator')->icon('heroicon-o-calculator')->schema($this->top('calculator')),
                Tab::make('Contact')->icon('heroicon-o-phone')->schema([
                    ...$this->top('contact', tokens: 'You can use {phone}, {email}, {shortName} and {address}.'),
                    Section::make('Contact box')->description('Used on the Contact page and at the bottom of the Home page.')->columns(1)->schema([
                        Fields::text('pages.contactPanel.eyebrow', 'Small label'),
                        Fields::text('pages.contactPanel.title', 'Heading (Home page)'),
                        Fields::area('pages.contactPanel.intro', 'Text (Home page)', null, 2),
                        Fields::text('pages.contactPanel.formTitle', 'Title above the form', null, true),
                        Fields::text('pages.contactPanel.formNote', 'Note under that title'),
                    ]),
                ]),
                Tab::make('Quote')->icon('heroicon-o-document-check')->schema([
                    ...$this->top('quote'),
                    Section::make('Beside the form')->columns(1)->schema([
                        Fields::text('pages.quote.nextTitle', 'Title of the steps box', null, true),
                        Repeater::make('pages.quote.steps')->label('Steps')->reorderable()->addActionLabel('Add a step')->columns(2)
                            ->itemLabel(fn (array $state) => $state['title'] ?? 'Step')->schema([
                                Fields::text('title', 'Step title', null, true),
                                Fields::area('body', 'What happens', null, 2)->required(),
                            ]),
                        Fields::text('pages.quote.talkTitle', 'Title of the "talk to us" box', null, true),
                        Fields::area('pages.quote.talkText', 'Text in that box', null, 2),
                    ]),
                ]),
                Tab::make('Blog')->icon('heroicon-o-newspaper')->schema([
                    ...$this->top('blog'),
                    Section::make('Box at the end of every article')->columns(1)->schema([
                        Fields::text('pages.blog.ctaTitle', 'Heading', null, true),
                        Fields::area('pages.blog.ctaText', 'Text', null, 2),
                        Fields::text('pages.blog.ctaButton', 'Button text', null, true),
                    ]),
                ]),
                Tab::make('Clearing agent page')->icon('heroicon-o-map-pin')->schema([
                    ...$this->top('agent', tokens: 'You can use {name}, {area} and {region}.'),
                    Section::make('Introduction')->columns(1)->schema([
                        Fields::text('pages.agent.introTitle', 'Heading', null, true),
                        Repeater::make('pages.agent.introParagraphs')->label('Paragraphs')->simple(Fields::area('text', 'Paragraph', null, 4)->required())->addActionLabel('Add a paragraph')->reorderable(),
                    ]),
                    Section::make('Section headings')->collapsed()->columns(2)->schema([
                        Fields::text('pages.agent.servicesEyebrow', 'Services: small label'),
                        Fields::text('pages.agent.servicesTitle', 'Services: heading', null, true),
                        Fields::area('pages.agent.servicesIntro', 'Services: text', null, 2)->columnSpanFull(),
                        Fields::text('pages.agent.stepsEyebrow', 'Steps: small label'),
                        Fields::text('pages.agent.stepsTitle', 'Steps: heading', null, true),
                        Fields::area('pages.agent.stepsIntro', 'Steps: text', null, 2)->columnSpanFull(),
                        Fields::text('pages.agent.faqEyebrow', 'Questions: small label'),
                        Fields::text('pages.agent.faqTitle', 'Questions: heading', null, true),
                        Fields::area('pages.agent.faqIntro', 'Questions: text', null, 2)->columnSpanFull(),
                        Fields::text('pages.agent.ctaTitle', 'Closing box: heading', null, true),
                        Fields::area('pages.agent.ctaText', 'Closing box: text', null, 2)->columnSpanFull(),
                    ]),
                ]),
                Tab::make('Each service page')->icon('heroicon-o-squares-2x2')->schema([
                    Section::make('Wording shared by every service page')->description('The service\'s own name, text and search listing are edited under Services. Use {service} for the service name in lower case, like "import clearance".')->columns(2)->schema([
                        Fields::text('pages.service.overviewTitle', 'Heading above the main text', null, true)->columnSpanFull(),
                        Fields::text('pages.service.includedLabel', 'Label above the "included" list', null, true),
                        Fields::text('pages.service.problemsEyebrow', 'Problems: small label'),
                        Fields::text('pages.service.problemsTitle', 'Problems: heading', null, true),
                        Fields::area('pages.service.problemsIntro', 'Problems: text', null, 2)->columnSpanFull(),
                        Fields::text('pages.service.processEyebrow', 'Steps: small label'),
                        Fields::text('pages.service.processTitle', 'Steps: heading', null, true),
                        Fields::area('pages.service.processIntro', 'Steps: text', null, 2)->columnSpanFull(),
                        Fields::text('pages.service.moreTitle', 'Heading above the other services', null, true)->columnSpanFull(),
                        Fields::text('pages.service.ctaTitle', 'Closing box: heading', null, true),
                        Fields::area('pages.service.ctaText', 'Closing box: text', null, 2),
                    ]),
                ]),
                Tab::make('Footer')->icon('heroicon-o-bars-3-bottom-left')->schema([
                    Section::make('Bottom of every page')->description($tokens)->columns(1)->schema([
                        Fields::area('pages.footer.about', 'About text', null, 3)->required(),
                        Fields::text('pages.footer.linksHeading', 'Heading of the first column', null, true),
                        Fields::text('pages.footer.contactHeading', 'Heading of the contact column', null, true),
                        Fields::text('pages.footer.moreHeading', 'Heading of the second column', null, true),
                    ]),
                ]),
            ]),
        ];
    }
}
