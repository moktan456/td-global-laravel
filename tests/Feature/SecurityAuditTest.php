<?php

namespace Tests\Feature;

use App\Filament\Pages\FaqPage;
use App\Filament\Pages\Menus;
use App\Filament\Pages\PhotosPage;
use App\Filament\Pages\SiteSettings;
use App\Mail\PlainMessage;
use App\Models\ContentOverride;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\Support\MakesStaff;
use Tests\TestCase;

/** Regression tests for the findings of the October 2026 security audit. */
class SecurityAuditTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    public function test_text_from_the_admin_cannot_break_out_of_the_structured_data_script(): void
    {
        $this->actingAs($this->admin());
        $page = Livewire::test(FaqPage::class);
        $d = $page->get('data');
        $k = array_key_first($d['faq']['list']);
        $d['faq']['list'][$k]['answer'] = 'x</script><script>alert(1)</script>';
        $page->set('data', $d)->call('save');

        $html = $this->get('/clearing-agent-gelephu')->getContent();
        $this->assertStringNotContainsString('</script><script>alert(1)', $html);
        $this->assertStringContainsString('\\u003C/script\\u003E', $html);
    }

    public function test_links_that_run_code_are_refused_by_the_admin(): void
    {
        $this->actingAs($this->admin());
        $page = Livewire::test(Menus::class);
        $d = $page->get('data');
        $k = array_key_first($d['site']['primaryNav']);
        $d['site']['primaryNav'][$k]['href'] = 'javascript:alert(document.cookie)';
        $page->set('data', $d)->call('save')->assertHasFormErrors();
        $this->get('/about')->assertDontSee('javascript:alert', false);

        $site = Livewire::test(SiteSettings::class);
        $s = $site->get('data');
        $s['site']['site']['social']['facebook'] = 'javascript:alert(1)';
        $site->set('data', $s)->call('save');
        $this->get('/')->assertDontSee('javascript:alert', false);
    }

    public function test_safe_url_only_lets_safe_links_through(): void
    {
        foreach (['/services', 'https://example.com/a?b=c', 'http://example.com', 'mailto:a@example.com', 'tel:+97517975757', '#top', 'services/x'] as $ok) {
            $this->assertSame($ok, safe_url($ok), $ok);
        }
        foreach (['javascript:alert(1)', 'JaVaScRiPt:alert(1)', " java\tscript:alert(1)", 'data:text/html,<script>', 'vbscript:x', 'file:///etc/passwd', ''] as $bad) {
            $this->assertSame('#', safe_url($bad), $bad);
        }
    }

    public function test_hostile_links_already_stored_are_neutralised_when_shown(): void
    {
        ContentOverride::create(['name' => 'site', 'data' => ['footerLinks' => [['label' => 'Click', 'href' => 'javascript:alert(1)']], 'site' => ['social' => ['x' => 'data:text/html,hi']]]]);
        $this->get('/')->assertOk()->assertDontSee('javascript:alert', false)->assertDontSee('data:text/html', false);
    }

    public function test_the_contact_and_quote_forms_cannot_be_used_to_flood_the_inbox(): void
    {
        config(['mail.default' => 'smtp']);
        Mail::fake();
        $accepted = 0;
        for ($i = 0; $i < 20; $i++) {
            $r = $this->post('/contact', ['name' => 'Bot', 'email' => 'bot@example.com', 'message' => "spam $i"], ['X-Enhance' => '1']);
            $accepted += str_contains($r->getContent(), 'received your message') ? 1 : 0;
        }
        $this->assertSame(config('td.limits.forms.max'), $accepted);
        $this->post('/quote', ['name' => 'Bot', 'email' => 'bot@example.com', 'direction' => 'import', 'mode' => 'road', 'goods' => 'x'], ['X-Enhance' => '1'])
            ->assertSee('Too many messages');
    }

    public function test_a_filled_honeypot_field_sends_nothing_and_says_nothing(): void
    {
        config(['mail.default' => 'smtp']);
        Mail::fake();
        $this->post('/contact', ['name' => 'Bot', 'email' => 'bot@example.com', 'message' => 'hi', 'homepage_url' => 'http://spam.example'], ['X-Enhance' => '1'])->assertOk();
        Mail::assertNothingSent();
        Mail::assertNothingQueued();
    }

    public function test_form_fields_are_capped_in_length(): void
    {
        config(['mail.default' => 'smtp']);
        Mail::fake();
        $this->post('/contact', ['name' => 'A', 'email' => 'a@example.com', 'message' => str_repeat('x', 200000), 'extra' => ['nested']], ['X-Enhance' => '1'])->assertOk();
        Mail::assertSent(PlainMessage::class, fn ($m) => strlen($m->render()) < 6000);
    }

    public function test_a_forged_forwarded_for_header_cannot_dodge_the_sign_in_limit(): void
    {
        $this->staff(['username' => 'pema']);
        // The visitor connects from 203.0.113.5 and writes a different X-Forwarded-For each time.
        $send = fn (string $forged, string $password) => $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.5'])
            ->withHeader('X-Forwarded-For', $forged)
            ->post('/staff/login', ['username' => 'pema', 'password' => $password], ['X-Enhance' => '1']);
        for ($i = 0; $i < 5; $i++) {
            $send("198.51.100.$i", 'wrong-password-'.$i);
        }
        $send('198.51.100.99', 'a-long-password-1')->assertSee('Too many tries');
        $this->assertGuest();
    }

    public function test_different_visitors_are_counted_separately(): void
    {
        $login = fn (string $addr) => $this->withServerVariables(['REMOTE_ADDR' => $addr])
            ->post('/staff/login', ['username' => 'someone', 'password' => 'wrong-password'], ['X-Enhance' => '1']);
        for ($i = 0; $i < 6; $i++) {
            $login('203.0.113.5');
        }
        $login('203.0.113.5')->assertSee('Too many tries');
        $login('203.0.113.6')->assertDontSee('Too many tries');
    }

    public function test_changing_your_password_signs_out_your_other_devices(): void
    {
        $user = $this->staff();
        DB::table('sessions')->insert(['id' => 'othersession', 'user_id' => $user->id, 'ip_address' => '1.2.3.4', 'user_agent' => 'x', 'payload' => '', 'last_activity' => time()]);
        $this->actingAs($user)->post('/staff/password', ['password' => 'a-brand-new-pass', 'confirm' => 'a-brand-new-pass'])->assertRedirect('/staff');
        $this->assertDatabaseMissing('sessions', ['id' => 'othersession']);
    }

    public function test_uploads_accept_pictures_only(): void
    {
        \Storage::fake('public');
        $this->actingAs($this->admin());
        $page = Livewire::test(PhotosPage::class);
        $d = $page->get('data');
        $row = array_key_first($d['images']['list']);
        $svg = UploadedFile::fake()->createWithContent('evil.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
        $page->set("data.images.list.$row.upload", [$svg])->call('save');
        $this->assertSame([], \Storage::disk('public')->allFiles('uploads'));
    }

    public function test_every_page_sends_the_security_headers(): void
    {
        $r = $this->get('/');
        $r->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString("object-src 'none'", $r->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("frame-ancestors 'self'", $r->headers->get('Content-Security-Policy'));
    }

    public function test_the_admin_and_staff_areas_are_closed_to_the_wrong_people(): void
    {
        foreach (['/admin', '/admin/company', '/admin/guide', '/admin/photos'] as $path) {
            $this->get($path)->assertRedirect('/admin/login');
        }
        $staff = $this->staff(['role' => 'staff']);
        foreach (['/admin', '/admin/company', '/admin/photos', '/admin/guide'] as $path) {
            $this->actingAs($staff)->get($path)->assertForbidden();
        }
        foreach (['/staff/team', '/staff/telegram'] as $path) {
            $this->actingAs($staff)->get($path)->assertRedirect('/staff');
        }
        $this->actingAs($staff)->post('/staff/team', ['full_name' => 'X', 'username' => 'hacker', 'password' => 'a-long-password-1', 'role' => 'admin'])->assertRedirect('/staff');
        $this->assertDatabaseMissing('users', ['username' => 'hacker']);
    }

    public function test_the_media_route_serves_only_pictures_from_the_uploads_folder(): void
    {
        \Storage::fake('public');
        \Storage::disk('public')->put('uploads/a.svg', '<svg><script>alert(1)</script></svg>');
        \Storage::disk('public')->put('secret.txt', 'secret');
        foreach (['/media/uploads/a.svg', '/media/secret.txt', '/media/uploads/../secret.txt', '/media/..%2Fsecret.txt', '/media/uploads/%2e%2e/secret.txt'] as $p) {
            $this->get($p)->assertNotFound();
        }
    }
}
