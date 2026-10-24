@extends('layouts.staff-auth', ['title' => 'Choose your password', 'lead' => $user->must_change_password ? "Welcome, {$user->name}. The password you were given is temporary. Choose your own to continue." : "Choose a new password. You'll stay signed in on this device."])

@section('content')
    @include('staff.password-form', ['state' => $state])
@endsection
