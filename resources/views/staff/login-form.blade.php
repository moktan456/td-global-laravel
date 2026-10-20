@php
    $state = $state ?? form_state('login');
    $ids = ['username' => 'login-username', 'password' => 'login-password'];
@endphp
<form id="login-form" method="post" action="/staff/login" data-enhance novalidate class="grid gap-5">
    @csrf
    <x-form-status :state="$state" :ids="$ids" />
    <x-text-field :id="$ids['username']" name="username" label="Username" autocomplete="username" :value="$state->value('username')" :error="$state->error('username')" />
    <x-text-field :id="$ids['password']" name="password" type="password" label="Password" autocomplete="current-password" :error="$state->error('password')" />
    <div>
        <x-submit-button>Sign in</x-submit-button>
    </div>
</form>
