<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Turning an account off, or changing its role, must take effect on the pages
 * the person already has open. Route middleware doesn't run on Livewire's
 * update calls by itself, so these go through real HTTP, page then update.
 */
class SessionRevocationTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        return User::forceCreate([
            'name' => ucfirst($role), 'email' => $role . uniqid() . '@example.test', 'password' => bcrypt('password-123'),
            'role' => $role, 'is_active' => true, 'must_change_password' => false,
        ]);
    }

    /** The Livewire snapshot (JSON string) of the first component on the page whose name contains $needle. */
    private function snapshotOf(string $html, string $needle): string
    {
        preg_match_all('/wire:snapshot="([^"]+)"/', $html, $m);
        foreach ($m[1] as $encoded) {
            $json = html_entity_decode($encoded, ENT_QUOTES);
            $data = json_decode($json, true);
            if (str_contains($data['memo']['name'] ?? '', $needle)) {
                return $json;
            }
        }

        $this->fail("No Livewire component matching [$needle] on the page.");
    }

    private function livewireUpdate(string $snapshot)
    {
        return $this->postJson('/livewire/update', [
            'components' => [['snapshot' => $snapshot, 'updates' => (object) [], 'calls' => []]],
        ], ['X-Livewire' => 'true']);
    }

    public function test_deactivated_user_is_signed_out_on_the_next_page_request(): void
    {
        $user = $this->user('shop_manager');
        $this->actingAs($user)->get(route('password.change'))->assertOk();

        $user->forceFill(['is_active' => false])->save();

        $this->get(route('password.change'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_deactivated_user_is_signed_out_on_a_livewire_update(): void
    {
        $owner = $this->user('owner');
        $html = $this->actingAs($owner)->get(route('owner.users.index'))->assertOk()->getContent();
        $snapshot = $this->snapshotOf($html, 'user-list');

        // Control: an active owner's update goes through.
        $this->livewireUpdate($snapshot)->assertOk();

        $owner->forceFill(['is_active' => false])->save();

        $this->livewireUpdate($snapshot)->assertStatus(419);
        $this->assertGuest();
    }

    public function test_demoted_user_cannot_keep_using_an_owner_page_that_is_open(): void
    {
        $owner = $this->user('owner');
        $html = $this->actingAs($owner)->get(route('owner.users.index'))->assertOk()->getContent();
        $snapshot = $this->snapshotOf($html, 'user-list');

        $this->livewireUpdate($snapshot)->assertOk();

        // Same person, now only a shop manager. Their open owner page must stop working.
        $owner->forceFill(['role' => 'shop_manager'])->save();

        $response = $this->livewireUpdate($snapshot);
        $this->assertNotSame(200, $response->getStatusCode(), 'A demoted owner could still use an open owner page.');
    }
}
