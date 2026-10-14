<?php

namespace App\Services\Sales;

use App\Models\Customer;
use App\Models\OpeningCredit;
use App\Models\Shop;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Old credit: what customers owed a shop before the system was in use.
 *
 * Each entry goes through CustomerCreditLedger::extend() at the lending shop,
 * dated when the credit was taken, so it is repaid, written off and flagged
 * overdue like any other credit. There is no sale behind it: it never counts
 * as sales or as credit given on a day. A customer who isn't registered yet is
 * created from the phone + name (registered at the lending shop).
 *
 * Owner / admin only.
 */
class OpeningCreditService
{
    public const MAX_AMOUNT = 1_000_000_000_000;

    public function __construct(private CustomerCreditLedger $ledger) {}

    /**
     * Rwandan numbers are stored as 07XXXXXXXX. Accepts +250 / 250 prefixes,
     * spaces and dashes, and Excel's lost leading zero (788123456).
     */
    public static function normalisePhone(mixed $phone): ?string
    {
        if ($phone === null) {
            return null;
        }
        if (is_float($phone)) {
            $phone = number_format($phone, 0, '', '');
        }
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (strlen($digits) === 12 && str_starts_with($digits, '250')) {
            $digits = '0' . substr($digits, 3);
        } elseif (strlen($digits) === 9 && str_starts_with($digits, '7')) {
            $digits = '0' . $digits;
        }

        return strlen($digits) >= 10 && strlen($digits) <= 15 ? $digits : null;
    }

    /** Whole RWF from 12500, "12,500", "12 500 RWF". Null when not a whole positive number. */
    public static function parseAmount(mixed $amount): ?int
    {
        if (is_int($amount)) {
            return $amount;
        }
        if (is_float($amount)) {
            return floor($amount) == $amount ? (int) $amount : null;
        }
        $s = preg_replace('/(rwf|frw|\s|,)/i', '', (string) $amount);
        if ($s === '' || ! preg_match('/^\d+(\.0+)?$/', $s)) {
            return null;
        }

        return (int) $s;
    }

