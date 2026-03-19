<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    private const CACHE_KEY = 'app_settings';
    private const CACHE_TTL = 3600; // 1 hour

    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();
        return $settings[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $setting = Setting::where('key', $key)->first();
        if (!$setting) return;

        // Serialize based on type
        $serialized = match ($setting->type) {
            'boolean' => $value ? 'true' : 'false',
            'json'    => json_encode($value),
            default   => (string) $value,
        };

        $setting->update(['value' => $serialized]);
        $this->clearCache();
    }

    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Setting::all()
                ->mapWithKeys(fn ($s) => [$s->key => $s->getTypedValue()])
                ->toArray();
        });
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // ── Convenience helpers ───────────────────────────────────────────────

    public function allowIndividualItemSales(): bool
    {
        return (bool) $this->get('allow_individual_item_sales', true);
    }

    public function individualSaleCategoryIds(): array
    {
        return (array) $this->get('individual_sale_category_ids', []);
    }

    public function categoryAllowsIndividualSales(int $categoryId): bool
    {
        if (!$this->allowIndividualItemSales()) return false;
        $allowed = $this->individualSaleCategoryIds();
        return empty($allowed) || in_array($categoryId, $allowed);
    }

    public function allowSellerReturns(): bool
    {
        return (bool) $this->get('allow_seller_returns', true);
    }

    public function returnApprovalThreshold(): int
    {
        return (int) $this->get('return_approval_threshold', 100000);
    }

    public function maxReturnDays(): int
    {
        return (int) $this->get('max_return_days', 30);
    }

    public function allowCreditSales(): bool
    {
        return (bool) $this->get('allow_credit_sales', true);
    }

    public function creditRequiresCustomer(): bool
    {
        return (bool) $this->get('credit_requires_customer', true);
    }

    public function maxCreditPerCustomer(): int
    {
        return (int) $this->get('max_credit_per_customer', 0);
    }

    public function priceOverrideThreshold(): int
    {
        return (int) $this->get('price_override_threshold', 20);
    }

    public function allowPriceOverride(): bool
    {
        return (bool) $this->get('allow_price_override', true);
    }
}
