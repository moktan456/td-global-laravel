@php($state = $state ?? form_state('telegram'))
<form id="telegram-form" method="post" action="/staff/telegram/connect" data-enhance class="grid gap-4">
    @csrf
    <x-form-status :state="$state" :ids="[]" />
    <div>
        <x-submit-button>{{ $connected ? 'Connect again' : 'Connect the bot to this website' }}</x-submit-button>
    </div>
</form>
