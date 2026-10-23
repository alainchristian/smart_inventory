<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Customer;
use App\Models\DailySession;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\Transfer;
use App\Models\Transporter;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Str;

/**
 * Turns a raw audit entry into something a person can read: a one-sentence
 * summary, labelled rows with names instead of ids and money as money, and a
 * link to the record. Presentation only: it never changes what is stored.
 */
class ActivityPresenter
{
    /** key => [model, column holding its name]. Keys ending in "_by" are always users. */
    private const REFERENCES = [
        'shop_id'             => [Shop::class, 'name'],
        'warehouse_id'        => [Warehouse::class, 'name'],
        'source_warehouse_id' => [Warehouse::class, 'name'],
        'customer_id'         => [Customer::class, 'name'],
        'product_id'          => [Product::class, 'name'],
        'category_id'         => [Category::class, 'name'],
        'expense_category_id' => [ExpenseCategory::class, 'name'],
        'transporter_id'      => [Transporter::class, 'name'],
        'sale_id'             => [Sale::class, 'sale_number'],
        'transfer_id'         => [Transfer::class, 'transfer_number'],
        'user_id'             => [User::class, 'name'],
        'seller_id'           => [User::class, 'name'],
        'daily_session_id'    => [DailySession::class, 'session_date'],
    ];

