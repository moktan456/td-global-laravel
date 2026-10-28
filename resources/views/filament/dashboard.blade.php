{{-- Filament's stylesheet only contains its own classes, so the layout here uses Filament components plus a little inline CSS. --}}
<x-filament-panels::page>
    <x-filament::section>
        <p style="max-width:48rem;line-height:1.6">
            Pick what you want to change. Each page shows the text exactly as it appears on the website. Press <strong>Save changes</strong> and the website updates straight away.
            Changed your mind? <strong>Back to the original text</strong> on each page undoes your edits.
            New here? Read the <a href="{{ \App\Filament\Pages\UserGuide::getUrl() }}" style="font-weight:600;text-decoration:underline">user guide</a>.
        </p>
    </x-filament::section>

    @foreach ($this->sections() as $group => $cards)
        <x-filament::section :heading="$group">
            <ul style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fill,minmax(17rem,1fr));list-style:none;margin:0;padding:0">
                @foreach ($cards as $card)
                    <li>
                        <a href="{{ $card['url'] }}" style="display:flex;gap:.9rem;height:100%;padding:1rem;border-radius:.75rem;text-decoration:none;color:inherit;border:1px solid rgba(128,128,128,.3)">
                            <x-filament::icon :icon="$card['icon']" style="width:1.75rem;height:1.75rem;flex-shrink:0;color:var(--primary-500)" />
                            <span>
                                <span style="display:block;font-weight:600">{{ $card['label'] }}</span>
                                <span style="display:block;margin-top:.25rem;font-size:.875rem;opacity:.75">{{ $card['hint'] }}</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </x-filament::section>
    @endforeach

    <p style="font-size:.875rem;opacity:.75">
        Shipment tracking, clients and the team are managed in the <a href="/staff" style="font-weight:600;text-decoration:underline">staff area</a>, not here.
    </p>
</x-filament-panels::page>
