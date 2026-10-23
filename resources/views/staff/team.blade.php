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
                <h2 id="team-heading" class="font-display text-2xl font-semibold text-navy-900">Everyone ({{ $total }})</h2>
                <form method="get" action="/staff/team" role="search" aria-label="Search the team" class="mt-4 flex flex-wrap items-end gap-3">
                    <div class="min-w-56 flex-1 basis-56">
                        <label for="team-q" class="block font-semibold text-navy-900">Search</label>
                        <input id="team-q" type="search" name="q" value="{{ $q }}" placeholder="Name or username" autocomplete="off" class="mt-2 min-h-11 w-full rounded-control border border-control bg-white px-3">
                    </div>
                    <div>
                        <label for="team-role" class="block font-semibold text-navy-900">Role</label>
                        <select id="team-role" name="role" class="mt-2 min-h-11 rounded-control border border-control bg-white px-3">
                            <option value="">Everyone</option>
                            <option value="admin" @selected($role === 'admin')>Super admins</option>
                            <option value="staff" @selected($role === 'staff')>Staff</option>
                        </select>
                    </div>
                    <div>
                        <label for="team-access" class="block font-semibold text-navy-900">Status</label>
                        <select id="team-access" name="access" class="mt-2 min-h-11 rounded-control border border-control bg-white px-3">
                            <option value="">Any</option>
                            <option value="active" @selected($access === 'active')>Active</option>
                            <option value="off" @selected($access === 'off')>Switched off</option>
                        </select>
                    </div>
                    <button type="submit" class="min-h-11 rounded-control bg-navy-900 px-5 font-semibold text-white hover:bg-navy-800">Search</button>
                    @if ($q !== '' || $role !== '' || $access !== '')
                        <a href="/staff/team" class="inline-flex min-h-11 items-center px-2 font-semibold text-brand underline underline-offset-2">Clear</a>
                    @endif
                </form>
                <p role="status" class="mt-4 text-muted">
                    @if ($team->total() === 0)
                        No one matches{{ $q !== '' ? ' “'.$q.'”' : '' }}.
                    @elseif ($q !== '' || $role !== '' || $access !== '')
                        {{ $team->total() }} {{ $team->total() === 1 ? 'person matches' : 'people match' }}{{ $q !== '' ? ' “'.$q.'”' : '' }}. Showing {{ $team->firstItem() }}–{{ $team->lastItem() }}.
                    @else
                        Showing {{ $team->firstItem() }}–{{ $team->lastItem() }} of {{ $team->total() }}, oldest first.
                    @endif
                </p>
                <ul class="mt-4 grid gap-3">
                    @foreach ($team as $m)
                        <li class="rounded-tile border border-line bg-white p-4">
                            <p class="font-semibold text-navy-900">{{ $m->name }}{{ $m->id === auth()->id() ? ' (you)' : '' }}</p>
                            <p class="text-muted">{{ $m->username ? 'Username: '.$m->username : 'Signs in with email: '.$m->email }} · {{ $m->role === 'admin' ? 'Super admin' : 'Staff' }}</p>
                            <p class="mt-1 text-sm text-muted">{{ $m->active ? 'Active' : 'Switched off' }}{{ $m->must_change_password ? ' · has a temporary password' : '' }} · added {{ $m->created_at->timezone('Asia/Thimphu')->format('j M Y') }}</p>
                            @include('staff.team-row', ['member' => $m, 'state' => form_state('staffrow'.$m->id)])
                        </li>
                    @endforeach
                </ul>
                <x-pager :paginator="$team" label="people" />
            </section>
        </div>
    </x-container>
@endsection
