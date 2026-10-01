<?php

namespace App\Console\Commands;

use App\Enums\AlertSeverity;
use App\Enums\TransferStatus;
use App\Models\Alert;
use App\Models\Box;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Transfer;
use Illuminate\Console\Command;

class GenerateSystemAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate system alerts for low stock, expiring products, and pending transfers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔔 Generating system alerts...');

        $alertsCreated = 0;

        // Generate low stock alerts
        $alertsCreated += $this->generateLowStockAlerts();

        // Generate expiring product alerts
        $alertsCreated += $this->generateExpiringProductAlerts();

        // Generate pending transfer alerts
        $alertsCreated += $this->generatePendingTransferAlerts();
        $alertsCreated += $this->generateTransferDelayAlerts();

        // Generate pending warehouse fulfillment alerts
        $alertsCreated += $this->generatePendingFulfillmentAlerts();

        // Generate overdue credit alerts
        $alertsCreated += $this->generateOverdueCreditAlerts();

        // Resolve alerts for issues that have been fixed
        $alertsResolved = $this->resolveFixedIssues();

        $this->info("✅ Generated {$alertsCreated} new alerts");
        $this->info("✅ Resolved {$alertsResolved} fixed alerts");

        return Command::SUCCESS;
    }

    /**
     * Generate alerts for low stock products
     */
    private function generateLowStockAlerts(): int
    {
        $count = 0;

        $lowStockProducts = Product::active()
            ->with('boxes')
            ->get()
            ->filter(function ($product) {
                $totalStock = $product->boxes()
                    ->whereIn('status', ['full', 'partial'])
                    ->sum('items_remaining');
                return $totalStock <= $product->low_stock_threshold && $totalStock > 0;
            });

        foreach ($lowStockProducts as $product) {
            $totalStock = $product->boxes()
                ->whereIn('status', ['full', 'partial'])
                ->sum('items_remaining');

            // Check if alert already exists and is unresolved
            $existingAlert = Alert::where('entity_type', Product::class)
                ->where('entity_id', $product->id)
                ->where('title', 'Low Stock Alert')
                ->unresolved()
                ->first();

            if (!$existingAlert) {
                Alert::create([
                    'title' => 'Low Stock Alert',
                    'message' => "{$product->name} is running low. Only {$totalStock} items remaining (threshold: {$product->low_stock_threshold}).",
                    'severity' => AlertSeverity::CRITICAL,
                    'entity_type' => Product::class,
                    'entity_id' => $product->id,
                    'action_url' => route('owner.products.edit', $product),
                    'action_label' => 'View Product',
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Generate alerts for expiring products
     */
    private function generateExpiringProductAlerts(): int
    {
        $count = 0;

        $expiringBoxes = Box::whereIn('status', ['full', 'partial'])
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>=', now())
            ->with('product')
            ->get();

        foreach ($expiringBoxes as $box) {
            // Check if alert already exists and is unresolved
            $existingAlert = Alert::where('entity_type', Box::class)
                ->where('entity_id', $box->id)
                ->where('title', 'Product Expiring Soon')
                ->unresolved()
                ->first();

            if (!$existingAlert) {
                $daysUntilExpiry = now()->diffInDays($box->expiry_date);
                $severity = $daysUntilExpiry <= 7 ? AlertSeverity::CRITICAL : AlertSeverity::WARNING;

                Alert::create([
                    'title' => 'Product Expiring Soon',
                    'message' => "{$box->product->name} (Box {$box->box_code}) expires in {$daysUntilExpiry} days. {$box->items_remaining} items remaining.",
                    'severity' => $severity,
                    'entity_type' => Box::class,
                    'entity_id' => $box->id,
                    'action_url' => '#',
                    'action_label' => 'View Box',
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Generate alerts for pending transfers
     */
    private function generatePendingTransferAlerts(): int
    {
        $count = 0;

        // Get transfers pending for more than 24 hours
        $pendingTransfers = Transfer::where('status', TransferStatus::PENDING)
            ->where('requested_at', '<=', now()->subHours(24))
            ->with(['toShop', 'fromWarehouse'])
            ->get();

        foreach ($pendingTransfers as $transfer) {
            // Check if alert already exists and is unresolved
            $existingAlert = Alert::where('entity_type', Transfer::class)
                ->where('entity_id', $transfer->id)
                ->where('title', 'Pending Transfer Approval')
                ->unresolved()
                ->first();

            if (!$existingAlert) {
                $hoursPending = now()->diffInHours($transfer->requested_at);

                Alert::create([
                    'title' => 'Pending Transfer Approval',
                    'message' => "Transfer {$transfer->transfer_number} from {$transfer->fromWarehouse->name} to {$transfer->toShop->name} has been pending for {$hoursPending} hours.",
                    'severity' => AlertSeverity::WARNING,
                    'entity_type' => Transfer::class,
                    'entity_id' => $transfer->id,
                    'action_url' => route('owner.transfers.show', $transfer),
                    'action_label' => 'Review Transfer',
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Transfers stuck after approval (owner Settings → transfer alert hours):
     * not packed, packed but not dispatched, overdue on the road (past the
     * expected arrival, or the fallback hours), arrived but not scanned in.
     * TransferService::record() resolves these when the transfer moves on.
     */
    private function generateTransferDelayAlerts(): int
    {
        $settings = app(\App\Services\SettingsService::class);
        $pack = $settings->transferAlertPackHours();
        $transit = $settings->transferAlertTransitHours();
        $receive = $settings->transferAlertReceiveHours();

        $checks = [
            ['Transfer Not Packed', AlertSeverity::WARNING,
                Transfer::where('status', TransferStatus::APPROVED)->where('reviewed_at', '<=', now()->subHours($pack)),
                fn ($t) => "{$t->transfer_number} for {$t->toShop?->name} was approved " . $this->ago($t->reviewed_at) . ' and is not packed yet.'],
            ['Transfer Waiting for Transporter', AlertSeverity::WARNING,
                Transfer::where('status', TransferStatus::READY)->where('packing_done_at', '<=', now()->subHours($pack)),
                fn ($t) => "{$t->transfer_number} for {$t->toShop?->name} has been packed for " . $this->ago($t->packing_done_at, false) . ' without being dispatched.'],
            ['Transfer Overdue in Transit', AlertSeverity::CRITICAL,
                Transfer::where('status', TransferStatus::IN_TRANSIT)->where(fn ($q) => $q
                    ->where('expected_arrival_at', '<', now())
                    ->orWhere(fn ($r) => $r->whereNull('expected_arrival_at')->where('shipped_at', '<=', now()->subHours($transit)))),
                fn ($t) => "{$t->transfer_number} left " . $this->ago($t->shipped_at) . " with {$t->transporter?->name}"
                    . ($t->expected_arrival_at ? ', expected ' . local_time($t->expected_arrival_at)->format('D H:i') : '')
                    . ", and {$t->toShop?->name} hasn't confirmed arrival."],
            ['Transfer Not Received', AlertSeverity::WARNING,
                Transfer::where('status', TransferStatus::DELIVERED)->where('delivered_at', '<=', now()->subHours($receive)),
                fn ($t) => "{$t->transfer_number} arrived at {$t->toShop?->name} " . $this->ago($t->delivered_at) . ' and its boxes are not scanned in yet.'],
        ];

        $count = 0;
        foreach ($checks as [$title, $severity, $query, $message]) {
            foreach ($query->with(['toShop', 'transporter'])->get() as $transfer) {
                $exists = Alert::where('entity_type', Transfer::class)->where('entity_id', $transfer->id)
                    ->where('title', $title)->unresolved()->exists();
                if ($exists) {
                    continue;
                }
                Alert::create([
                    'title'        => $title,
                    'message'      => $message($transfer),
                    'severity'     => $severity,
                    'entity_type'  => Transfer::class,
                    'entity_id'    => $transfer->id,
                    'action_url'   => route('owner.transfers.show', $transfer),
                    'action_label' => 'Open transfer',
                ]);
                $count++;
            }
        }

        return $count;
    }

    /** "3 h ago" / "2 d ago" (or without "ago"). */
    private function ago($at, bool $suffix = true): string
    {
        $hours = (int) abs(now()->diffInHours($at));
        $text = $hours >= 48 ? intdiv($hours, 24) . ' d' : $hours . ' h';

        return $suffix ? "{$text} ago" : $text;
    }

    /**
     * Generate alerts for warehouse-direct sales sitting unfulfilled too long.
     */
    private function generatePendingFulfillmentAlerts(): int
    {
        $count = 0;

        $pendingSales = Sale::warehouseDirect()
            ->pendingFulfillment()
            ->where('sale_date', '<=', now()->subHours(24))
            ->with('shop')
            ->get();

        foreach ($pendingSales as $sale) {
            $existingAlert = Alert::where('entity_type', Sale::class)
                ->where('entity_id', $sale->id)
                ->where('title', 'Pending Fulfillment')
                ->unresolved()
                ->first();

            if (!$existingAlert) {
                // abs()+intval() — diffInHours() returns a signed float in this
                // Carbon version (negative + fractional when sale_date is past).
                $hoursPending = (int) abs(now()->diffInHours($sale->sale_date));

                Alert::create([
                    'title' => 'Pending Fulfillment',
                    'message' => "Sale {$sale->sale_number} for {$sale->shop?->name} has been awaiting warehouse dispatch for {$hoursPending} hours.",
                    'severity' => AlertSeverity::WARNING,
                    'entity_type' => Sale::class,
                    'entity_id' => $sale->id,
                    'action_url' => route('warehouse.sales.fulfillment'),
                    'action_label' => 'Review Queue',
                ]);
                $count++;
            }
        }

        return $count;
    }

    private function generateOverdueCreditAlerts(): int
    {
        $overdueDays = app(\App\Services\SettingsService::class)->overdueCreditDays();

        if ($overdueDays <= 0) {
            return 0;
        }

        $count  = 0;
        $cutoff = now()->subDays($overdueDays);

        $overdueCustomers = Customer::where('outstanding_balance', '>', 0)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($cutoff) {
                $q->whereNull('last_repayment_at')
                  ->where('last_credit_at', '<', $cutoff);
            })
            ->orWhere(function ($q) use ($cutoff) {
                $q->where('outstanding_balance', '>', 0)
                  ->whereNull('deleted_at')
                  ->where('last_repayment_at', '<', $cutoff);
            })
            ->get();

        foreach ($overdueCustomers as $customer) {
            $exists = Alert::where('entity_type', 'Customer')
                ->where('entity_id', $customer->id)
                ->where('title', 'like', 'Overdue Credit%')
                ->unresolved()
                ->exists();

            if ($exists) {
                continue;
            }

            $daysSinceActivity = $customer->last_repayment_at
                ? (int) now()->diffInDays($customer->last_repayment_at)
                : (int) now()->diffInDays($customer->last_credit_at ?? $customer->created_at);

            Alert::create([
                'title'        => 'Overdue Credit — ' . $customer->name,
                'message'      => number_format($customer->outstanding_balance) . ' RWF outstanding.'
                               . ' No repayment in ' . $daysSinceActivity . ' days.',
                'severity'     => $customer->outstanding_balance >= 100000 ? AlertSeverity::CRITICAL : AlertSeverity::WARNING,
                'entity_type'  => 'Customer',
                'entity_id'    => $customer->id,
                'action_url'   => route('owner.credit.writeoffs'),
                'action_label' => 'View Write-offs',
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * Resolve alerts for issues that have been fixed
     */
    private function resolveFixedIssues(): int
    {
        $count = 0;

        // Resolve low stock alerts when stock is replenished
        $lowStockAlerts = Alert::where('title', 'Low Stock Alert')
            ->where('entity_type', Product::class)
            ->unresolved()
            ->get();

        foreach ($lowStockAlerts as $alert) {
            $product = Product::find($alert->entity_id);
            if (!$product) {
                // Product was deleted entirely — the alert is orphaned, resolve it.
                $alert->markAsResolved();
                $count++;
                continue;
            }

            $totalStock = $product->boxes()
                ->whereIn('status', ['full', 'partial'])
                ->sum('items_remaining');

            // If stock is above threshold or completely out, resolve the alert
            if ($totalStock > $product->low_stock_threshold || $totalStock === 0) {
                $alert->markAsResolved();
                $count++;
            }
        }

        // Resolve expiring product alerts when box is expired or empty
        $expiringAlerts = Alert::where('title', 'Product Expiring Soon')
            ->where('entity_type', Box::class)
            ->unresolved()
            ->get();

        foreach ($expiringAlerts as $alert) {
            $box = Box::find($alert->entity_id);
            if ($box) {
                // Resolve if box is empty, expired, or more than 30 days from expiry
                if ($box->items_remaining === 0 || $box->expiry_date < now() || $box->expiry_date > now()->addDays(30)) {
                    $alert->markAsResolved();
                    $count++;
                }
            }
        }

        // Resolve transfer alerts when transfer is no longer pending
        $transferAlerts = Alert::whereIn('title', [
                'Pending Transfer Approval',
                'New Transfer Request',
                'Transfer Approval Required',
            ])
            ->where('entity_type', Transfer::class)
            ->unresolved()
            ->get();

        foreach ($transferAlerts as $alert) {
            $transfer = Transfer::find($alert->entity_id);
            if ($transfer && $transfer->status !== TransferStatus::PENDING) {
                $alert->markAsResolved();
                $count++;
            }
        }

        // Resolve fulfillment alerts once the sale is fulfilled, cancelled, or voided
        $fulfillmentAlerts = Alert::where('title', 'Pending Fulfillment')
            ->where('entity_type', Sale::class)
            ->unresolved()
            ->get();

        foreach ($fulfillmentAlerts as $alert) {
            $sale = Sale::find($alert->entity_id);
            if (!$sale || $sale->fulfillment_status !== 'pending') {
                $alert->markAsResolved();
                $count++;
            }
        }

        return $count;
    }
}
