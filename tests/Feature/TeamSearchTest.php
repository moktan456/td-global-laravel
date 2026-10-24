<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class TeamSearchTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    private function person(string $name, string $username, array $attrs = [])
    {
        return $this->staff($attrs + ['name' => $name, 'username' => $username, 'email' => "{$username}@staff.td-global.invalid"]);
    }

    public function test_search_finds_people_by_name_or_username(): void
    {
        $this->person('Zangmo Tester', 'zangmo.t');
        $this->person('Karma Lhamo', 'karma.l');
        $this->actingAs($this->admin(['name' => 'Boss Admin', 'username' => 'boss']));

        $this->get('/staff/team?q=zangmo')->assertOk()->assertSee('Zangmo Tester')->assertDontSee('Karma Lhamo')->assertSee('1 person matches');
        $this->get('/staff/team?q=KARMA.L')->assertSee('Karma Lhamo')->assertDontSee('Zangmo Tester');
        $this->get('/staff/team?q='.urlencode('tester zangmo'))->assertSee('Zangmo Tester');   // every word, any order
        $this->get('/staff/team?q=zzz')->assertSee('No one matches')->assertSee('Clear');
    }

    public function test_role_and_status_filters(): void
    {
        $this->person('Zangmo Tester', 'zangmo.t');
        $this->person('Switched Off', 'off.person', ['active' => false]);
        $this->actingAs($this->admin(['name' => 'Boss Admin', 'username' => 'boss']));

        $this->get('/staff/team?role=admin')->assertSee('>boss</p>', false)->assertDontSee('Zangmo Tester');
        $this->get('/staff/team?role=staff')->assertSee('Zangmo Tester')->assertDontSee('>boss</p>', false);
        $this->get('/staff/team?access=off')->assertSee('Switched Off')->assertDontSee('Zangmo Tester')->assertDontSee('>boss</p>', false);
        $this->get('/staff/team?access=active&role=staff')->assertSee('Zangmo Tester')->assertDontSee('Switched Off');
    }

    public function test_the_team_is_paged_ten_at_a_time_and_the_links_keep_the_search(): void
    {
        for ($i = 1; $i <= 14; $i++) {
            $this->person(sprintf('Member %02d', $i), sprintf('member%02d', $i));
        }
        $this->actingAs($this->admin(['name' => 'Boss Admin', 'username' => 'boss']));

        $this->get('/staff/team')->assertSee('Everyone (15)')->assertSee('Showing 1–10 of 15')->assertSee('Page 1 of 2');
        $this->get('/staff/team?page=2')->assertSee('Showing 11–15 of 15')->assertSee('Member 14');
        $html = html_entity_decode($this->get('/staff/team?q=member&role=staff')->getContent());
        $this->assertMatchesRegularExpression('#href="[^"]*q=member[^"]*page=2#', $html);
        $this->get('/staff/team?page=9')->assertRedirect('/staff/team?page=2');
    }

    public function test_junk_in_the_query_string_is_ignored_and_staff_still_cannot_open_the_page(): void
    {
        $this->actingAs($this->admin());
        $this->get('/staff/team?q[]=x&role=evil&access[]=y&page=abc')->assertOk();

        $this->actingAs($this->staff())->get('/staff/team')->assertRedirect('/staff');
    }

    public function test_the_company_is_the_link_in_the_client_list_and_the_person_is_the_link_when_there_is_no_company(): void
    {
        $with = $this->client(['full_name' => 'Tashi Wangmo', 'company' => 'Druk Motors']);
        $without = $this->client(['full_name' => 'Pema Choden', 'company' => null]);
        $html = $this->actingAs($this->staff())->get('/staff/clients')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<a href="/staff/clients/'.$with->id.'"[^>]*>Druk Motors</a>#', $html);
        $this->assertDoesNotMatchRegularExpression('#<a href="/staff/clients/'.$with->id.'"[^>]*>Tashi Wangmo</a>#', $html);
        $this->assertStringContainsString('Tashi Wangmo', $html, 'the person is still shown, under the company');
        $this->assertMatchesRegularExpression('#<a href="/staff/clients/'.$without->id.'"[^>]*>Pema Choden</a>#', $html);
    }
}
