@php
    $phone = site('contact.phone');
    $phoneHref = site('contact.phoneHref');
    $email = site('contact.email');
    $address = site('contact.address');
@endphp
<aside aria-label="Contact and account" class="on-dark bg-navy-950 text-sm text-on-dark-muted print:hidden">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-6 gap-y-1 px-4 py-1.5 sm:px-6 lg:px-16">
        <ul class="flex flex-wrap items-center gap-x-6 gap-y-1" aria-label="Contact details">
            @if (is_filled($phone))
                <li class="flex items-center gap-2">
                    <x-lucide name="phone" class="size-4 text-gold" />
                    @if ($phoneHref)
                        <a href="tel:{{ $phoneHref }}" class="inline-flex items-center py-1.5 hover:text-white pointer-coarse:min-h-11">{{ $phone }}</a>
                    @else
                        <span>{{ $phone }}</span>
                    @endif
                </li>
            @endif
            <li class="flex items-center gap-2">
                <x-lucide name="mail" class="size-4 text-gold" />
                <a href="mailto:{{ $email }}" class="inline-flex items-center py-1.5 hover:text-white pointer-coarse:min-h-11">{{ $email }}</a>
            </li>
            @if (is_filled($address))
                <li class="hidden items-center gap-2 md:flex">
                    <x-lucide name="map-pin" class="size-4 text-gold" />
                    <span>{{ $address }}</span>
                </li>
            @endif
        </ul>
        <ul class="flex items-center gap-x-6" aria-label="Staff and contact">
            <li><a href="/staff/login" class="inline-flex items-center py-1.5 hover:text-white pointer-coarse:min-h-11">{{ label('buttons.staffLogin') }}</a></li>
            <li><a href="/contact" class="inline-flex items-center py-1.5 hover:text-white pointer-coarse:min-h-11">{{ label('buttons.contactUs') }}</a></li>
        </ul>
    </div>
</aside>
