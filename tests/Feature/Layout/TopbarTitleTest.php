<?php

namespace Tests\Feature\Layout;

use App\Livewire\Layout\Topbar;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** The topbar shows the current page's name, not "Dashboard" everywhere. */
class TopbarTitleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_pages_show_their_own_title(): void
    {
        $owner = User::forceCreate([
            'name' => 'Owner', 'email' => 'o' . uniqid() . '@example.test', 'password' => 'x',
            'role' => 'owner', 'is_active' => true, 'must_change_password' => false,
        ]);

        foreach ([
            'owner.products.index'  => 'Products',
            'owner.reports.sales'   => 'Sales Analytics',
            'owner.settings'        => 'Settings',
            'owner.dashboard'       => 'Dashboard',
        ] as $route => $title) {
            $html = $this->actingAs($owner)->get(route($route))->assertOk()->getContent();
            $this->assertMatchesRegularExpression('/data-page-title>\s*' . preg_quote($title, '/') . '\s*</', $html, $route);
        }
    }

    /** Every page that renders inside the app layout needs an entry — add one when you add a page. */
    public function test_every_app_page_has_a_title(): void
    {
        // Printable / file / standalone pages don't render the topbar
        $noTopbar = ['/print', '/pdf', '/export/', 'receipt', 'delivery-note', 'picking-slip', 'picking-list'];

        $missing = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($r) => in_array('GET', $r->methods(), true))
            ->map(fn ($r) => $r->getName())
            ->filter(fn ($n) => $n && preg_match('/^(owner|shop|warehouse)\./', $n))
            ->reject(fn ($n) => collect($noTopbar)->contains(fn ($s) => str_contains(Route::getRoutes()->getByName($n)->uri(), trim($s, '/'))))
            ->reject(fn ($n) => array_key_exists($n, Topbar::TITLES))
            ->values()->all();

        $this->assertSame([], $missing, 'Add these routes to Topbar::TITLES');
    }
}