    /** Never worth showing: bookkeeping columns and anything secret. */
    private const HIDDEN = ['id', 'created_at', 'updated_at', 'deleted_at', 'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    private const MONEY = '/(amount|total|price|balance|variance|retained|refund|paid|outstanding|cost|cash|momo|bank|owed|repaid|written_off|deposit|withdrawal|expected|given|settled|reduced|reversed)/i';
    private const NOT_MONEY = '/(count|quantity|qty|items|number|method|status|_id$|percent|pct|rate)/i';

    /** @var array<string, string|null> lookups already made while rendering */
    private array $names = [];

    // ── Sentence ─────────────────────────────────────────────────────────────

    public function summary(ActivityLog $log): string
    {
        $who = $log->user_name ?: 'System';
        $ref = $log->entity_identifier;
        $d   = is_array($log->details) ? $log->details : [];
        $new = is_array($log->new_values) ? $log->new_values : [];
        $rwf = fn ($v) => number_format((float) $v) . ' RWF';
        $kind = $this->entityLabel($log->entity_type);

        $text = match (true) {
            in_array($log->action, ['signed_in', 'login'], true)   => "{$who} signed in",
            in_array($log->action, ['signed_out', 'logout'], true) => "{$who} signed out",
            $log->action === 'failed_login'                        => 'Failed sign-in attempt' . ($ref ? " for {$ref}" : ''),

            in_array($log->action, ['sale_created', 'mixed_sale_created', 'warehouse_direct_sale'], true)
                => "{$who} made sale {$ref}" . (isset($d['total']) ? ' for ' . $rwf($d['total']) : ''),
            $log->action === 'sale_voided'
                => "{$who} voided sale {$ref}" . (! empty($d['reason']) ? " ({$d['reason']})" : '')
                    . (! empty($d['credit_reversed']) ? ', taking ' . $rwf($d['credit_reversed']) . ' off the customer\'s credit' : ''),
            $log->action === 'fulfillment_confirmed'
                => "{$who} handed over sale {$ref}" . (! empty($d['recipient']) ? " to {$d['recipient']}" : ''),

            $log->action === 'daily_session_opened'
                => "{$who} opened the register for {$ref}" . (isset($d['opening_balance']) ? ' with ' . $rwf($d['opening_balance']) : ''),
            $log->action === 'daily_session_closed'
                => "{$who} closed the day {$ref}",

            $log->action === 'credit_repayment_recorded'
                => "{$who} recorded a repayment of " . $rwf($d['amount'] ?? 0)
                    . (isset($d['new_balance']) ? ' (the customer now owes ' . $rwf($d['new_balance']) . ')' : ''),

            $log->entity_type === 'ReturnModel' && $log->action === 'processed'
                => "{$who} processed return {$ref}"
                    . (isset($new['refund_amount']) && ! ($new['is_exchange'] ?? false) ? ', refunding ' . $rwf($new['refund_amount'])
                        . (! empty($d['refund_method']) ? ' by ' . $this->humanize($d['refund_method']) : '') : ' (exchange)'),
            $log->entity_type === 'ReturnModel' && $log->action === 'approved'
                => "{$who} approved return {$ref}" . (isset($d['refund_amount']) && ! ($d['is_exchange'] ?? false) ? ' (' . $rwf($d['refund_amount']) . ')' : ''),

            $log->action === 'customer_registered' => "{$who} registered customer {$ref}",
            $log->action === 'customer_updated'    => "{$who} updated customer {$ref}" . $this->changedFields($log),

            $log->action === 'created' => "{$who} created {$kind} " . ($ref ?: '') . $this->amountSuffix($new),
            $log->action === 'updated' => "{$who} changed {$kind} " . ($ref ?: '') . $this->changedFields($log),
            $log->action === 'deleted' => "{$who} deleted {$kind} " . ($ref ?: ''),

            $log->action === 'support_password_reset' => "Platform support reset the password of {$ref}",
            str_starts_with($log->action, 'support_user_') => "Platform support " . strtolower(Str::after($log->humanLabel(), 'Support User ')) . " account {$ref}",
            in_array($log->action, ['role_switched_to_admin', 'role_switched_to_owner'], true)
                => "{$who} switched to the " . (str_ends_with($log->action, 'admin') ? 'admin' : 'owner') . ' view',

            default => trim("{$who}: " . $log->humanLabel() . ($ref ? " {$ref}" : '')),
        };

        $text = trim(preg_replace('/\s+/', ' ', $text));

        if ($log->location_name && ! str_starts_with($log->action, 'support_')) {
            $text .= " at {$log->location_name}";
        }

        return $text;
    }

    /** " (amount, description)" for an update, from the keys that actually changed. */
    private function changedFields(ActivityLog $log): string
    {
        $new = is_array($log->new_values) ? $log->new_values : [];
        $old = is_array($log->old_values) ? $log->old_values : [];

        $fields = collect(array_keys($new))
            ->reject(fn ($k) => in_array($k, self::HIDDEN, true))
            ->filter(fn ($k) => ! array_key_exists($k, $old) || $old[$k] !== $new[$k])
            ->map(fn ($k) => strtolower($this->label($k)))
            ->values();

        if ($fields->isEmpty()) {
            return '';
        }

        return ' (' . $fields->take(4)->implode(', ') . ($fields->count() > 4 ? ', …' : '') . ')';
    }

    private function amountSuffix(array $values): string
    {
        foreach (['amount', 'total', 'refund_amount'] as $k) {
            if (isset($values[$k]) && is_numeric($values[$k])) {
                return ' for ' . number_format((float) $values[$k]) . ' RWF';
            }
        }

        return '';
    }

    public function entityLabel(?string $type): string
    {
        return match ($type) {
            null, ''                 => 'record',
            'ReturnModel'            => 'return',
            'daily_session', 'DailySession' => 'day',
            'HeldSale'               => 'held sale',
            default                  => Str::of($type)->snake(' ')->lower()->toString(),
        };
    }

    // ── Rows ─────────────────────────────────────────────────────────────────

    /** Labelled rows for a values array (details, or a created / deleted record). */
    public function rows(array $values): array
    {
        $rows = [];

        foreach ($values as $key => $value) {
            if (is_string($key) && in_array($key, self::HIDDEN, true)) {
                continue;
            }
            $rows[] = ['label' => $this->label($key), 'value' => $this->display($key, $value)];
        }

        return $rows;
    }

    /** Details can be a sentence (older entries) as well as key / value pairs. */
    public function detailRows(ActivityLog $log): array
    {
        $details = $log->details;

        if (is_string($details) && trim($details) !== '') {
            return [['label' => 'Note', 'value' => $details]];
        }

        return is_array($details) ? $this->rows($details) : [];
    }

    /** Before → after for every field that changed, shown the same way on both sides. */
    public function changes(ActivityLog $log): array
    {
        $old = is_array($log->old_values) ? $log->old_values : [];
        $new = is_array($log->new_values) ? $log->new_values : [];

        if ($old === [] || $new === []) {
            return [];
        }

        $rows = [];
        foreach ($new as $key => $value) {
            if (in_array($key, self::HIDDEN, true) || (array_key_exists($key, $old) && $old[$key] === $value)) {
                continue;
            }
            $rows[] = [
                'label' => $this->label($key),
                'old'   => array_key_exists($key, $old) ? $this->display($key, $old[$key]) : '—',
                'new'   => $this->display($key, $value),
            ];
        }

        return $rows;
    }

    // ── Where / link ─────────────────────────────────────────────────────────

    /**
     * Where it happened. Entries written before locations were stored (the log
     * can't be edited) fall back to the person's assigned location, flagged.
     *
     * @return array{name: string, kind: ?string, assigned: bool}|null
     */
    public function location(ActivityLog $log): ?array
    {
        if ($log->location_name) {
            return ['name' => $log->location_name, 'kind' => $log->location_type, 'assigned' => false];
        }

        $assigned = $log->user?->location;

        return $assigned
            ? ['name' => $assigned->name, 'kind' => $log->user->location_type?->value, 'assigned' => true]
            : null;
    }

    /** @return array{url: string, label: string}|null */
    public function recordLink(ActivityLog $log, User $viewer): ?array
    {
        // Platform support doesn't open the business's own pages (it uses View as)
        if ($viewer->isSuperAdmin()) {
            return null;
        }

        $url = $log->actionUrl($viewer);

        if (! $url && $log->entity_id && ($viewer->isOwner() || $viewer->isAdmin())) {
            try {
                $url = match ($log->entity_type) {
                    'Product'     => route('owner.products.edit', $log->entity_id),
                    'ReturnModel' => route('owner.returns.index'),
                    default       => null,
                };
            } catch (\Throwable) {
                $url = null;
            }
        }

        return $url ? ['url' => $url, 'label' => 'Open ' . $this->entityLabel($log->entity_type)] : null;
    }

    // ── Values ───────────────────────────────────────────────────────────────

    public function label(int|string $key): string
    {
        $key = (string) $key;

        if (preg_match('/^(.+)_id$/', $key, $m)) {
            return ucfirst(str_replace('_', ' ', $m[1]));
        }

        return ucfirst(str_replace('_', ' ', $key));
    }

    /** Show one value the way a person would write it. */
    public function display(int|string $key, mixed $value): string
    {
        $key = (string) $key;

        if ($value === null || $value === '') {
            return '—';
        }
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return $this->displayArray($value);
        }

        if ((is_int($value) || (is_string($value) && ctype_digit($value))) && ($name = $this->nameFor($key, (int) $value))) {
            return $name;
        }

        if (is_numeric($value) && preg_match(self::MONEY, $key) && ! preg_match(self::NOT_MONEY, $key)) {
            return number_format((float) $value) . ' RWF';
        }

        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $value)) {
            try {
                return local_time($value)->format('d M Y H:i');
            } catch (\Throwable) {
                return $value;
            }
        }

        if (is_string($value) && preg_match('/^[a-z]+(_[a-z]+)+$/', $value)) {
            return $this->humanize($value);
        }

        return (string) $value;
    }

    private function displayArray(array $value): string
    {
        if ($value === []) {
            return '—';
        }

        // A list of things ("Nike Air × 2") reads on separate lines
        if (array_is_list($value)) {
            return collect($value)->map(fn ($item) => is_array($item) ? $this->inline($item) : (string) $item)->implode('; ');
        }

        return $this->inline($value);
    }

    /** ["name" => "Nike", "qty" => 2] → "Name: Nike, Qty: 2" */
    private function inline(array $item): string
    {
        return collect($item)
            ->reject(fn ($v, $k) => in_array($k, self::HIDDEN, true))
            ->map(fn ($v, $k) => $this->label($k) . ': ' . $this->display($k, $v))
            ->implode(', ');
    }

    /** The name behind an id, for the keys we know (and any "…_by" user column). */
    private function nameFor(string $key, int $id): ?string
    {
        $spec = self::REFERENCES[$key] ?? (str_ends_with($key, '_by') ? [User::class, 'name'] : null);
        if (! $spec) {
            return null;
        }

        [$model, $column] = $spec;
        $cacheKey = $model . ':' . $id;

        if (! array_key_exists($cacheKey, $this->names)) {
            $value = $model::query()->whereKey($id)->value($column);
            $this->names[$cacheKey] = $value instanceof \DateTimeInterface ? $value->format('d M Y') : ($value !== null ? (string) $value : null);
        }

        return $this->names[$cacheKey];
    }

    private function humanize(string $value): string
    {
        return ucfirst(str_replace('_', ' ', $value));
    }
}
