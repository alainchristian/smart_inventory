<?php

namespace App\Livewire\Dashboard;

use App\Models\ActivityLog;
use Livewire\Component;

class ActivityFeed extends Component
{
    /**
     * Map raw action strings to human-readable labels + icon key.
     * Returns ['label' => string, 'icon' => string, 'color' => string]
     */
    public function parseAction(ActivityLog $log): array
    {
        $action = strtolower($log->action ?? '');
        $entity = strtolower($log->entity_type ?? '');

        // Transfer events
        if (str_contains($action, 'transfer_requested') || ($action === 'created' && $entity === 'transfer')) {
            return ['label' => 'Transfer requested', 'icon' => 'transfer', 'color' => 'blue'];
        }
        if (str_contains($action, 'transfer_approved') || ($action === 'approved' && $entity === 'transfer')) {
            return ['label' => 'Transfer approved', 'icon' => 'check', 'color' => 'green'];
        }
        if (str_contains($action, 'transfer_rejected') || ($action === 'rejected' && $entity === 'transfer')) {
            return ['label' => 'Transfer rejected', 'icon' => 'x', 'color' => 'red'];
        }
        if (str_contains($action, 'transfer_packed') || str_contains($action, 'scan_out')) {
            return ['label' => 'Boxes scanned out', 'icon' => 'transfer', 'color' => 'blue'];
        }
        if (str_contains($action, 'transfer_received') || str_contains($action, 'scan_in')) {
            return ['label' => 'Transfer received', 'icon' => 'check', 'color' => 'green'];
        }
        if (str_contains($action, 'transfer_discrepancy') || str_contains($action, 'discrepancy')) {
            return ['label' => 'Discrepancy flagged', 'icon' => 'warning', 'color' => 'amber'];
        }
        if (str_contains($action, 'transfer')) {
            return ['label' => 'Transfer updated', 'icon' => 'transfer', 'color' => 'blue'];
        }

        // Sale events
        if (str_contains($action, 'sale_voided') || str_contains($action, 'voided')) {
            return ['label' => 'Sale voided', 'icon' => 'x', 'color' => 'red'];
        }
        if (str_contains($action, 'price_modified') || str_contains($action, 'price_override')) {
            return ['label' => 'Price modified', 'icon' => 'warning', 'color' => 'amber'];
        }
        if (str_contains($action, 'sale') || ($action === 'created' && $entity === 'sale')) {
            return ['label' => 'Sale completed', 'icon' => 'sale', 'color' => 'green'];
        }

        // Return events
        if (str_contains($action, 'return') && str_contains($action, 'approved')) {
            return ['label' => 'Return approved', 'icon' => 'check', 'color' => 'green'];
        }
        if ($action === 'approved' && str_contains($entity, 'return')) {
            return ['label' => 'Return approved', 'icon' => 'check', 'color' => 'green'];
        }
        if (str_contains($action, 'return')) {
            return ['label' => 'Return processed', 'icon' => 'return', 'color' => 'amber'];
        }

        // Box / inventory events
        if (str_contains($action, 'box_received') || ($action === 'created' && $entity === 'box')) {
            return ['label' => 'Box received', 'icon' => 'box', 'color' => 'blue'];
        }
        if (str_contains($action, 'box_damaged') || str_contains($action, 'damaged')) {
            return ['label' => 'Box damaged', 'icon' => 'warning', 'color' => 'red'];
        }
        if (str_contains($action, 'box_adjustment') || str_contains($action, 'adjustment')) {
            return ['label' => 'Inventory adjusted', 'icon' => 'box', 'color' => 'amber'];
        }
        if (str_contains($action, 'box')) {
            return ['label' => 'Box updated', 'icon' => 'box', 'color' => 'blue'];
        }

        // Damaged goods disposition
        if (str_contains($action, 'disposition') || str_contains($entity, 'damaged')) {
            return ['label' => 'Damage disposition', 'icon' => 'warning', 'color' => 'red'];
        }

        // Product / catalog
        if (str_contains($action, 'product') || $entity === 'product') {
            return ['label' => 'Product updated', 'icon' => 'product', 'color' => 'blue'];
        }

        // User / auth
        if (str_contains($action, 'login') || str_contains($action, 'logout')) {
            return ['label' => ucfirst($action), 'icon' => 'user', 'color' => 'default'];
        }

        // Generic created/updated/deleted
        if ($action === 'created') {
            return ['label' => ucfirst($entity ?: 'Record') . ' created', 'icon' => 'default', 'color' => 'blue'];
        }
        if ($action === 'updated') {
            return ['label' => ucfirst($entity ?: 'Record') . ' updated', 'icon' => 'default', 'color' => 'default'];
        }
        if ($action === 'deleted') {
            return ['label' => ucfirst($entity ?: 'Record') . ' deleted', 'icon' => 'x', 'color' => 'red'];
        }

        // Fallback — clean up underscores and capitalise
        return [
            'label' => ucfirst(str_replace(['_', '-'], ' ', $action)),
            'icon'  => 'default',
            'color' => 'default',
        ];
    }

    /**
     * Build a concise context line from entity_identifier + details JSON.
     * Examples:
     *   "TR-2026-03-00003 · 8 boxes · Remera Shop"
     *   "SL-2026-03-00041 · 55,000 RWF"
     *   "BOX-20260304-01620 · 6 items"
     */
    public function buildContext(ActivityLog $log): string
    {
        $parts = [];

        if ($log->entity_identifier) {
            $parts[] = $log->entity_identifier;
        }

        $details = $log->details ?? [];

        // Box count (transfers)
        if (!empty($details['box_count'])) {
            $parts[] = $details['box_count'] . ' box' . ($details['box_count'] > 1 ? 'es' : '');
        }

        // Shop / warehouse name
        if (!empty($details['shop_name'])) {
            $parts[] = $details['shop_name'];
        } elseif (!empty($details['warehouse_name'])) {
            $parts[] = $details['warehouse_name'];
        }

        // Sale total
        if (!empty($details['total'])) {
            $parts[] = number_format($details['total']) . ' RWF';
        }

        // Price diff (price modifications)
        if (!empty($details['diff_pct'])) {
            $parts[] = ($details['diff_pct'] > 0 ? '+' : '') . $details['diff_pct'] . '%';
        }

        // Quantity
        if (!empty($details['quantity'])) {
            $parts[] = $details['quantity'] . ' items';
        }

        // Refund amount (returns)
        if (!empty($details['refund_amount'])) {
            $parts[] = 'Refund: ' . number_format($details['refund_amount']) . ' RWF';
        }

        return implode(' · ', $parts);
    }

    public function render()
    {
        $activities = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

        return view('livewire.dashboard.activity-feed', [
            'activities' => $activities,
        ]);
    }
}
