<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_pages_carry_the_hardening_headers(): void
    {
        $r = $this->get('/login');

        $r->assertOk();
        $r->assertHeader('X-Content-Type-Options', 'nosniff');
        $r->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $r->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString("frame-ancestors 'self'", $r->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString('camera=(self)', $r->headers->get('Permissions-Policy'));
    }

    public function test_api_responses_and_https_get_them_too(): void
    {
        $this->postJson('/api/scanner/connect', ['session_code' => 'x'])->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->get('https://localhost/login')->assertHeader('Strict-Transport-Security');
    }

    public function test_the_removed_test_pages_are_gone(): void
    {
        $this->get('/qr-test')->assertNotFound();
        $this->get('/test-transporters')->assertNotFound();
    }
}
