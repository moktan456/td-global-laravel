<?php

namespace Tests\Feature;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class StaffPolishTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    public function test_an_empty_system_invites_the_first_request_and_the_first_client(): void
    {
        $this->actingAs($this->staff());
        $this->get('/staff')->assertOk()->assertSee('No shipments yet')->assertSee('/staff/shipments/new', false)->assertDontSee('Search shipments');
        $this->get('/staff/clients')->assertOk()->assertSee('No clients yet')->assertSee('Register a new client');
    }

    public function test_the_register_form_starts_open_only_while_there_are_no_clients(): void
    {
        $this->actingAs($this->staff());
        preg_match('/<details id="register-client"[^>]*>/', $this->get('/staff/clients')->getContent(), $m);
        $this->assertStringContainsString(' open', $m[0]);
    }

    public function test_a_search_with_no_matches_is_still_a_no_match_not_a_first_run(): void
    {
        $this->shipment();
        $this->actingAs($this->staff())->get('/staff?q=zzzz')->assertOk()->assertSee('No shipments match')->assertDontSee('No shipments yet');
    }

    public function test_an_unknown_address_inside_the_workspace_gets_a_workspace_page(): void
    {
        $html = $this->actingAs($this->staff())->get('/staff/shipments/TD-9999-NOPE00')->assertNotFound()->assertSee("We couldn't find that page", false)->getContent();
        $this->assertStringContainsString('Staff workspace', $html);
        $this->assertStringNotContainsString('Request a quote', $html, 'not the public site\'s frame');
        $this->actingAs($this->staff())->get('/staff/clients/99999')->assertNotFound()->assertSee("We couldn't find that page", false);
    }

    public function test_the_public_site_keeps_its_own_404(): void
    {
        $this->actingAs($this->staff())->get('/no-such-page')->assertNotFound()->assertDontSee("We couldn't find that page", false);
    }

    public function test_an_expired_page_sends_staff_to_sign_in_with_a_message(): void
    {
        $request = Request::create('/staff/clients', 'POST');
        $request->setLaravelSession($this->app['session.store']);
        $response = app(ExceptionHandler::class)->render($request, new TokenMismatchException('CSRF token mismatch.'));

        $this->assertTrue($response->isRedirect(url('/staff/login')) || $response->headers->get('Location') === url('/staff/login') || str_ends_with((string) $response->headers->get('Location'), '/staff/login'));
        $this->assertSame('Your session ended. Please sign in again.', $this->app['session.store']->get('forms.login')['message'] ?? null);
        $this->get('/staff/login')->assertOk();
    }

    public function test_list_rows_name_their_columns_for_screen_readers(): void
    {
        $this->shipment($this->client(['company' => 'Druk Motors']));
        $html = $this->actingAs($this->staff())->get('/staff')->getContent();
        foreach (['Client: ', 'Goods: ', 'Stage: ', 'Updated '] as $label) {
            $this->assertStringContainsString('<span class="sr-only">'.$label.'</span>', $html);
        }
    }

    public function test_the_old_pill_menu_component_is_gone(): void
    {
        $this->assertFileDoesNotExist(resource_path('views/components/staff-nav.blade.php'));
    }
}
