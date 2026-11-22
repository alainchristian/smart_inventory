<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * There is no public sign-up. Accounts are created by an owner / admin or by
 * platform support, and every creation is audited. An open /register page let
 * a stranger create an unaudited shop_manager account in production
 * (2026-11-19), so this must stay closed.
 */
class RegistrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_registration_page_does_not_exist(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_registration_cannot_be_posted_to(): void
    {
        $before = User::count();

        $this->post('/register', [
            'name' => 'Stranger',
            'email' => 'stranger@example.com',
            'password' => 'a-long-password-123',
            'password_confirmation' => 'a-long-password-123',
        ])->assertStatus(404);

        $this->assertSame($before, User::count());
        $this->assertGuest();
    }

    public function test_no_route_allows_self_registration(): void
    {
        $this->assertFalse(Route::has('register'), 'A public register route exists again.');
    }
}
