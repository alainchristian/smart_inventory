<?php

namespace Tests\Feature\Owner;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The owner dashboard's Owner Actions widget links to these two pages
 * (pending return approvals / damaged goods without a decision). They
 * used to 500 because their views didn't exist.
 */
class OwnerReturnsDamagedPagesTest extends TestCase
{
    use DatabaseTransactions;

    private function owner(): User
    {
        return User::forceCreate([
            'name' => 'Owner', 'email' => 'o' . uniqid() . '@example.test', 'password' => 'x',
            'role' => 'owner', 'is_active' => true, 'must_change_password' => false,
        ]);
    }

    public function test_owner_returns_page_lists_all_shops(): void
    {
        $this->actingAs($this->owner())
            ->get(route('owner.returns.index'))
            ->assertOk()
            ->assertSeeLivewire('shop.returns.return-list');
    }

    public function test_owner_damaged_goods_page_loads(): void
    {
        $this->actingAs($this->owner())
            ->get(route('owner.damaged-goods.index'))
            ->assertOk()
            ->assertSeeLivewire('shop.damaged-goods.damaged-goods-list');
    }

    public function test_routes_without_views_are_gone(): void
    {
        foreach (['owner.users.create', 'owner.users.edit', 'owner.returns.show', 'owner.damaged-goods.show',
                  'shop.sales.show', 'products.index', 'products.show',
                  'warehouse.reports.inventory', 'warehouse.reports.transfers'] as $name) {
            $this->assertFalse(\Illuminate\Support\Facades\Route::has($name), "$name should be removed");
        }
    }
}
