<?php

namespace Tests\Feature\Auth;

use App\Livewire\Forms\LoginForm;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt;
use Tests\TestCase;

/**
 * Guessing one account's password from many addresses: the per-email + IP limit
 * gives every new address 5 free tries, so there is also a cap per email.
 */
class LoginThrottleTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::forceCreate([
            'name' => 'Throttle Test', 'email' => 'throttle' . uniqid() . '@example.test', 'password' => bcrypt('a-long-password-123'),
            'role' => 'shop_manager', 'is_active' => true, 'must_change_password' => false,
        ]);

        RateLimiter::clear(LoginForm::emailThrottleKey($this->user->email));
    }

    private function attempt(string $email, string $password)
    {
        return Volt::test('pages.auth.login')->set('form.email', $email)->set('form.password', $password)->call('login');
    }

    public function test_wrong_passwords_are_counted_per_email_regardless_of_address(): void
    {
        $key = LoginForm::emailThrottleKey($this->user->email);

        $this->attempt($this->user->email, 'wrong-1');
        $this->attempt($this->user->email, 'wrong-2');

        $this->assertSame(2, RateLimiter::attempts($key));
        // Case and spacing in the typed email don't make a fresh counter.
        $this->assertSame($key, LoginForm::emailThrottleKey('  ' . strtoupper($this->user->email) . ' '));
    }

    public function test_after_the_cap_even_the_right_password_is_refused_from_a_fresh_address(): void
    {
        $key = LoginForm::emailThrottleKey($this->user->email);

        // Ten failures already recorded for this email (from ten different addresses).
        for ($i = 0; $i < LoginForm::MAX_FAILS_PER_EMAIL; $i++) {
            RateLimiter::hit($key, LoginForm::EMAIL_LOCK_SECONDS);
        }

        // This address has never failed (its own email+IP limit is clear).
        $this->attempt($this->user->email, 'a-long-password-123')->assertHasErrors(['form.email']);
        $this->assertGuest();
    }

    public function test_other_accounts_are_not_affected_by_one_accounts_lockout(): void
    {
        $key = LoginForm::emailThrottleKey($this->user->email);
        for ($i = 0; $i < LoginForm::MAX_FAILS_PER_EMAIL; $i++) {
            RateLimiter::hit($key, LoginForm::EMAIL_LOCK_SECONDS);
        }

        $other = User::forceCreate([
            'name' => 'Other', 'email' => 'other' . uniqid() . '@example.test', 'password' => bcrypt('another-long-pass-1'),
            'role' => 'shop_manager', 'is_active' => true, 'must_change_password' => false,
        ]);

        $this->attempt($other->email, 'another-long-pass-1')->assertHasNoErrors();
        $this->assertAuthenticatedAs($other);
    }

    public function test_a_successful_sign_in_clears_the_count(): void
    {
        $key = LoginForm::emailThrottleKey($this->user->email);

        $this->attempt($this->user->email, 'wrong');
        $this->assertSame(1, RateLimiter::attempts($key));

        $this->attempt($this->user->email, 'a-long-password-123')->assertHasNoErrors();
        $this->assertSame(0, RateLimiter::attempts($key));
    }
}
