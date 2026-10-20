@php
    $state = $state ?? form_state('team');
    $ids = ['full_name' => 'team-name', 'username' => 'team-username', 'password' => 'team-password', 'role' => 'team-role'];
    $min = \App\Support\StaffAccounts::MIN_PASSWORD_LENGTH;
@endphp
<form id="team-form" method="post" action="/staff/team" data-enhance novalidate class="grid gap-5">
    @csrf
    <x-form-status :state="$state" :ids="$ids" />
    <x-text-field :id="$ids['full_name']" name="full_name" label="Full name" placeholder="e.g. Pema Dorji" autocomplete="off" :value="$state->value('full_name')" :error="$state->error('full_name')" />
    <x-text-field :id="$ids['username']" name="username" label="Username" hint="Lowercase letters, numbers, dots, dashes or underscores. For example pema.dorji" autocomplete="off" :value="$state->value('username')" :error="$state->error('username')" />
    <x-text-field :id="$ids['password']" name="password" label="Temporary password" :hint="'At least '.$min.' characters. Tell them in person or by phone. They\'ll choose their own at first sign-in.'" autocomplete="off" :error="$state->error('password')" />
    <x-select-field :id="$ids['role']" name="role" label="Role" :value="$state->value('role') ?: 'staff'" :error="$state->error('role')" hint="Staff update shipments and clients. A super admin can also manage this team." :options="[['value' => 'staff', 'label' => 'Staff'], ['value' => 'admin', 'label' => 'Super admin']]" />
    <div>
        <x-submit-button>Create account</x-submit-button>
    </div>
</form>
