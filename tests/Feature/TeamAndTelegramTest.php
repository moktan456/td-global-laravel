<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class TeamAndTelegramTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    public function test_only_a_super_admin_sees_team_and_telegram_pages(): void
    {
        $this->actingAs($this->staff());
        $this->get('/staff/team')->assertRedirect('/staff');
        $this->get('/staff/telegram')->assertRedirect('/staff');
        $this->post('/staff/team', ['full_name' => 'X', 'username' => 'xxx', 'password' => 'long-enough-pass', 'role' => 'admin'])->assertRedirect('/staff');
        $this->assertDatabaseMissing('users', ['username' => 'xxx']);
        $this->get('/staff')->assertDontSee('/staff/team');
    }

    public function test_admin_creates_staff_who_must_change_the_temporary_password(): void
    {
        $this->actingAs($this->admin());
        $this->get('/staff/team')->assertOk()->assertSee('Add a staff member');
        $this->post('/staff/team', ['full_name' => 'Pema Dorji', 'username' => 'Pema.Dorji', 'password' => 'temporary-pass-1', 'role' => 'staff'], ['X-Enhance' => '1'])
            ->assertSee('can now sign in as "pema.dorji"');
        $user = User::where('username', 'pema.dorji')->firstOrFail();
        $this->assertTrue($user->must_change_password);
        $this->assertSame('pema.dorji@staff.td-global.invalid', $user->email);
        $this->assertSame('staff', $user->role);

        $this->post('/staff/team', ['full_name' => 'Again', 'username' => 'pema.dorji', 'password' => 'temporary-pass-1', 'role' => 'staff'], ['X-Enhance' => '1'])->assertSee('That username is taken.');
        $this->post('/staff/team', ['full_name' => '', 'username' => 'A', 'password' => 'short', 'role' => 'boss'], ['X-Enhance' => '1'])
            ->assertSee('Use 3 to 30 lowercase letters')->assertSee('Use at least 10 characters.')->assertSee('Choose a role.');
        $this->post('/staff/team', ['full_name' => 'Z', 'username' => 'zed', 'password' => 'zed-zed-zed-zed', 'role' => 'staff'], ['X-Enhance' => '1'])->assertSee("The password can't contain the username.");
    }

    public function test_reset_password_signs_the_person_out_and_requires_a_new_one(): void
    {
        $admin = $this->admin();
        $user = $this->staff();
        DB::table('sessions')->insert(['id' => 'abc', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
        $this->actingAs($admin)->post("/staff/team/{$user->id}/password", ['password' => 'new-temp-password-1'], ['X-Enhance' => '1'])->assertSee('New temporary password set');
        $this->assertTrue($user->fresh()->must_change_password);
        $this->assertDatabaseMissing('sessions', ['id' => 'abc']);
        auth()->logout();
        $this->post('/staff/login', ['username' => $user->username, 'password' => 'new-temp-password-1'])->assertRedirect('/staff/password');
    }

    public function test_switching_someone_off_blocks_them_but_you_cannot_switch_off_yourself_or_the_last_admin(): void
    {
        $admin = $this->admin();
        $user = $this->staff();
        $this->actingAs($admin);
        $this->post("/staff/team/{$user->id}/active", ['active' => 'false'], ['X-Enhance' => '1'])->assertSee('is switched off.');
        $this->assertFalse($user->fresh()->active);
        $this->post("/staff/team/{$user->id}/active", ['active' => 'true'], ['X-Enhance' => '1'])->assertSee('is active again.');
        $this->assertTrue($user->fresh()->active);

        $this->post("/staff/team/{$admin->id}/active", ['active' => 'false'], ['X-Enhance' => '1'])->assertSee("You can't switch off your own account.");
        $second = $this->admin();
        $this->actingAs($second)->post("/staff/team/{$admin->id}/active", ['active' => 'false'], ['X-Enhance' => '1'])->assertSee('is switched off.');
        $this->actingAs($admin)->assertTrue(true);
        $third = $this->staff();
        $this->actingAs($second)->post("/staff/team/{$admin->id}/active", ['active' => 'true'], ['X-Enhance' => '1']);
        $this->assertTrue($admin->fresh()->active);
    }

    public function test_webhook_rejects_requests_without_the_secret(): void
    {
        config(['td.telegram.bot_token' => 'TESTTOKEN', 'td.telegram.webhook_secret' => 'a-long-enough-secret-123']);
        $this->postJson('/api/telegram/webhook', [])->assertForbidden();
        $this->postJson('/api/telegram/webhook', [], ['X-Telegram-Bot-Api-Secret-Token' => 'wrong'])->assertForbidden();
        config(['td.telegram.bot_token' => '']);
        $this->postJson('/api/telegram/webhook', [], ['X-Telegram-Bot-Api-Secret-Token' => 'a-long-enough-secret-123'])->assertStatus(503);
    }

    public function test_chatid_and_status_commands(): void
    {
        config(['td.telegram.bot_token' => 'TESTTOKEN', 'td.telegram.webhook_secret' => 'a-long-enough-secret-123']);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
        $h = ['X-Telegram-Bot-Api-Secret-Token' => 'a-long-enough-secret-123'];
        $this->postJson('/api/telegram/webhook', ['message' => ['text' => '/chatid@td_bot', 'chat' => ['id' => -100123, 'type' => 'supergroup']]], $h)->assertOk();
        Http::assertSent(fn ($r) => str_contains($r['text'], '-100123') && str_contains($r['text'], 'TELEGRAM_STAFF_CHAT_ID'));

        $client = $this->client(['telegram_chat_id' => 42]);
        $s = $this->shipment($client);
        $this->postJson('/api/telegram/webhook', ['message' => ['text' => '/status', 'chat' => ['id' => 42, 'type' => 'private']]], $h)->assertOk();
        Http::assertSent(fn ($r) => str_contains($r['text'], $s->tracking_number) && str_contains($r['text'], 'Declaration Submitted'));
        // Group chats other than /chatid are ignored.
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
        $this->postJson('/api/telegram/webhook', ['message' => ['text' => '/status', 'chat' => ['id' => -1, 'type' => 'group']]], $h)->assertOk();
        Http::assertNothingSent();
    }

    public function test_admin_can_connect_the_bot_from_the_staff_site(): void
    {
        config(['td.telegram.bot_token' => 'TESTTOKEN', 'td.telegram.bot_username' => 'td_test_bot', 'td.telegram.webhook_secret' => 'a-long-enough-secret-123']);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['username' => 'td_test_bot', 'url' => '']])]);
        $this->actingAs($this->admin());
        $this->get('/staff/telegram')->assertOk()->assertSee('Bot recognised by Telegram')->assertSee('@td_test_bot');
        $this->post('/staff/telegram/connect', [], ['X-Enhance' => '1'])->assertSee('Connected. Telegram will now send messages to');
        Http::assertSent(fn ($r) => str_contains($r->url(), '/setWebhook') && $r['secret_token'] === 'a-long-enough-secret-123' && str_ends_with($r['url'], '/api/telegram/webhook'));
    }
}
