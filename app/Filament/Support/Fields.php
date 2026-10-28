<?php

namespace App\Filament\Support;

use App\Support\Content;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

/** Field shortcuts shared by the content pages. */
class Fields
{
    public static function text(string $name, string $label, ?string $help = null, bool $required = false): TextInput
    {
        return TextInput::make($name)->label($label)->helperText($help)->required($required)->maxLength(255);
    }

    public static function area(string $name, string $label, ?string $help = null, int $rows = 3): Textarea
    {
        return Textarea::make($name)->label($label)->helperText($help)->rows($rows)->autosize();
    }

    /** A link target: a path on this site (/services) or a full address. */
    public static function link(string $name = 'href', string $label = 'Links to'): TextInput
    {
        return TextInput::make($name)->label($label)->required()->maxLength(500)
            ->helperText('A page on this site, like /services, or a full web address.');
    }

    /** A photo from the Photos page. */
    public static function photo(string $name, string $label = 'Photo'): Select
    {
        $options = [];
        foreach (Content::load('images') as $key => $img) {
            $options[$key] = $img['alt'] ?? $key;
        }

        return Select::make($name)->label($label)->options($options)->searchable()->required();
    }

    /** @return array<string, string> */
    public static function icons(array $names): array
    {
        return array_combine($names, array_map(fn ($n) => ucfirst($n), $names));
    }
}
