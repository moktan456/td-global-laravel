<?php

namespace App\Filament\Support;

use App\Models\ContentOverride;
use App\Support\Content;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;

/**
 * An admin page that edits one or more content files (see App\Support\Content). The form is filled from the live
 * content, and Save stores it as an override, so the public site changes at once. "Back to the original text" deletes
 * the override.
 *
 * Subclasses say which files they own (`files()`: file => the top-level keys it edits, or null for the whole file) and
 * draw the fields (`fields()`), whose state paths start with the file name: `site.site.name`, `services`, `faq`.
 *
 * @property-read Schema $form
 */
abstract class ContentPage extends Page
{
    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /** The public page this one changes, shown as a "View page" button. */
    protected static ?string $previewUrl = null;

    protected static ?string $intro = null;

    /** @return array<string, list<string>|null> */
    abstract protected static function files(): array;

    /** @return array<int, Component> */
    abstract protected function fields(): array;

    public function mount(): void
    {
        $this->form->fill($this->loadState());
    }

    /** @return array<string, mixed> */
    protected function loadState(): array
    {
        $state = [];
        foreach (array_keys(static::files()) as $file) {
            $state[$file] = Content::load($file);
        }

        return $this->toForm($state);
    }

    /** Reshape the stored content for the form (override when the stored shape is awkward to edit). */
    protected function toForm(array $state): array
    {
        return $state;
    }

    /** The reverse of toForm(). */
    protected function fromForm(array $state): array
    {
        return $state;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components($this->fields())->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Save changes')->submit('save')->keyBindings(['mod+s']),
                    ])->alignment(Alignment::Start)->sticky()->key('form-actions'),
                ]),
        ]);
    }

    public function save(): void
    {
        $state = $this->fromForm($this->form->getState());
        if ($bad = self::unsafeLink($state)) {
            Notification::make()->title('Not saved: unsafe link')->body("The link \"{$bad}\" is not allowed. Use a page on this site like /services, or a web address starting with https://.")->danger()->send();

            return;
        }

        foreach (static::files() as $file => $keys) {
            $value = self::blank($state[$file] ?? []);
            $existing = (array) (ContentOverride::find($file)?->data ?? []);
            $new = $keys === null
                ? Content::merge($existing, $value)
                : Content::merge($existing, array_intersect_key($value, array_flip($keys)));
            ContentOverride::updateOrCreate(['name' => $file], ['data' => $new, 'updated_by' => auth()->id()]);
        }
        Content::flush();

        Notification::make()->title('Saved. The website now shows your changes.')->success()->send();
    }

    /** Forget every edit made on this page and show the text that shipped with the site. */
    public function resetToOriginal(): void
    {
        foreach (static::files() as $file => $keys) {
            $row = ContentOverride::find($file);
            if (! $row) {
                continue;
            }
            if ($keys === null) {
                $row->delete();

                continue;
            }
            $data = (array) $row->data;
            foreach ($keys as $key) {
                unset($data[$key]);
            }
            $data === [] ? $row->delete() : $row->update(['data' => $data]);
        }
        Content::flush();
        $this->form->fill($this->loadState());
        Notification::make()->title('Back to the original text.')->success()->send();
    }

    /** The first link in the data that browsers would run as code (javascript:, data:...), or null. A safety net behind the field rules. */
    private static function unsafeLink(mixed $value, string $key = ''): ?string
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if ($bad = self::unsafeLink($v, (string) $k)) {
                    return $bad;
                }
            }

            return null;
        }
        if (is_string($value) && trim($value) !== '' && in_array($key, ['href', 'url', 'mapUrl', 'photoUrl', 'photographerUrl', 'facebook', 'x', 'linkedin'], true)
            && safe_url($value) === '#' && trim($value) !== '#') {
            return $value;
        }

        return null;
    }

    /** Empty inputs arrive as null; the site expects empty text. */
    private static function blank(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($v) => self::blank($v), $value);
        }

        return $value ?? '';
    }

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        $actions = [];
        if (static::$previewUrl) {
            $actions[] = Action::make('preview')->label('View page')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')->url(static::$previewUrl)->openUrlInNewTab();
        }
        $actions[] = Action::make('reset')
            ->label('Back to the original text')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Go back to the original text?')
            ->modalDescription('Everything you changed on this page is removed and the text that came with the website returns. This cannot be undone.')
            ->modalSubmitActionLabel('Yes, go back')
            ->action(fn () => $this->resetToOriginal());

        return $actions;
    }

    public function getSubheading(): string|Htmlable|null
    {
        return static::$intro;
    }
}
