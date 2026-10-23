<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class ClientSearchTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    private function signIn(): void
    {
        $this->actingAs($this->staff());
    }

    public function test_the_list_is_paged_ten_at_a_time_newest_first(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->client(['full_name' => sprintf('Person %02d', $i)]);
        }
        $this->signIn();

        $first = $this->get('/staff/clients')->assertOk()->assertSee('Clients (25)')->assertSee('Showing 1–10 of 25')->assertSee('Page 1 of 3');
        $first->assertSee('Person 25')->assertSee('Person 16')->assertDontSee('Person 15');

        $this->get('/staff/clients?page=3')->assertOk()->assertSee('Showing 21–25 of 25')->assertSee('Person 05')->assertDontSee('Person 06');
    }

    public function test_a_page_that_does_not_exist_goes_to_the_last_one(): void
    {
        $this->client();
        $this->signIn();
        $this->get('/staff/clients?page=9')->assertRedirect('/staff/clients?page=1');
        $this->get('/staff/clients?page=1')->assertOk()->assertSee('Showing 1–1 of 1');
    }

    public function test_search_finds_clients_by_name_company_email_and_phone(): void
    {
        $this->client(['full_name' => 'Ngawang Dorji', 'company' => 'Druk Traders', 'email' => 'nd@example.com', 'phone' => '+97517975757']);
        $this->client(['full_name' => 'Pema Choden', 'company' => 'Gelephu Motors', 'email' => 'pema@motors.bt', 'phone' => '+97577123456']);
        $this->signIn();

        $this->get('/staff/clients?q=ngawang')->assertSee('Ngawang Dorji')->assertDontSee('Pema Choden')->assertSee('1 client matches');
        $this->get('/staff/clients?q=GELEPHU')->assertSee('Pema Choden')->assertDontSee('Ngawang Dorji');
        $this->get('/staff/clients?q=pema@motors')->assertSee('Pema Choden')->assertDontSee('Ngawang Dorji');
        $this->get('/staff/clients?q=17975757')->assertSee('Ngawang Dorji')->assertDontSee('Pema Choden');
        $this->get('/staff/clients?q='.urlencode('+975 17 975757'))->assertSee('Ngawang Dorji');
        $this->get('/staff/clients?q='.urlencode('druk dorji'))->assertSee('Ngawang Dorji')->assertDontSee('Pema Choden');   // every word must match
    }

    public function test_search_with_no_matches_says_so_and_offers_to_clear(): void
    {
        $this->client(['full_name' => 'Ngawang Dorji']);
        $this->signIn();
        $this->get('/staff/clients?q=zzz')->assertOk()->assertSee('No clients match')->assertSee('Clear')->assertDontSee('Ngawang Dorji');
    }

    public function test_percent_and_underscore_are_searched_literally(): void
    {
        $this->client(['full_name' => 'Plain Name']);
        $this->client(['full_name' => '100% Cotton Ltd']);
        $this->signIn();
        $this->get('/staff/clients?q='.urlencode('%'))->assertSee('100% Cotton Ltd')->assertDontSee('Plain Name');
        $this->get('/staff/clients?q='.urlencode('_'))->assertSee('No clients match');
    }

    public function test_letters_do_not_turn_into_a_match_everything_phone_search(): void
    {
        $this->client(['full_name' => 'Ngawang Dorji']);
        $this->client(['full_name' => 'Pema Choden']);
        $this->signIn();
        $this->get('/staff/clients?q=ngawang')->assertDontSee('Pema Choden');
    }

    public function test_telegram_filter_and_search_work_together_and_stay_in_the_page_links(): void
    {
        config(['td.telegram.bot_token' => 'TESTTOKEN', 'td.telegram.bot_username' => 'td_test_bot']);
        for ($i = 0; $i < 12; $i++) {
            $this->client(['full_name' => "Filler {$i}"]);
        }
        $this->client(['full_name' => 'Linked One', 'telegram_chat_id' => 555]);
        $this->client(['full_name' => 'Unlinked One']);   // newest, so it is on page 1
        $this->signIn();

        $this->get('/staff/clients?telegram=connected')->assertSee('Linked One')->assertDontSee('Unlinked One')->assertSee('1 client matches');
        $this->get('/staff/clients?telegram=not')->assertDontSee('Linked One')->assertSee('Unlinked One');
        $this->get('/staff/clients?telegram=connected&q=linked')->assertSee('Linked One');
        // the next-page link keeps the search
        $this->get('/staff/clients?telegram=not&q=one')->assertDontSee('Page 2');
        $html = $this->get('/staff/clients?telegram=not')->getContent();
        $this->assertMatchesRegularExpression('#href="[^"]*telegram=not[^"]*page=2#', html_entity_decode($html));
    }

    public function test_ignores_junk_in_the_query_string(): void
    {
        $this->client();
        $this->signIn();
        $this->get('/staff/clients?q[]=x&telegram=evil&page=abc')->assertOk();
    }
}
