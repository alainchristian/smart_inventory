<?php

namespace App\Enums;

enum AlertSeverity: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match($this) {
            self::INFO => 'Info',
            self::WARNING => 'Warning',
            self::CRITICAL => 'Critical',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::INFO => 'blue',
            self::WARNING => 'yellow',
            self::CRITICAL => 'red',
        };
    }
}