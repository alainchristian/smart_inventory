<?php

namespace Tests\Feature\Inventory;

use App\Livewire\Warehouse\Inventory\ReceiveBoxes;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

/**
 * Receive Stock → Excel import still reads .xlsx / .xls / .csv after the
 * phpoffice/phpspreadsheet security upgrade (1.30.2 → 1.30.7).
 */
class ExcelImportTest extends TestCase
{
    use DatabaseTransactions;

    private User $owner;
    private int $warehouseId;
    private Product $known;
    private string $catName;

    protected function setUp(): void
    {
        parent::setUp();
        $u = substr(uniqid(), -8);
        $this->owner = User::forceCreate([
            'name' => 'Owner', 'email' => "xo$u@example.test", 'password' => 'x',
            'role' => 'owner', 'is_active' => true, 'must_change_password' => false,
        ]);
        $this->warehouseId = DB::table('warehouses')->insertGetId(['name' => "WH $u", 'code' => "W$u", 'created_at' => now(), 'updated_at' => now()]);
        $this->catName = "Cat $u";
        $cat = DB::table('categories')->insertGetId(['name' => $this->catName, 'code' => "C$u", 'created_at' => now(), 'updated_at' => now()]);
        $this->known = Product::forceCreate([
            'sku' => "KNOWN-$u", 'name' => "Known Shoe $u", 'barcode' => '77' . random_int(10000000000, 99999999999),
            'category_id' => $cat, 'items_per_box' => 12, 'purchase_price' => 800, 'selling_price' => 1000,
            'box_selling_price' => 12000, 'is_active' => true, 'low_stock_threshold' => 5, 'reorder_point' => 10,
        ]);
    }

    /** Builds a real spreadsheet file with the upgraded library and returns it as an upload. */
    private function upload(string $ext, string $writer): UploadedFile
    {
        $sheet = new Spreadsheet();
        $sheet->getActiveSheet()->fromArray([
            ['barcode', 'product_name', 'sku', 'category', 'items_per_box', 'box_purchase_price', 'box_selling_price', 'boxes', 'batch_number', 'expiry_date'],
            [$this->known->barcode, $this->known->name, $this->known->sku, '', '12', '9600', '12000', '3', 'B-KNOWN', ''],
            ['8800000000001', 'Brand New Sandal', 'NEW-SAN-1', '', '10', '5000', '7000', '2', '', ''],
        ], null, 'A1', true);

        $path = tempnam(sys_get_temp_dir(), 'xl') . ".$ext";
        IOFactory::createWriter($sheet, $writer)->save($path);

        // Livewire's test uploader needs a testing File; its bytes are the real spreadsheet
        return UploadedFile::fake()->createWithContent("stock.$ext", file_get_contents($path));
    }

    public static function formats(): array
    {
        return ['xlsx' => ['xlsx', 'Xlsx'], 'xls' => ['xls', 'Xls'], 'csv' => ['csv', 'Csv']];
    }

    /** @dataProvider formats */
    public function test_import_preview_reads_the_file(string $ext, string $writer): void
    {
        $c = Livewire::actingAs($this->owner)->test(ReceiveBoxes::class)
            ->set('warehouseId', $this->warehouseId)
            ->set('excelFile', $this->upload($ext, $writer))
            ->call('processExcelFile')
            ->assertHasNoErrors()
            ->assertSet('showExcelPreview', true);

        $recognized = $c->get('excelRecognized');
        $unknown    = $c->get('excelUnknown');
        $this->assertCount(1, $recognized, "$ext: existing product matched by barcode");
        $this->assertSame($this->known->id, $recognized[0]['product_id']);
        $this->assertSame(3, (int) $recognized[0]['boxes']);
        $this->assertCount(1, $unknown, "$ext: new product offered for creation");
        $this->assertSame('Brand New Sandal', $unknown[0]['product_name']);
        $this->assertSame([], $c->get('excelErrors'));
    }

    public function test_several_new_products_without_a_barcode_import_together(): void
    {
        // Used to fail on the second row: an empty barcode was saved as '' and
        // products.barcode is unique ("Key (barcode)=() already exists").
        $sheet = new Spreadsheet();
        $sheet->getActiveSheet()->fromArray([
            ['barcode', 'product_name', 'sku', 'category', 'items_per_box', 'box_purchase_price', 'box_selling_price', 'boxes', 'batch_number', 'expiry_date'],
            ['', 'Plate ' . $this->catName, 'PL-' . substr(md5($this->catName), 0, 8), $this->catName, '24', '28000', '34000', '2', '', ''],
            ['', 'Glass ' . $this->catName, 'GL-' . substr(md5($this->catName), 0, 8), $this->catName, '36', '20160', '27000', '3', '', ''],
        ], null, 'A1', true);
        $path = tempnam(sys_get_temp_dir(), 'xl') . '.xlsx';
        IOFactory::createWriter($sheet, 'Xlsx')->save($path);

        Livewire::actingAs($this->owner)->test(ReceiveBoxes::class)
            ->set('warehouseId', $this->warehouseId)
            ->set('excelFile', UploadedFile::fake()->createWithContent('stock.xlsx', file_get_contents($path)))
            ->call('processExcelFile')
            ->assertSet('showExcelPreview', true)
            ->call('confirmExcelImport');

        $products = Product::whereIn('name', ['Plate ' . $this->catName, 'Glass ' . $this->catName])->get();
        $this->assertCount(2, $products);
        $this->assertSame([null, null], $products->pluck('barcode')->all());
        $this->assertSame(5, DB::table('boxes')->whereIn('product_id', $products->pluck('id'))->count());
    }

    public function test_product_search_finds_products_by_category_and_lists_more_than_eight(): void
    {
        $u   = substr(uniqid(), -6);
        $cat = DB::table('categories')->insertGetId(['name' => "LADIES SHOES $u", 'code' => "LS$u", 'created_at' => now(), 'updated_at' => now()]);
        $shoe = Product::forceCreate([
            'sku' => "ZZ-PUMP-$u", 'name' => "Zz Pump $u", 'category_id' => $cat, 'items_per_box' => 12,
            'purchase_price' => 1, 'selling_price' => 2, 'box_selling_price' => 24, 'is_active' => true,
            'low_stock_threshold' => 1, 'reorder_point' => 1,
        ]);

        $c = Livewire::actingAs($this->owner)->test(ReceiveBoxes::class)
            ->set('productSearch', "ladies shoes $u")
            ->call('performProductSearch');
        $this->assertSame([$shoe->id], array_column($c->get('searchResults'), 'id'), 'found by its category name');

        // Browse list (empty search) isn't cut to 8 products any more
        for ($i = 0; $i < 10; $i++) {
            Product::forceCreate([
                'sku' => "AA-$i-$u", 'name' => "Aa Item $i $u", 'category_id' => $cat, 'items_per_box' => 12,
                'purchase_price' => 1, 'selling_price' => 2, 'box_selling_price' => 24, 'is_active' => true,
                'low_stock_threshold' => 1, 'reorder_point' => 1,
            ]);
        }
        $c->set('productSearch', '')->call('performProductSearch');
        $this->assertGreaterThan(8, count($c->get('searchResults')));
    }
}
