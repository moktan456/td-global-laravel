@extends('layouts.staff-auth', ['title' => 'Staff sign in', 'lead' => 'Sign in with the username and password you were given.'])

@section('content')
    @include('staff.login-form', ['state' => $state])
@endsection
