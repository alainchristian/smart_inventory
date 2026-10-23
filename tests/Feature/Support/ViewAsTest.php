<?php

namespace Tests\Feature\Support;

use App\Models\User;
use App\Support\ViewAs;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Platform support can look at another account's screens and cannot change a thing. */
class ViewAsTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role, array $extra = []): User
    {
        return User::forceCreate(array_merge([
            'name' => ucfirst($role) . ' ' . uniqid(), 'email' => $role . uniqid() . '@example.test',
            'password' => bcrypt('password-123'), 'role' => $role, 'is_active' => true, 'must_change_password' => false,
        ], $extra));
    }

    /** Signed in as support, past the two-step check (that has its own tests). */
    private function asSupport(User $support): static
    {
        return $this->actingAs($support)->withSession([\App\Http\Middleware\RequireTwoFactor::SESSION_KEY => $support->id]);
    }

    /** Signed in as support, with view-as pointed at $target. */
    private function viewing(User $support, User $target): static
    {
        return $this->asSupport($support)->withSession([ViewAs::SESSION_KEY => $target->id]);
    }

    public function test_start_shows_the_targets_screens_with_a_banner(): void
    {
        $support = $this->user('super_admin');
        $owner   = $this->user('owner', ['name' => 'Olivia Owner']);

        $this->asSupport($support)->post(route('support.view-as.start', $owner->id))
            ->assertRedirect($owner->getDashboardRoute());
        $this->assertSame($owner->id, session(ViewAs::SESSION_KEY));
        $this->assertDatabaseHas('activity_logs', ['action' => 'support_view_as_started', 'entity_id' => $owner->id]);

        $this->viewing($support, $owner)->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Viewing as')
            ->assertSee('Olivia Owner')
            ->assertSee(route('support.view-as.stop'), false);
    }

    public function test_cannot_view_as_inactive_or_support_accounts_or_without_being_support(): void
    {
        $support  = $this->user('super_admin');
        $inactive = $this->user('owner', ['is_active' => false]);
        $other    = $this->user('super_admin');

        $this->asSupport($support)->post(route('support.view-as.start', $inactive->id))->assertNotFound();
        $this->asSupport($support)->post(route('support.view-as.start', $other->id))->assertNotFound();
        $this->actingAs($this->user('owner'))->post(route('support.view-as.start', $inactive->id))->assertForbidden();
    }

    public function test_a_stray_flag_does_nothing_for_anyone_but_support(): void
    {
        $owner  = $this->user('owner');
        $victim = $this->user('shop_manager');

        // An owner whose session somehow carries the flag stays themselves, with no banner
        $this->actingAs($owner)->withSession([ViewAs::SESSION_KEY => $victim->id])
            ->get(route('owner.dashboard'))->assertOk()->assertDontSee('Viewing as');
    }

    public function test_every_write_is_refused_and_nothing_is_saved(): void
    {
        $support = $this->user('super_admin');
        $owner   = $this->user('owner');
        $target  = $this->user('shop_manager', ['name' => 'Before']);

        Route::middleware(['web', 'auth'])->group(function () use ($target) {
            Route::post('/__t/eloquent', fn () => User::whereKey($target->id)->first()->update(['name' => 'After']));
            Route::post('/__t/builder', fn () => User::whereKey($target->id)->update(['name' => 'After']));
            Route::post('/__t/raw', fn () => DB::table('users')->where('id', $target->id)->delete());
            Route::post('/__t/insert', fn () => User::forceCreate(['name' => 'X', 'email' => 'x@example.test', 'password' => 'x', 'role' => 'shop_manager']));
        });

        foreach (['eloquent', 'builder', 'raw', 'insert'] as $kind) {
            // Livewire / JSON callers get a 423 the page turns into a toast
            $json = $this->viewing($support, $owner)->postJson("/__t/{$kind}");
            $this->assertSame(423, $json->getStatusCode(), "{$kind}: got {$json->getStatusCode()} → " . $json->headers->get('Location'));
            $json->assertJson(['view_only' => true]);
            // A plain form post is sent back with the message
            $this->viewing($support, $owner)->post("/__t/{$kind}")
                ->assertRedirect()->assertSessionHas('error');
        }

        $this->assertSame('Before', $target->fresh()->name);
        $this->assertDatabaseHas('users', ['id' => $target->id]);
        $this->assertDatabaseMissing('users', ['email' => 'x@example.test']);
    }

    public function test_request_runs_as_the_target_with_mail_and_queue_off_and_is_rolled_back(): void
    {
        $support = $this->user('super_admin');
        $owner   = $this->user('owner');
        $mail    = config('mail.default');
        $queue   = config('queue.default');
        $seen    = [];

        Route::middleware(['web', 'auth'])->get('/__t/probe', function () use (&$seen, $owner) {
            // cache is the one table that may be written while just looking
            DB::table('cache')->insert(['key' => 'view-as-probe', 'value' => 'x', 'expiration' => time() + 60]);
            $seen = [
                'id'    => Auth::id(),
                'mail'  => config('mail.default'),
                'queue' => config('queue.default'),
                'level' => DB::transactionLevel(),
            ];
            return 'ok';
        });

        $this->viewing($support, $owner)->get('/__t/probe')->assertOk();

        $this->assertSame($owner->id, $seen['id']);
        $this->assertSame('array', $seen['mail']);
        $this->assertSame('null', $seen['queue']);
        $this->assertGreaterThan(1, $seen['level']);           // its own transaction inside the test's
        $this->assertDatabaseMissing('cache', ['key' => 'view-as-probe']);  // rolled back
        $this->assertSame($mail, config('mail.default'));      // restored
        $this->assertSame($queue, config('queue.default'));
        $this->assertFalse(app(ViewAs::class)->active());
    }

    public function test_the_support_console_is_never_impersonated_and_leaves_the_mode(): void
    {
        $support = $this->user('super_admin');
        $owner   = $this->user('owner');

        $this->viewing($support, $owner)->get(route('support.accounts'))->assertOk()->assertDontSee('Viewing as');
        $this->assertNull(session(ViewAs::SESSION_KEY));

        // Back on an operational page the support account is itself again: forbidden
        $this->asSupport($support)->get(route('owner.dashboard'))->assertForbidden();
    }

    public function test_exit_clears_the_mode_and_is_audited(): void
    {
        $support = $this->user('super_admin');
        $owner   = $this->user('owner');

        $this->viewing($support, $owner)->post(route('support.view-as.stop'))
            ->assertRedirect(route('support.accounts'));
        $this->assertNull(session(ViewAs::SESSION_KEY));
        $this->assertDatabaseHas('activity_logs', ['action' => 'support_view_as_stopped', 'entity_id' => $owner->id]);
    }

    public function test_deactivated_target_ends_the_mode(): void
    {
        $support = $this->user('super_admin');
        $owner   = $this->user('owner', ['is_active' => false]);

        $this->viewing($support, $owner)->get(route('owner.dashboard'))->assertRedirect(route('support.accounts'));
        $this->assertNull(session(ViewAs::SESSION_KEY));
    }

    /**
     * Pages must render the same when viewed as when the person opens them
     * themselves, i.e. nothing that merely looks at a page writes to the
     * database (that would be refused here with a 423).
     */
    public function test_looking_at_pages_never_trips_the_write_block(): void
    {
        $support = $this->user('super_admin');

        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($r) => in_array('GET', $r->methods(), true) && $r->getName())
            ->filter(fn ($r) => preg_match('/^(owner|shop|warehouse)\./', $r->getName()))
            ->reject(fn ($r) => str_contains($r->uri(), '{'))
            ->reject(fn ($r) => preg_match('#/(print|pdf|export)#', $r->uri()))
            ->values();

        $this->assertGreaterThan(40, $routes->count());

        $blocked = [];
        foreach (['owner', 'shop_manager', 'warehouse_manager'] as $role) {
            $target = $this->user($role);
            foreach ($routes as $route) {
                $status = $this->viewing($support, $target)->get('/' . $route->uri())->getStatusCode();
                if ($status === 423) {
                    $blocked[] = "{$role} {$route->getName()}";
                }
            }
        }

        $this->assertSame([], $blocked, 'These pages write while being looked at: ' . implode(', ', $blocked));
    }
}
