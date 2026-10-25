<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_page_carries_the_protective_headers(): void
    {
        foreach (['/', '/staff/login', '/track'] as $url) {
            $this->get($url)->assertOk()
                ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
                ->assertHeader('X-Content-Type-Options', 'nosniff')
                ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
                ->assertHeader('Permissions-Policy');
        }
        $this->get('/staff')->assertRedirect()->assertHeader('X-Frame-Options', 'SAMEORIGIN');   // redirects too
    }

    public function test_hsts_is_off_until_switched_on_and_only_over_https(): void
    {
        $this->get('https://localhost/')->assertHeaderMissing('Strict-Transport-Security');

        config(['td.hsts' => true]);
        $this->get('https://localhost/')->assertHeader('Strict-Transport-Security', 'max-age=31536000');
        $this->get('http://localhost/')->assertHeaderMissing('Strict-Transport-Security');
    }
}
