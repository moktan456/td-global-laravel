@extends('layouts.app', ['title' => 'Team', 'robots' => 'noindex, nofollow'])

@section('content')
    <x-page-header title="Team" :crumbs="[['label' => 'Staff', 'href' => '/staff'], ['label' => 'Team']]" />
    <x-container class="py-10">
        <x-staff-nav current="/staff/team" :name="auth()->user()->name" :role="auth()->user()->role" />
        <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_1.2fr]">
            <section aria-labelledby="add-heading" class="self-start rounded-card border border-line bg-white p-6">
                <h2 id="add-heading" class="font-display text-2xl font-semibold text-navy-900">Add a staff member</h2>
                <p class="mt-2 text-muted">They sign in with this username and password at the staff sign-in page.</p>
                <div class="mt-6">
                    @include('staff.team-form', ['state' => $state])
                </div>
            </section>
            <section aria-labelledby="team-heading">
                <h2 id="team-heading" class="font-display text-2xl font-semibold text-navy-900">Everyone ({{ count($team) }})</h2>
                <ul class="mt-6 grid gap-3">
                    @foreach ($team as $m)
                        <li class="rounded-tile border border-line bg-white p-4">
                            <p class="font-semibold text-navy-900">{{ $m->name }}{{ $m->id === auth()->id() ? ' (you)' : '' }}</p>
                            <p class="text-muted">{{ $m->username ? 'Username: '.$m->username : 'Signs in with email: '.$m->email }} · {{ $m->role === 'admin' ? 'Super admin' : 'Staff' }}</p>
                            <p class="mt-1 text-sm text-muted">{{ $m->active ? 'Active' : 'Switched off' }}{{ $m->must_change_password ? ' · has a temporary password' : '' }} · added {{ $m->created_at->timezone('Asia/Thimphu')->format('j M Y') }}</p>
                            @include('staff.team-row', ['member' => $m, 'state' => form_state('staffrow'.$m->id)])
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>
    </x-container>
@endsection
