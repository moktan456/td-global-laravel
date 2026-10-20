@extends('layouts.app', ['title' => 'Change password', 'robots' => 'noindex, nofollow'])

@section('content')
    <x-page-header title="Choose your password" :crumbs="[['label' => 'Staff', 'href' => '/staff'], ['label' => 'Password']]" />
    <x-container class="max-w-xl py-16">
        <p class="mb-8 text-lg text-muted">
            {{ $user->must_change_password ? "Welcome, {$user->name}. The password you were given is temporary. Choose your own to continue." : "Choose a new password. You'll stay signed in on this device." }}
        </p>
        @include('staff.password-form', ['state' => $state])
    </x-container>
@endsection
