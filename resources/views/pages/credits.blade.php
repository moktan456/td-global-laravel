@extends('layouts.app', [
    'title' => 'Photo credits',
    'description' => 'The photographers whose images appear on the TD Global Clearing & Logistics website.',
    'canonical' => '/credits',
])

@section('content')
    <x-page-header title="Photo credits" :crumbs="[['label' => 'Photo credits']]">
        <x-slot:intro>
            <p>Cargo, crane and yard photos on this site are TD Global's own. Other photos are from <a href="https://unsplash.com/license" class="font-semibold text-white underline underline-offset-2">Unsplash</a> and used under the Unsplash License. Thank you to the photographers.</p>
        </x-slot:intro>
    </x-page-header>
    <x-container class="max-w-3xl py-16">
        <ul class="divide-y divide-line border-y border-line">
            @foreach (content('credits') as $key => $c)
                <li class="py-4">
                    <p class="text-ink">{{ content('images')[$key]['alt'] ?? $key }}</p>
                    <p class="mt-1 text-sm text-muted">
                        @if (! empty($c['placeholder']) || empty($c['photographer']))
                            Placeholder — photo not downloaded yet (Unsplash ID {{ $c['unsplashId'] }})
                        @else
                            Photo by
                            @if (! empty($c['photographerUrl']))
                                <a href="{{ $c['photographerUrl'] }}" class="font-semibold text-brand underline underline-offset-2">{{ $c['photographer'] }}</a>
                            @else
                                {{ $c['photographer'] }}
                            @endif
                            on
                            <a href="{{ $c['photoUrl'] }}" class="font-semibold text-brand underline underline-offset-2">Unsplash</a>
                        @endif
                    </p>
                </li>
            @endforeach
        </ul>
    </x-container>
@endsection
