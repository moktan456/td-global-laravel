<?php

namespace App\Filament\Pages;

use App\Filament\Support\ContentPage;
use App\Filament\Support\Fields;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class ButtonsAndForms extends ContentPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cursor-arrow-rays';

    protected static string|\UnitEnum|null $navigationGroup = 'Pages';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Buttons & forms';

    protected static ?string $title = 'Buttons, form labels & messages';

    protected static ?string $slug = 'buttons-and-forms';

    protected static ?string $previewUrl = '/quote';

    protected static ?string $intro = 'The text on buttons and links, the labels on the contact, quote and calculator forms, and the messages visitors see after sending.';

    protected static function files(): array
    {
        return ['labels' => null];
    }

    /** @param  array<string, string>  $map  field => label */
    private function group(string $group, array $map, array $long = [], array $help = []): array
    {
        $fields = [];
        foreach ($map as $key => $label) {
            $path = "labels.$group.$key";
            $fields[] = in_array($key, $long, true)
                ? Fields::area($path, $label, $help[$key] ?? null, 2)->columnSpanFull()
                : Fields::text($path, $label, $help[$key] ?? null);
        }

        return $fields;
    }

    protected function fields(): array
    {
        return [
            Tabs::make('Labels')->persistTabInQueryString()->tabs([
                Tab::make('Buttons & links')->icon('heroicon-o-cursor-arrow-rays')->schema([
                    Section::make('Buttons and links used across the website')->columns(2)->schema($this->group('buttons', [
                        'requestQuote' => 'Request a quote (main button)',
                        'getQuote' => 'Get a quote (service pages)',
                        'exactQuote' => 'Get an exact quote (calculator)',
                        'contactUs' => 'Contact us',
                        'call' => 'Call button',
                        'learnMore' => 'Learn more (service cards)',
                        'readMore' => 'Read more (article cards)',
                        'allArticles' => 'All articles',
                        'moreAboutUs' => 'More about us',
                        'agentLink' => 'Link to the Gelephu clearing-agent page',
                        'estimateCost' => 'Estimate the cost',
                        'calculateCost' => 'Calculate cost (home page)',
                        'whatsapp' => 'WhatsApp link',
                        'directions' => 'Get directions',
                        'popular' => 'Badge on the popular price pack',
                        'backToTop' => 'Back to top',
                        'staffLogin' => 'Staff login link (top bar)',
                    ], [], ['call' => 'Use {phone} for the phone number.'])),
                ]),
                Tab::make('Contact form')->icon('heroicon-o-envelope')->schema([
                    Section::make('Contact form')->description('The grey example text inside each box is the "example".')->columns(2)->schema($this->group('contactForm', [
                        'nameLabel' => 'Name: label', 'namePlaceholder' => 'Name: example',
                        'emailLabel' => 'Email: label', 'emailPlaceholder' => 'Email: example',
                        'messageLabel' => 'Message: label', 'messageHint' => 'Message: help text', 'messagePlaceholder' => 'Message: example',
                        'submit' => 'Send button',
                    ], ['messagePlaceholder'])),
                ]),
                Tab::make('Quote form')->icon('heroicon-o-document-check')->schema([
                    Section::make('Your details')->columns(2)->schema($this->group('quoteForm', [
                        'detailsLegend' => 'Group title', 'planLabel' => 'Label before the chosen plan',
                        'nameLabel' => 'Name: label', 'namePlaceholder' => 'Name: example',
                        'emailLabel' => 'Email: label', 'emailPlaceholder' => 'Email: example',
                        'companyLabel' => 'Company: label', 'companyPlaceholder' => 'Company: example',
                    ])),
                    Section::make('Your shipment')->columns(2)->schema($this->group('quoteForm', [
                        'shipmentLegend' => 'Group title', 'directionLabel' => 'Import or export: label',
                        'importOption' => 'Import option', 'exportOption' => 'Export option',
                        'modeLabel' => 'Shipping method: label', 'airOption' => 'Air option', 'roadOption' => 'Road option', 'railOption' => 'Rail option',
                        'goodsLabel' => 'Goods: label', 'goodsHint' => 'Goods: help text', 'detailsLabel' => 'Extra details: label',
                        'submit' => 'Send button',
                    ])),
                ]),
                Tab::make('Calculator')->icon('heroicon-o-calculator')->schema([
                    Section::make('Cost calculator')->columns(2)->schema($this->group('calculatorForm', [
                        'directionLegend' => 'Direction: title', 'importOption' => 'Import option', 'exportOption' => 'Export option',
                        'typeLegend' => 'Shipment type: title', 'standardOption' => 'Standard option', 'standardHint' => 'Standard: help text',
                        'complexOption' => 'Complex option', 'complexHint' => 'Complex: help text',
                        'modeLabel' => 'Shipping method: label', 'airOption' => 'Air option', 'roadOption' => 'Road option', 'railOption' => 'Rail option',
                        'linesLabel' => 'Tariff lines: label', 'linesHint' => 'Tariff lines: help text',
                        'baseFee' => 'Breakdown: base fee', 'extraLines' => 'Breakdown: extra tariff lines', 'handling' => 'Breakdown: handling', 'exportDiscount' => 'Breakdown: export discount',
                        'linesError' => 'Tariff lines: error message', 'badResult' => 'Result box: when the lines are not valid',
                        'estimateTitle' => 'Result box: title', 'estimateCaption' => 'Result box: caption', 'disclaimer' => 'Result box: small print',
                    ], ['linesHint', 'disclaimer'], ['linesHint' => 'Use {included} for the number of lines included in the base fee.'])),
                ]),
                Tab::make('Hidden text')->icon('heroicon-o-eye-slash')->schema([
                    Section::make('Read aloud by screen readers, or shown as a hint')->description('Visitors who use a screen reader hear these. The search box example text is shown on screen.')->columns(2)->schema($this->group('a11y', [
                        'skip' => 'Skip link at the very top',
                        'newTab' => 'Added to links that open a new tab',
                        'mapNewTab' => 'Map directions link: new tab note',
                        'searchLabel' => 'Search box: label', 'searchPlaceholder' => 'Search box: example text', 'searchButton' => 'Search button',
                        'menu' => 'Phone menu button', 'close' => 'Close button', 'emailUs' => 'Email button (bottom right)', 'viewFullSize' => 'Certificate: view full size',
                        'prevSlide' => 'Banner: previous', 'nextSlide' => 'Banner: next', 'stopSlides' => 'Banner: stop the slide show', 'startSlides' => 'Banner: start the slide show',
                        'prevArticle' => 'Articles: previous', 'nextArticle' => 'Articles: next', 'prevServices' => 'Services: scroll back', 'nextServices' => 'Services: scroll on',
                    ])),
                ]),
                Tab::make('Messages')->icon('heroicon-o-chat-bubble-bottom-center-text')->schema([
                    Section::make('After sending')->description('Use {name} and {email} to include what the visitor typed.')->columns(1)->schema($this->group('messages', [
                        'contactSuccess' => 'Contact form: sent',
                        'quoteSuccess' => 'Quote form: sent',
                        'notConnected' => 'When the form is not connected',
                        'sendFailed' => 'When sending fails',
                        'tooMany' => 'When someone sends too many messages',
                    ], ['contactSuccess', 'quoteSuccess', 'notConnected', 'sendFailed', 'tooMany'], ['notConnected' => 'Use {email} for the company email.'])),
                    Section::make('Problems with what was typed')->columns(2)->schema($this->group('messages', [
                        'enterName' => 'Name missing', 'enterEmail' => 'Email missing', 'badEmail' => 'Email not valid',
                        'enterMessage' => 'Message missing', 'chooseDirection' => 'Import or export not chosen',
                        'chooseMode' => 'Shipping method not chosen', 'enterGoods' => 'Goods not described',
                    ])),
                    Section::make('Small words')->columns(2)->schema($this->group('common', [
                        'optional' => 'Tag after optional fields', 'choose' => 'First choice in drop-downs',
                    ])),
                ]),
            ]),
        ];
    }
}
