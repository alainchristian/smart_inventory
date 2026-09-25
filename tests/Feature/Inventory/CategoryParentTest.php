<?php

namespace Tests\Feature\Inventory;

use App\Livewire\Owner\Categories\CategoryManager;
use App\Models\Category;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

/** Owner → Product Categories: choosing a parent category. */
class CategoryParentTest extends TestCase
{
    use DatabaseTransactions;

    private User $owner;
    private string $u;

    protected function setUp(): void
    {
        parent::setUp();
        $this->u = substr(uniqid(), -7);
        $this->owner = User::forceCreate([
            'name' => 'Owner', 'email' => 'o' . uniqid() . '@example.test', 'password' => 'x',
            'role' => 'owner', 'is_active' => true, 'must_change_password' => false,
        ]);
    }

    private function cat(array $attrs): Category
    {
        return Category::create($attrs + ['code' => 'T' . substr(uniqid(), -9) . random_int(10, 99)]);
    }

    public function test_owner_can_create_a_sub_category_and_it_counts_for_shops(): void
    {
        $parent = $this->cat(['name' => "Footwear {$this->u}", 'is_active' => true]);

        Livewire::actingAs($this->owner)->test(CategoryManager::class)
            ->call('openCreate')
            ->assertSee("Footwear {$this->u}")
            ->set('form_name', "Sandals {$this->u}")
            ->set('form_parent_id', (string) $parent->id)
            ->call('save')
            ->assertHasNoErrors();

        $child = Category::where('name', "Sandals {$this->u}")->firstOrFail();
        $this->assertSame($parent->id, $child->parent_id);
        $this->assertStringStartsWith('SANDALS', $child->code, 'blank Short Code is derived from the name');

        $shop = Shop::forceCreate(['name' => 'S', 'code' => 'S' . $this->u, 'is_active' => true, 'sells_all_categories' => false]);
        $shop->categories()->sync([$parent->id]);
        $this->assertTrue($shop->sellsCategory($child->id));
    }

    public function test_cannot_create_a_loop(): void
    {
        $a = $this->cat(['name' => "A {$this->u}", 'is_active' => true]);
        $b = $this->cat(['name' => "B {$this->u}", 'parent_id' => $a->id, 'is_active' => true]);

        $lw = Livewire::actingAs($this->owner)->test(CategoryManager::class)
            ->call('openEdit', $a->id);

        // A's own sub-category isn't offered as its parent
        $this->assertFalse($lw->instance()->parentOptions->pluck('id')->contains($b->id));

        $lw->set('form_parent_id', (string) $b->id)->call('save')->assertHasErrors('form_parent_id');
        $this->assertNull($a->fresh()->parent_id);
    }

    public function test_cannot_delete_a_category_with_sub_categories(): void
    {
        $a = $this->cat(['name' => "A {$this->u}", 'is_active' => true]);
        $this->cat(['name' => "B {$this->u}", 'parent_id' => $a->id, 'is_active' => true]);

        Livewire::actingAs($this->owner)->test(CategoryManager::class)
            ->call('confirmDelete', $a->id)
            ->call('deleteCategory');

        $this->assertNotNull(Category::find($a->id));
    }

    public function test_list_shows_sub_categories_under_their_parent(): void
    {
        $a = $this->cat(['name' => "Aaa {$this->u}", 'is_active' => true]);
        $this->cat(['name' => "Zzz {$this->u}", 'is_active' => true]);
        $this->cat(['name' => "Mmm {$this->u}", 'parent_id' => $a->id, 'is_active' => true]);

        Livewire::actingAs($this->owner)->test(CategoryManager::class)
            ->set('search', $this->u)
            ->assertSeeInOrder(["Aaa {$this->u}", "Mmm {$this->u}", "Zzz {$this->u}"])
            ->assertSee('1 sub-category');
    }
}
