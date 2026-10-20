<?php

namespace Tests\Feature;

use App\Mail\PlainMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class TrackingTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    public function test_tracking_number_alone_shows_the_timeline_and_nothing_private(): void
    {
        $client = $this->client(['full_name' => 'Secret Person', 'phone' => '+97517123456']);
        $s = $this->shipment($client, ['goods' => 'secret goods']);
        $s->status = 'documents_checked';
        $s->save();
        $s->events()->latest('id')->first()->update(['note' => 'All good']);

        $res = $this->post('/track', ['tracking' => strtolower($s->tracking_number)], ['X-Enhance' => '1'])->assertOk();
        $res->assertSee($s->tracking_number)->assertSee('Documents checked')->assertSee('All good')->assertSee('Request received');
        $res->assertDontSee('Secret Person')->assertDontSee('secret goods')->assertDontSee('17123456');
    }

    public function test_hidden_events_are_not_shown(): void
    {
        $s = $this->shipment();
        $s->events()->create(['status' => 'on_hold', 'note' => 'internal only', 'visible_to_client' => false]);
        $this->post('/track', ['tracking' => $s->tracking_number], ['X-Enhance' => '1'])->assertDontSee('internal only');
    }

    public function test_new_numbers_have_six_characters(): void
    {
        $this->assertMatchesRegularExpression('/^TD-\d{4}-[2-9A-HJKMNP-Z]{6}$/', $this->shipment()->tracking_number);
    }

    public function test_link_from_an_email_opens_the_status(): void
    {
        $s = $this->shipment();
        $this->get('/track?n='.$s->tracking_number)->assertOk()->assertSee('Request received');
    }

    public function test_wrong_and_malformed_numbers_get_a_plain_message(): void
    {
        $this->post('/track', ['tracking' => 'TD-2610-ZZZZZZ'], ['X-Enhance' => '1'])->assertSee("We couldn't find a shipment");
        $this->post('/track', ['tracking' => 'nonsense'], ['X-Enhance' => '1'])->assertSee('Enter a tracking number like');
    }

    public function test_lookups_are_rate_limited_per_visitor(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->post('/track', ['tracking' => 'TD-2610-ZZZZZZ'], ['X-Enhance' => '1'])->assertSee("couldn't find");
        }
        $this->post('/track', ['tracking' => 'TD-2610-ZZZZZZ'], ['X-Enhance' => '1'])->assertSee('Too many lookups');
    }

    public function test_reminder_sends_tracking_numbers_to_the_email_on_file_and_shows_nothing(): void
    {
        config(['mail.default' => 'smtp']);
        Mail::fake();
        $client = $this->client(['email' => 'karma@example.com', 'phone' => '+97517975757']);
        $a = $this->shipment($client);
        $b = $this->shipment($client);

        $res = $this->post('/track/reminder', ['contact' => '17975757'], ['X-Enhance' => '1'])->assertOk();
        $res->assertSee('If that phone number or email belongs to one of our clients');
        $res->assertDontSee('karma@example.com')->assertDontSee($a->tracking_number);

        Mail::assertSent(PlainMessage::class, 1);
        $this->assertDatabaseHas('notification_log', ['client_id' => $client->id, 'kind' => 'tracking_reminder', 'channel' => 'email', 'status' => 'sent']);
    }

    public function test_reminder_by_email_works_and_an_unknown_contact_gets_the_same_answer_and_no_email(): void
    {
        config(['mail.default' => 'smtp']);
        Mail::fake();
        $client = $this->client(['email' => 'karma@example.com']);
        $this->shipment($client);

        $known = $this->post('/track/reminder', ['contact' => 'KARMA@example.com'], ['X-Enhance' => '1'])->getContent();
        $unknown = $this->post('/track/reminder', ['contact' => 'nobody@example.com'], ['X-Enhance' => '1'])->getContent();
        Mail::assertSent(PlainMessage::class, 1);
        $strip = fn ($html) => preg_replace('/(value|data-reveal|name)="[^"]*"|_token[^>]*>/', '', $html);
        $this->assertSame($strip($known), $strip(preg_replace('/nobody@example\.com/', 'karma@example.com', $unknown)));
    }

    public function test_reminder_is_sent_even_if_the_client_switched_notifications_off(): void
    {
        config(['mail.default' => 'smtp']);
        Mail::fake();
        $client = $this->client(['email' => 'karma@example.com', 'notify_email' => false]);
        $this->shipment($client);
        $this->post('/track/reminder', ['contact' => 'karma@example.com'], ['X-Enhance' => '1']);
        Mail::assertSent(PlainMessage::class, 1);
    }

    public function test_reminder_validates_the_input_and_is_rate_limited_per_contact(): void
    {
        $this->post('/track/reminder', ['contact' => ''], ['X-Enhance' => '1'])->assertSee('Enter the phone number or email you gave us.');
        $this->post('/track/reminder', ['contact' => 'abc'], ['X-Enhance' => '1'])->assertSee('Enter a phone number like');
        for ($i = 0; $i < 3; $i++) {
            $this->post('/track/reminder', ['contact' => 'karma@example.com'], ['X-Enhance' => '1'])->assertSee('If that phone number');
        }
        $this->post('/track/reminder', ['contact' => 'karma@example.com'], ['X-Enhance' => '1'])->assertSee("We've sent a few of these already");
    }

    public function test_a_client_without_shipments_gets_nothing(): void
    {
        config(['mail.default' => 'smtp']);
        Mail::fake();
        $this->client(['email' => 'karma@example.com']);
        $this->post('/track/reminder', ['contact' => 'karma@example.com'], ['X-Enhance' => '1']);
        Mail::assertNothingSent();
    }
}
