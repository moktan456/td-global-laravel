@php
    $state = $state ?? form_state('password');
    $ids = ['password' => 'password-new', 'confirm' => 'password-confirm'];
@endphp
<form id="password-form" method="post" action="/staff/password" data-enhance novalidate class="grid gap-5">
    @csrf
    <x-form-status :state="$state" :ids="$ids" />
    <x-text-field :id="$ids['password']" name="password" type="password" label="New password" :hint="'At least '.\App\Support\StaffAccounts::MIN_PASSWORD_LENGTH.' characters. A few random words is a good password.'" autocomplete="new-password" :error="$state->error('password')" />
    <x-text-field :id="$ids['confirm']" name="confirm" type="password" label="Type it again" autocomplete="new-password" :error="$state->error('confirm')" />
    <div>
        <x-submit-button>Save password</x-submit-button>
    </div>
</form>
