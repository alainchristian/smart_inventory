<?php

namespace App\Livewire\Owner\Credit;

use App\Models\Customer;
use App\Models\OpeningCredit;
use App\Models\Shop;
use App\Services\Sales\CustomerCreditLedger;
use App\Services\Sales\OpeningCreditService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Owner → Finance → Old credit: credit customers owed before the system was
 * in use, typed in one by one or uploaded from Excel. All writes go through
 * OpeningCreditService.
 */
class OpeningCredits extends Component
{
    use WithFileUploads, WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'shop', except: '')]
    public string $shopFilter = '';

    // ── Add one ──────────────────────────────────────────────
    public bool $showForm = false;
    public string $phone = '';
    public string $name = '';
    public string $shopId = '';
    public string $amount = '';
    public string $takenOn = '';
    public string $reference = '';
    public string $notes = '';

    // ── Upload ───────────────────────────────────────────────
    public bool $showImport = false;
    public $excelFile = null;
    /** Preview rows: row, phone, name, shop, amount, taken_on, date_given, errors[], is_new_customer, existing_name */
    public array $preview = [];
    /** Raw rows as read from the sheet, keyed by row number — what gets imported */
    public array $importRows = [];

    // ── Remove ───────────────────────────────────────────────
    public ?int $removingId = null;
    public string $removeReason = '';

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user->isOwner() && ! $user->isAdmin()) {
            abort(403, 'Owner and admin only.');
        }
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingShopFilter(): void { $this->resetPage(); }

    // ── Add one ──────────────────────────────────────────────

    public function openForm(): void
    {
        $this->reset(['phone', 'name', 'amount', 'takenOn', 'reference', 'notes']);
        $this->shopId = $this->shopFilter;
        $this->resetValidation();
        $this->showImport = false;
        $this->showForm   = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetValidation();
    }

    /** The registered customer for the typed phone, with what they owe each shop. */
    #[Computed]
    public function phoneMatch(): ?array
    {
        $phone = OpeningCreditService::normalisePhone($this->phone);
        if (! $phone) {
            return null;
        }
        $customer = Customer::where('phone', $phone)->first();

        return $customer ? [
            'name' => $customer->name,
            'owes' => app(CustomerCreditLedger::class)->describeOwed($customer, null),
        ] : null;
    }

    public function save(OpeningCreditService $service): void
    {
        $this->resetValidation();
        $c = $service->check($this->formInput(), $this->shops);
        if ($c['errors']) {
            foreach ($c['errors'] as $error) {
                $field = match (true) {
                    str_contains($error, 'Phone')                                 => 'phone',
                    str_contains($error, 'name')                                  => 'name',
                    str_contains($error, 'Shop')                                  => 'shopId',
                    str_contains($error, 'Amount')                                => 'amount',
                    str_contains($error, 'Date')                                  => 'takenOn',
                    default                                                       => 'amount',
                };
                $this->addError($field, $error);
            }
            return;
        }

        try {
            $entry = $service->record($this->formInput(), auth()->user(), 'manual');
        } catch (\DomainException $e) {
            $this->addError('amount', $e->getMessage());
            return;
        }

        $this->dispatch('notification', ['type' => 'success', 'message' =>
            number_format($entry->amount) . ' RWF old credit added for ' . $entry->customer->name
            . ($entry->customer_created ? ' (new customer registered).' : '.')]);
        $this->showForm = false;
    }

    private function formInput(): array
    {
        return [
            'phone' => $this->phone, 'name' => $this->name, 'shop' => $this->shopId,
            'amount' => $this->amount, 'taken_on' => $this->takenOn,
            'reference' => $this->reference, 'notes' => $this->notes,
        ];
    }

    // ── Upload ───────────────────────────────────────────────

    public function openImport(): void
    {
        $this->reset(['excelFile', 'preview', 'importRows']);
        $this->resetValidation();
        $this->showForm   = false;
        $this->showImport = true;
    }

    public function closeImport(): void
    {
        $this->reset(['excelFile', 'preview', 'importRows']);
        $this->showImport = false;
    }

    public function updatedExcelFile(OpeningCreditService $service): void
    {
        $this->reset(['preview', 'importRows']);
        $this->resetValidation();
        $this->validate(['excelFile' => 'required|file|mimes:xlsx,xls,csv|max:5120'], [], ['excelFile' => 'file']);

        try {
            $sheets = Excel::toArray([], $this->excelFile->getRealPath());
            $rows   = OpeningCreditService::rowsFromSheet($sheets[0] ?? []);
        } catch (\DomainException $e) {
            $this->addError('excelFile', $e->getMessage());
            return;
        } catch (\Throwable $e) {
            report($e);
            $this->addError('excelFile', "The file couldn't be read. Use the template (.xlsx).");
            return;
        }

        if (! $rows) {
            $this->addError('excelFile', 'The sheet has no rows under the headings.');
            return;
        }
        if (count($rows) > 2000) {
            $this->addError('excelFile', 'Up to 2,000 rows per upload — split the sheet.');
            return;
        }

        $seen = [];
        foreach ($rows as $rowNo => $row) {
            $c = $service->check($row, $this->shops);
            // The same debt twice in one sheet
            if (! $c['errors'] && $c['phone']) {
                $key = $c['phone'] . '|' . $c['shop']->id . '|' . $c['amount'] . '|' . $c['taken_on'];
                if (isset($seen[$key])) {
                    $c['errors'][] = 'Same as row ' . $seen[$key] . '.';
                } else {
                    $seen[$key] = $rowNo;
                }
            }
            $this->preview[] = [
                'row'             => $rowNo,
                'phone'           => $c['phone'] ?? (string) ($row['phone'] ?? ''),
                'name'            => $c['name'] ?: (string) ($row['name'] ?? ''),
                'shop'            => $c['shop']?->name ?? (string) ($row['shop'] ?? ''),
                'amount'          => $c['amount'],
                'taken_on'        => $c['taken_on'],
                'date_given'      => $c['date_given'],
                'errors'          => $c['errors'],
                'is_new_customer' => $c['is_new_customer'],
                'name_differs'    => ! $c['is_new_customer'] && $c['typed_name'] !== ''
                                     && mb_strtolower($c['typed_name']) !== mb_strtolower($c['name']),
            ];
            $this->importRows[$rowNo] = $row;
        }
    }

    #[Computed]
    public function previewTotals(): array
    {
        $ok = collect($this->preview)->filter(fn ($r) => ! $r['errors']);

        return [
            'ok'        => $ok->count(),
            'amount'    => (int) $ok->sum('amount'),
            'new'       => $ok->where('is_new_customer', true)->unique('phone')->count(),
            'problems'  => count($this->preview) - $ok->count(),
        ];
    }

    public function confirmImport(OpeningCreditService $service): void
    {
        $okRows = collect($this->preview)->filter(fn ($r) => ! $r['errors'])->pluck('row')->all();
        if (! $okRows) {
            $this->addError('excelFile', 'There are no rows ready to add.');
            return;
        }

        try {
            $result = $service->import(array_intersect_key($this->importRows, array_flip($okRows)), auth()->user());
        } catch (\DomainException $e) {
            $this->addError('excelFile', $e->getMessage());
            return;
        }

        $this->dispatch('notification', ['type' => 'success', 'message' =>
            "Added {$result['recorded']} old credit " . str('entry')->plural($result['recorded'])
            . ' (' . number_format($result['amount']) . ' RWF)'
            . ($result['customers_created'] ? ", {$result['customers_created']} new " . str('customer')->plural($result['customers_created']) : '')
            . ($result['skipped'] ? ", {$result['skipped']} skipped" : '') . '.']);
        $this->closeImport();
    }

    public function downloadTemplate()
    {
        $book  = new Spreadsheet();
        $sheet = $book->getActiveSheet()->setTitle('Old credit');
        $sheet->fromArray(['Phone', 'Customer name', 'Shop', 'Amount (RWF)', 'Date taken', 'Reference', 'Notes'], null, 'A1');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A2:A2001')->getNumberFormat()->setFormatCode('@');   // keep the leading 0
        $sheet->getStyle('D2:D2001')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E2:E2001')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        foreach (['A' => 16, 'B' => 26, 'C' => 26, 'D' => 15, 'E' => 13, 'F' => 16, 'G' => 32] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
        $sheet->freezePane('A2');

        $shops = $book->createSheet()->setTitle('Shops');
        $shops->setCellValue('A1', 'Shop names to use in column C')->getStyle('A1')->getFont()->setBold(true);
        foreach ($this->shops->values() as $i => $shop) {
            $shops->setCellValue('A' . ($i + 2), $shop->name);
        }
        $shops->getColumnDimension('A')->setWidth(36);

        $ex = $book->createSheet()->setTitle('Example');
        $ex->fromArray(['Phone', 'Customer name', 'Shop', 'Amount (RWF)', 'Date taken', 'Reference', 'Notes'], null, 'A1');
        $ex->getStyle('A1:G1')->getFont()->setBold(true);
        $ex->setCellValueExplicit('A2', '0788123456', DataType::TYPE_STRING);
        $ex->fromArray([null, 'Jean Mugisha', $this->shops->first()?->name ?? 'Shop name', 45000, '15/08/2026', 'Notebook p.12', 'Took 3 pairs'], null, 'A2', true);
        $ex->setCellValue('A4', 'How to fill it in (only the "Old credit" sheet is read):');
        $ex->getStyle('A4')->getFont()->setBold(true);
        foreach ([
            'Phone — required. 07XXXXXXXX; +250 is fine. An existing customer is matched by phone.',
            'Customer name — required for a customer who is not registered yet (they are registered with it).',
            'Shop — required: the shop that gave the credit (see the Shops sheet). It is repaid at that shop.',
            'Amount — required: what the customer still owes, in RWF.',
            'Date taken — day/month/year. Blank = today. Overdue alerts count from this date.',
            'Reference, Notes — optional (e.g. notebook page).',
        ] as $i => $line) {
            $ex->setCellValue('A' . (5 + $i), $line);
        }
        $ex->getColumnDimension('A')->setWidth(16);
        $book->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($book) {
            (new Xlsx($book))->save('php://output');
        }, 'old-credit-template.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    // ── Remove ───────────────────────────────────────────────

    public function askRemove(int $id): void
    {
        $this->removingId   = $id;
        $this->removeReason = '';
        $this->resetValidation();
    }

    public function cancelRemove(): void
    {
        $this->removingId = null;
        $this->resetValidation();
    }

    public function remove(OpeningCreditService $service): void
    {
        $entry = OpeningCredit::findOrFail($this->removingId);
        try {
            $service->remove($entry, $this->removeReason, auth()->user());
        } catch (\DomainException $e) {
            $this->addError('removeReason', $e->getMessage());
            return;
        }
        $this->dispatch('notification', ['type' => 'success', 'message' => 'Entry removed; ' . number_format($entry->amount) . ' RWF taken off the balance.']);
        $this->removingId = null;
    }

    // ── Data ─────────────────────────────────────────────────

    #[Computed]
    public function shops()
    {
        return Shop::orderBy('name')->get(['id', 'name', 'code']);
    }

    #[Computed]
    public function stats(): array
    {
        $s = OpeningCredit::active()->toBase()->selectRaw('
                COUNT(*)                                         AS entries,
                COALESCE(SUM(amount), 0)                         AS amount,
                COUNT(DISTINCT customer_id)                      AS customers,
                COUNT(DISTINCT customer_id) FILTER (WHERE customer_created) AS created,
                COUNT(DISTINCT shop_id)                          AS shops,
                COUNT(*) FILTER (WHERE source = \'excel\')       AS from_excel
            ')->first();

        // What is still owed by the customers who have old credit, at the shops it was entered for
        $owed = (int) DB::table('customer_shop_balances as b')
            ->whereExists(fn ($q) => $q->from('opening_credits as o')->whereNull('o.removed_at')
                ->whereColumn('o.customer_id', 'b.customer_id')->whereColumn('o.shop_id', 'b.shop_id'))
            ->sum('b.outstanding_balance');

        return collect((array) $s)->map(fn ($v) => (int) $v)->all() + ['still_owed' => $owed];
    }

    public function render()
    {
        $term = trim($this->search);
        $entries = OpeningCredit::query()
            ->with(['customer:id,name,phone,deleted_at', 'shop:id,name', 'recordedBy:id,name', 'removedBy:id,name'])
            ->when($this->shopFilter !== '', fn ($q) => $q->where('shop_id', (int) $this->shopFilter))
            ->when($term !== '', function ($q) use ($term) {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $q->whereHas('customer', fn ($c) => $c->withTrashed()
                    ->where('name', 'ilike', $like)->orWhere('phone', 'like', $like));
            })
            ->orderByRaw('removed_at IS NOT NULL')
            ->orderByDesc('created_at')->orderByDesc('id')
            ->paginate(25);

        return view('livewire.owner.credit.opening-credits', ['entries' => $entries]);
    }
}
