{{-- First sign-in with a temporary password: the plain centred card (nothing else is allowed until they choose one).
     A normal change, any time later: inside the workspace, with the sidebar. --}}
@php
    $forced = $user->must_change_password;
    $lead = $forced
        ? "Welcome, {$user->name}. The password you were given is temporary. Choose your own to continue."
        : "Choose a new password. You'll stay signed in on this device.";
@endphp
@extends($forced ? 'layouts.staff-auth' : 'layouts.staff', $forced
    ? ['title' => 'Choose your password', 'lead' => $lead]
    : ['title' => 'Change password', 'subtitle' => $lead, 'crumbs' => [['label' => 'Shipments', 'href' => '/staff'], ['label' => 'Change password']]])

@section('content')
    @if ($forced)
        @include('staff.password-form', ['state' => $state])
    @else
        <div class="max-w-xl rounded-card border border-line bg-surface p-6">
            @include('staff.password-form', ['state' => $state])
        </div>
    @endif
@endsection
