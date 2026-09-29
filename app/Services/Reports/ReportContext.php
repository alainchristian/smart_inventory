<?php
namespace App\Services\Reports;

use App\Models\Shop;
use App\Models\Warehouse;

/**
 * What one block runs for: a business-tz date range and a location
 * filter ('all' | 'shop:ID' | 'warehouse:ID').
 */
final class ReportContext
{
    public function __construct(
        public readonly string $from,
        public readonly string $to,
        public readonly string $location = 'all',
    ) {}

    public function withLocation(string $location): self
    {
        return new self($this->from, $this->to, $location);
    }

    public function withDates(string $from, string $to): self
    {
        return new self($from, $to, $this->location);
    }

    public function shopId(): ?int
    {
        return str_starts_with($this->location, 'shop:') ? (int) substr($this->location, 5) : null;
    }

    public function warehouseId(): ?int
    {
        return str_starts_with($this->location, 'warehouse:') ? (int) substr($this->location, 10) : null;
    }

    public function periodLabel(): string
    {
        return ReportPeriod::label($this->from, $this->to);
    }

    public static function locationLabel(string $location): string
    {
        if ($location === 'all' || $location === '') {
            return 'All locations';
        }
        [$type, $id] = array_pad(explode(':', $location, 2), 2, null);

        return match ($type) {
            'shop'      => Shop::whereKey((int) $id)->value('name') ?? 'Unknown shop',
            'warehouse' => Warehouse::whereKey((int) $id)->value('name') ?? 'Unknown warehouse',
            default     => 'All locations',
        };
    }

    /** 'all', or a well-formed 'shop:N' / 'warehouse:N'. Anything else falls back to 'all'. */
    public static function normaliseLocation(?string $location): string
    {
        return is_string($location) && preg_match('/^(shop|warehouse):\d+$/', $location) ? $location : 'all';
    }
}
