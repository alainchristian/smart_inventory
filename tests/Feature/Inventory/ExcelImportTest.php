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

    protected function setUp(): void
    {
        parent::setUp();
        $u = substr(uniqid(), -8);
        $this->owner = User::forceCreate([
            'name' => 'Owner', 'email' => "xo$u@example.test", 'password' => 'x',
            'role' => 'owner', 'is_active' => true, 'must_change_password' => false,
        ]);
        $this->warehouseId = DB::table('warehouses')->insertGetId(['name' => "WH $u", 'code' => "W$u", 'created_at' => now(), 'updated_at' => now()]);
        $cat = DB::table('categories')->insertGetId(['name' => "Cat $u", 'code' => "C$u", 'created_at' => now(), 'updated_at' => now()]);
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
}