    /** Y-m-d from an Excel date number or a typed date (day first: 15/03/2026). '' when blank, null when unreadable. */
    public static function parseDate(mixed $date): ?string
    {
        if ($date === null || $date === '') {
            return '';
        }
        if ($date instanceof \DateTimeInterface) {
            return Carbon::instance($date)->format('Y-m-d');
        }
        if (is_numeric($date) && (float) $date > 20000 && (float) $date < 80000) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $date))->format('Y-m-d');
        }
        $s = trim((string) $date);
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'j/n/Y', 'j-n-Y', 'd/m/y', 'Y/m/d'] as $format) {
            $d = \DateTime::createFromFormat('!' . $format, $s);
            if ($d && $d->format($format) === $s) {
                return $d->format('Y-m-d');
            }
        }
        // Single-digit day/month written with leading zeros elsewhere, e.g. 5/03/2026
        $d = \DateTime::createFromFormat('!j/m/Y', $s) ?: \DateTime::createFromFormat('!d/n/Y', $s);

        return $d ? $d->format('Y-m-d') : null;
    }

    /** Shop by id, name, code, or the short name after "—" ("Remera"). */
    public function resolveShop(mixed $shop, ?Collection $shops = null): ?Shop
    {
        $shops ??= Shop::orderBy('name')->get(['id', 'name', 'code']);
        if ($shop instanceof Shop) {
            return $shop;
        }
        if (is_int($shop) || (is_string($shop) && ctype_digit($shop))) {
            return $shops->firstWhere('id', (int) $shop);
        }

        $needle = Str::lower(trim((string) $shop));
        if ($needle === '') {
            return null;
        }
        $hit = $shops->first(fn ($s) => Str::lower($s->name) === $needle || Str::lower((string) $s->code) === $needle);
        if ($hit) {
            return $hit;
        }
        $short = $shops->filter(fn ($s) => Str::lower(trim(Str::afterLast($s->name, '—'))) === $needle);

        return $short->count() === 1 ? $short->first() : null;
    }

    /**
     * Validate one entry. Returns:
     *   errors[]   — empty when it can be recorded
     *   phone, name, shop (Shop|null), amount, taken_on (Y-m-d), reference, notes
     *   customer (Customer|null — the existing one), is_new_customer
     *
     * $input keys: phone, name, shop (id / name / code), amount, taken_on,
     * reference, notes.
     */
    public function check(array $input, ?Collection $shops = null): array
    {
        $errors = [];

        $rawPhone = trim((string) ($input['phone'] ?? ''));
        $phone    = self::normalisePhone($input['phone'] ?? null);
        if ($rawPhone === '') {
            $errors[] = 'Phone number is missing.';
        } elseif (! $phone) {
            $errors[] = "Phone number \"{$rawPhone}\" isn't valid.";
        }

        $customer = $phone ? Customer::withTrashed()->where('phone', $phone)->first() : null;
        $name     = trim((string) ($input['name'] ?? ''));
        if (! $customer && $phone) {
            if ($name === '') {
                $errors[] = 'New customer: the name is missing.';
            } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
                $errors[] = 'Customer name must be 2–100 characters.';
            }
        }

        $shop = $this->resolveShop($input['shop'] ?? null, $shops);
        if (! $shop) {
            $raw = trim((string) ($input['shop'] ?? ''));
            $errors[] = $raw === '' ? 'Shop is missing.' : "Shop \"{$raw}\" not found.";
        }

        $amount = self::parseAmount($input['amount'] ?? null);
        if ($amount === null || $amount <= 0) {
            $errors[] = 'Amount must be a whole number above 0.';
        } elseif ($amount > self::MAX_AMOUNT) {
            $errors[] = 'Amount is too large.';
        }

        $takenOn = self::parseDate($input['taken_on'] ?? null);
        if ($takenOn === null) {
            $errors[] = 'Date taken isn\'t a date (use day/month/year).';
        } elseif ($takenOn !== '') {
            if ($takenOn > business_today()->toDateString()) {
                $errors[] = 'Date taken is in the future.';
            } elseif ($takenOn < '2000-01-01') {
                $errors[] = 'Date taken is before 2000.';
            }
        }

        $reference = Str::limit(trim((string) ($input['reference'] ?? '')), 100, '');
        $notes     = Str::limit(trim((string) ($input['notes'] ?? '')), 500, '');

        // Same debt entered twice (re-uploading a sheet)
        if (! $errors && $customer) {
            $dup = OpeningCredit::active()
                ->where('customer_id', $customer->id)->where('shop_id', $shop->id)
                ->where('amount', $amount)
                ->whereDate('taken_on', $takenOn ?: business_today()->toDateString())
                ->first();
            if ($dup) {
                $errors[] = 'Already entered on ' . local_time($dup->created_at)->format('d M Y') . '.';
            }
        }

        return [
            'errors'          => $errors,
            'phone'           => $phone,
            'name'            => $customer?->name ?? $name,
            'typed_name'      => $name,
            'shop'            => $shop,
            'amount'          => $amount,
            'taken_on'        => $takenOn ?: business_today()->toDateString(),
            'date_given'      => (bool) $takenOn,
            'reference'       => $reference ?: null,
            'notes'           => $notes ?: null,
            'customer'        => $customer,
            'is_new_customer' => ! $customer,
        ];
    }

    /**
     * How much of what customers owe today is still old credit — for one shop
     * or all. Repayments and write-offs are taken as paying the oldest debt
     * first, so per customer × shop: min(balance, old credit − repaid − written off).
     */
    public static function stillOwed(?int $shopId = null): int
    {
        $sql = 'select coalesce(sum(least(b.outstanding_balance,
                       greatest(0, o.amt - b.total_repaid - b.total_written_off))), 0) as v
                from customer_shop_balances b
                join (select customer_id, shop_id, sum(amount) as amt from opening_credits
                      where removed_at is null group by customer_id, shop_id) o
                  on o.customer_id = b.customer_id and o.shop_id = b.shop_id'
            . ($shopId !== null ? ' where b.shop_id = ?' : '');

        return (int) DB::selectOne($sql, $shopId !== null ? [$shopId] : [])->v;
    }

    /** Record one entry. Throws DomainException with the first problem. */
    public function record(array $input, User $by, string $source = 'manual'): OpeningCredit
    {
        $this->authorise($by);

        return DB::transaction(function () use ($input, $by, $source) {
            $c = $this->check($input);
            if ($c['errors']) {
                throw new \DomainException($c['errors'][0]);
            }

            $created  = false;
            $customer = $c['customer'];
            if ($customer?->trashed()) {
                $customer->restore();
            }
            if (! $customer) {
                $customer = Customer::create([
                    'name'          => $c['name'],
                    'phone'         => $c['phone'],
                    'registered_by' => $by->id,
                    'shop_id'       => $c['shop']->id,
                ]);
                $created = true;
            }

            // Dated noon on the day taken (business time), so overdue alerts count from then
            $at = Carbon::parse($c['taken_on'] . ' 12:00', config('tenant.timezone'))->utc();
            $this->ledger->extend($customer, $c['shop']->id, $c['amount'], $at);

            $entry = OpeningCredit::create([
                'customer_id'      => $customer->id,
                'shop_id'          => $c['shop']->id,
                'amount'           => $c['amount'],
                'taken_on'         => $c['taken_on'],
                'reference'        => $c['reference'],
                'notes'            => $c['notes'],
                'source'           => $source,
                'customer_created' => $created,
                'recorded_by'      => $by->id,
            ]);

            if ($source === 'manual') {
                AuditLogger::log([
                    'action'            => 'opening_credit_recorded',
                    'module'            => 'credit',
                    'actor'             => $by,
                    'entity_type'       => 'Customer',
                    'entity_id'         => $customer->id,
                    'entity_identifier' => $customer->name . ' (' . $customer->phone . ')',
                    'details'           => [
                        'opening_credit_id' => $entry->id,
                        'shop_id'           => $c['shop']->id,
                        'amount'            => $c['amount'],
                        'taken_on'          => $c['taken_on'],
                        'customer_created'  => $created,
                    ],
                ]);
            }

            return $entry;
        });
    }

    /**
     * Record every row that passes check(), in one transaction. Rows with
     * problems are skipped; two identical rows in one sheet count once.
     *
     * @return array{recorded:int, amount:int, customers_created:int, skipped:int}
     */
    public function import(array $rows, User $by): array
    {
        $this->authorise($by);

        return DB::transaction(function () use ($rows, $by) {
            $recorded = $amount = $created = $skipped = 0;
            foreach ($rows as $row) {
                $c = $this->check($row);
                if ($c['errors']) {
                    $skipped++;
                    continue;
                }
                $entry = $this->record($row, $by, 'excel');
                $recorded++;
                $amount  += $entry->amount;
                $created += $entry->customer_created ? 1 : 0;
            }

            if ($recorded) {
                AuditLogger::log([
                    'action'      => 'opening_credits_imported',
                    'module'      => 'credit',
                    'actor'       => $by,
                    'entity_type' => 'OpeningCredit',
                    'details'     => [
                        'entries'           => $recorded,
                        'amount'            => $amount,
                        'customers_created' => $created,
                        'skipped'           => $skipped,
                    ],
                ]);
            }

            return ['recorded' => $recorded, 'amount' => $amount, 'customers_created' => $created, 'skipped' => $skipped];
        });
    }

    /**
     * Take back an entry made by mistake. Only while the shop is still owed
     * at least that much — once the customer has paid part of it, the
     * repayment is real money and the fix is a write-off.
     */
    public function remove(OpeningCredit $entry, string $reason, User $by): void
    {
        $this->authorise($by);
        $reason = trim($reason);
        if (mb_strlen($reason) < 3) {
            throw new \DomainException('Give a reason for removing this entry.');
        }

        DB::transaction(function () use ($entry, $reason, $by) {
            $entry = OpeningCredit::lockForUpdate()->findOrFail($entry->id);
            if ($entry->removed_at) {
                throw new \DomainException('This entry was already removed.');
            }
            $customer = $entry->customer;
            $owed     = $this->ledger->balanceAt($customer, $entry->shop_id);
            if ($owed < $entry->amount) {
                throw new \DomainException(
                    'The customer now owes this shop only ' . number_format($owed) . ' RWF, less than this entry ('
                    . number_format($entry->amount) . ' RWF). Part of it was paid, so it can\'t be removed — use a write-off instead.'
                );
            }

            $this->ledger->reverse($customer, $entry->shop_id, $entry->amount);
            $entry->forceFill(['removed_at' => now(), 'removed_by' => $by->id, 'remove_reason' => Str::limit($reason, 255, '')])->save();

            AuditLogger::log([
                'action'            => 'opening_credit_removed',
                'module'            => 'credit',
                'actor'             => $by,
                'severity'          => 'warning',
                'entity_type'       => 'Customer',
                'entity_id'         => $customer->id,
                'entity_identifier' => $customer->name . ' (' . $customer->phone . ')',
                'details'           => [
                    'opening_credit_id' => $entry->id,
                    'shop_id'           => $entry->shop_id,
                    'amount'            => $entry->amount,
                    'taken_on'          => $entry->taken_on->toDateString(),
                    'reason'            => $reason,
                ],
            ]);
        });
    }

    /**
     * Rows of the first sheet, matched to columns by their header text
     * (phone, name, shop, amount, date, reference, notes).
     *
     * @return array<int, array> keyed by spreadsheet row number
     */
    public static function rowsFromSheet(array $sheet): array
    {
        $header = array_map(fn ($h) => Str::lower(trim((string) $h)), $sheet[0] ?? []);
        $find   = function (array $words) use ($header) {
            foreach ($header as $i => $h) {
                foreach ($words as $w) {
                    if ($h !== '' && str_contains($h, $w)) {
                        return $i;
                    }
                }
            }
            return null;
        };
        $cols = [
            'phone'     => $find(['phone', 'tel']),
            'name'      => $find(['name', 'customer']),
            'shop'      => $find(['shop', 'branch']),
            'amount'    => $find(['amount', 'owed', 'balance']),
            'taken_on'  => $find(['date']),
            'reference' => $find(['reference', 'ref']),
            'notes'     => $find(['note', 'comment']),
        ];
        if ($cols['phone'] === null || $cols['amount'] === null) {
            throw new \DomainException('The first row must have the column headings from the template (Phone, Customer name, Shop, Amount, …).');
        }

        $rows = [];
        foreach (array_slice($sheet, 1, null, true) as $i => $line) {
            if (! array_filter($line, fn ($v) => $v !== null && trim((string) $v) !== '')) {
                continue;
            }
            $row = [];
            foreach ($cols as $key => $col) {
                $v = $col === null ? null : ($line[$col] ?? null);
                $row[$key] = is_string($v) ? trim($v) : $v;
            }
            $rows[$i + 1] = $row;
        }

        return $rows;
    }

    private function authorise(User $by): void
    {
        if (! $by->isOwner() && ! $by->isAdmin()) {
            throw new \DomainException('Only the owner can enter old credit.');
        }
    }
}
