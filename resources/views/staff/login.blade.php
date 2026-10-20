@extends('layouts.app', ['title' => 'Staff sign in', 'robots' => 'noindex, nofollow'])

@section('content')
    <x-page-header title="Staff sign in" :crumbs="[['label' => 'Staff sign in']]" />
    <x-container class="max-w-xl py-16">
        @include('staff.login-form', ['state' => $state])
    </x-container>
@endsection
