<?php

namespace Tests\Feature\Security;

use App\Livewire\Concerns\PinsToOwnLocation;
use App\Livewire\Shop\Dashboard as ShopDashboard;
use App\Livewire\Warehouse\Sales\FulfillmentQueue;
use App\Livewire\Warehouse\Inventory\ReceiveBoxes as WarehouseReceiveBoxes;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use ReflectionClass;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Any public Livewire property can be overwritten from the browser. A shop or
 * warehouse manager's location id is set in mount() from their assignment, so
 * the browser must never be able to change it (it would show or act on another
 * location's data). Ids nobody changes from the page are #[Locked]; the few
 * bound to an owner-only <select> use PinsToOwnLocation.
 */
class LocationTamperingTest extends TestCase
{
    use DatabaseTransactions;

    private int $shopA;
    private int $shopB;
    private int $whA;
    private int $whB;

    protected function setUp(): void
    {
        parent::setUp();

        $u   = uniqid();
        $now = now()->toDateTimeString();
        $code = fn (string $n) => $n . substr($u, -8);
        $shop = fn (string $n) => DB::table('shops')->insertGetId(['name' => "$n $u", 'code' => $code($n), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        $wh   = fn (string $n) => DB::table('warehouses')->insertGetId(['name' => "$n $u", 'code' => $code($n), 'created_at' => $now, 'updated_at' => $now]);

        $this->shopA = $shop('SA');
        $this->shopB = $shop('SB');
        $this->whA   = $wh('WA');
        $this->whB   = $wh('WB');
    }

    private function user(string $role, ?string $type = null, ?int $locationId = null): User
    {
        return User::forceCreate([
            'name' => ucfirst($role), 'email' => $role . uniqid() . '@example.test', 'password' => bcrypt('password-123'),
            'role' => $role, 'location_type' => $type, 'location_id' => $locationId,
            'is_active' => true, 'must_change_password' => false,
        ]);
    }

    public function test_shop_manager_cannot_switch_the_dashboard_to_another_shop(): void
    {
        $manager = $this->user('shop_manager', 'shop', $this->shopA);

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::actingAs($manager)->test(ShopDashboard::class)->set('shopId', $this->shopB);
    }

    public function test_warehouse_manager_cannot_switch_fulfillment_to_another_warehouse(): void
    {
        $manager = $this->user('warehouse_manager', 'warehouse', $this->whA);

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::actingAs($manager)->test(FulfillmentQueue::class)->set('warehouseId', $this->whB);
    }

    public function test_warehouse_manager_cannot_pick_another_warehouse_in_the_select(): void
    {
        $manager = $this->user('warehouse_manager', 'warehouse', $this->whA);

        $component = Livewire::actingAs($manager)->test(WarehouseReceiveBoxes::class);
        $this->assertSame($this->whA, $component->get('warehouseId'));

        // The abort may surface as an exception or as an error response (the
        // harness then has no state); either way the new value must not stick.
        try {
            $component->set('warehouseId', $this->whB);
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertNotSame($this->whB, $component->get('warehouseId'), 'A warehouse manager switched warehouse.');
    }

    public function test_owner_can_still_pick_any_warehouse(): void
    {
        $owner = $this->user('owner');

        Livewire::actingAs($owner)->test(WarehouseReceiveBoxes::class)
            ->set('warehouseId', $this->whB)
            ->assertSet('warehouseId', $this->whB);
    }

    /**
     * Guard for new code: every public property that holds a shop / warehouse /
     * session id must be #[Locked], or be on the list below with the reason it
     * is safe. Add a new component here only after checking it scopes by the
     * signed-in user on the server.
     */
    public function test_location_id_properties_are_locked_or_explicitly_allowed(): void
    {
        $watched = ['shopId', 'warehouseId', 'locationId', 'sessionId', 'shop_id', 'warehouse_id'];

        // class basename => reason
        $allowed = [
            // Orphaned: no route or view references these. If one is ever routed, lock or pin it first.
            'App\Livewire\Inventory\Boxes\ReceiveBoxes'   => 'orphaned (no route or view uses it)',
            'App\Livewire\Inventory\Transfers\TransferList' => 'orphaned (no route or view uses it)',
            'App\Livewire\Sales\PointOfSale'              => 'orphaned (no route or view uses it)',
            'App\Livewire\Shop\Sales\PointOfSale'         => 'orphaned (no route or view uses it)',
            'OpeningCredits'   => 'owner-only form field (a shop to lend from)',
            'SessionHistory'   => 'scopedShopId() ignores it for shop managers',
            'StockReturns'     => 'reset to the manager\'s own shop on every request',
            'BoxList'          => 'owner-side filter, results are not shop-scoped data of another tenant',
            'DamagedGoodsList' => 'filter; the list is scoped by the signed-in user',
            'ProductList'      => 'filter over the shared catalogue (owner / product pages)',
        ];

        $offenders = [];
        $base = app_path('Livewire');
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base));

        foreach ($it as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $rel   = str_replace(['/', '.php'], ['\\', ''], substr($file->getPathname(), strlen($base) + 1));
            $class = 'App\\Livewire\\' . str_replace('\\', '\\', str_replace(DIRECTORY_SEPARATOR, '\\', $rel));
            if (! class_exists($class)) {
                continue;
            }

            $ref = new ReflectionClass($class);
            if ($ref->isAbstract() || $ref->isTrait() || ! $ref->isSubclassOf(\Livewire\Component::class)) {
                continue;
            }

            foreach ($ref->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
                if ($prop->isStatic() || ! in_array($prop->getName(), $watched, true)) {
                    continue;
                }
                if ($prop->getAttributes(Locked::class)) {
                    continue;
                }
                if (in_array(PinsToOwnLocation::class, class_uses_recursive($class), true)) {
                    continue;
                }
                if (isset($allowed[$class]) || isset($allowed[$ref->getShortName()])) {
                    continue;
                }

                $offenders[] = $class . '::$' . $prop->getName();
            }
        }

        $this->assertSame([], $offenders, "Unlocked location id properties (lock them, or pin them, or allow with a reason):\n" . implode("\n", $offenders));
    }
}
