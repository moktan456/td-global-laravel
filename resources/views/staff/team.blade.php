@extends('layouts.staff', ['title' => 'Team'])

@section('content')
    <div class="grid gap-6">
        {{-- Adding someone is occasional; the team list is what you come back to. --}}
        <details id="add-staff" @if ($state->status !== 'idle') open @endif class="rounded-card border border-line bg-white p-5">
            <summary class="min-h-11 cursor-pointer py-2 font-display text-xl font-semibold text-navy-900">Add a staff member</summary>
            <p class="mt-2 text-muted">They sign in with this username and password at the staff sign-in page.</p>
            <div class="mt-5 max-w-3xl">
                @include('staff.team-form', ['state' => $state])
            </div>
        </details>

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

            @if ($team->count())
                {{-- One row per person; the password and switch-off controls are in the Manage menu (no overflow clipping: the menu hangs below its row). --}}
                <div class="mt-4 rounded-card border border-line bg-white">
                    <div aria-hidden="true" class="hidden gap-x-6 rounded-t-card border-b border-line bg-sand px-5 py-3 text-sm font-semibold text-navy-900 md:grid md:grid-cols-[minmax(0,1.4fr)_7.5rem_10.5rem_6.5rem_7.5rem]">
                        <span>Person</span><span>Role</span><span>Status</span><span>Added</span><span></span>
                    </div>
                    <ul class="divide-y divide-line">
                        @foreach ($team as $m)
                            <li class="grid items-center gap-x-6 gap-y-3 px-5 py-4 md:grid-cols-[minmax(0,1.4fr)_7.5rem_10.5rem_6.5rem_7.5rem]">
                                <div class="min-w-0">
                                    <p class="break-words font-semibold text-navy-900">{{ $m->name }}{{ $m->id === auth()->id() ? ' (you)' : '' }}</p>
                                    <p class="break-all text-muted">{{ $m->username ?: $m->email }}</p>
                                </div>
                                <div>
                                    @if ($m->role === 'admin')
                                        <span class="inline-flex items-center rounded-full bg-navy-900 px-3 py-1 text-sm font-semibold text-white">Super admin</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-sand px-3 py-1 text-sm font-semibold text-navy-900">Staff</span>
                                    @endif
                                </div>
                                <div>
                                    @if ($m->active)
                                        <span class="inline-flex items-center rounded-full bg-success/15 px-3 py-1 text-sm font-semibold text-success">Active</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-error/10 px-3 py-1 text-sm font-semibold text-error">Switched off</span>
                                    @endif
                                    @if ($m->must_change_password)
                                        <p class="mt-1 text-sm text-muted">Temporary password</p>
                                    @endif
                                </div>
                                <p class="text-sm text-muted md:text-base">{{ $m->created_at->timezone('Asia/Thimphu')->format('j M Y') }}</p>
                                <details data-menu class="relative md:justify-self-end">
                                    <summary class="inline-flex min-h-11 cursor-pointer list-none items-center gap-2 rounded-control border border-navy-900 px-4 font-semibold text-navy-900 hover:bg-sand [&::-webkit-details-marker]:hidden">
                                        Manage<x-lucide name="chevron-down" class="size-4" /><span class="sr-only"> {{ $m->name }}</span>
                                    </summary>
                                    <div class="absolute right-0 z-20 mt-2 w-[min(24rem,calc(100vw-2rem))] rounded-card border border-line bg-white p-5 shadow-lg max-md:left-0 max-md:right-auto">
                                        <p class="font-semibold text-navy-900">{{ $m->name }}</p>
                                        @include('staff.team-row', ['member' => $m, 'state' => form_state('staffrow'.$m->id)])
                                    </div>
                                </details>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <x-pager :paginator="$team" label="people" />
        </section>
    </div>
@endsection
