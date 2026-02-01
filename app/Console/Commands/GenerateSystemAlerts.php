<?php

namespace App\Console\Commands;

use App\Enums\AlertSeverity;
use App\Enums\TransferStatus;
use App\Models\Alert;
use App\Models\Box;
use App\Models\Product;
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
                    'action_url' => '#',
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
                    'action_url' => '#',
                    'action_label' => 'Review Transfer',
                ]);
                $count++;
            }
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
            if ($product) {
                $totalStock = $product->boxes()
                    ->whereIn('status', ['full', 'partial'])
                    ->sum('items_remaining');

                // If stock is above threshold or completely out, resolve the alert
                if ($totalStock > $product->low_stock_threshold || $totalStock === 0) {
                    $alert->markAsResolved();
                    $count++;
                }
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
        $transferAlerts = Alert::where('title', 'Pending Transfer Approval')
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

        return $count;
    }
}
