<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\ChangePassword;
use App\Models\User;
use App\Support\PasswordPolicy;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;
use Tests\TestCase;

/** Passwords must be at least 12 characters wherever one is chosen. */
class PasswordPolicyTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role = 'shop_manager', array $extra = []): User
    {
        return User::forceCreate(array_merge([
            'name' => ucfirst($role), 'email' => $role . uniqid() . '@example.test', 'password' => bcrypt('old-password-123'),
            'role' => $role, 'is_active' => true, 'must_change_password' => true,
        ], $extra));
    }

    public function test_the_minimum_is_twelve(): void
    {
        $this->assertSame(12, PasswordPolicy::MIN_LENGTH);
        $this->assertSame('min:12', PasswordPolicy::rule());
    }

    public function test_forced_password_change_refuses_eleven_characters_and_accepts_twelve(): void
    {
        $user = $this->user();

        Livewire::actingAs($user)->test(ChangePassword::class)
            ->set('password', 'elevenchars')->set('password_confirmation', 'elevenchars')
            ->call('save')->assertHasErrors(['password']);
        $this->assertTrue(Hash::check('old-password-123', $user->fresh()->password));

        Livewire::actingAs($user)->test(ChangePassword::class)
            ->set('password', 'twelve-chars')->set('password_confirmation', 'twelve-chars')
            ->call('save')->assertHasNoErrors();
        $this->assertTrue(Hash::check('twelve-chars', $user->fresh()->password));
    }

    public function test_the_framework_default_rule_used_by_reset_and_profile_forms_is_twelve(): void
    {
        $validator = validator(['p' => 'elevenchars'], ['p' => [Password::defaults()]]);
        $this->assertTrue($validator->fails());

        $this->assertFalse(validator(['p' => 'twelve-chars'], ['p' => [Password::defaults()]])->fails());
    }

    public function test_owner_cannot_create_a_user_with_a_short_password(): void
    {
        $owner = $this->user('owner', ['must_change_password' => false]);

        Livewire::actingAs($owner)->test(\App\Livewire\Owner\Users\UserList::class)
            ->set('form_name', 'New Person')->set('form_email', 'new' . uniqid() . '@example.test')
            ->set('form_role', 'owner')->set('form_password', 'short-pass1')
            ->call('save')->assertHasErrors(['form_password']);
    }
}
