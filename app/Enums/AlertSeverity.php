<?php

namespace App\Enums;

enum AlertSeverity: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case CRITICAL = 'critical';
    case SUCCESS = 'success';

    public function label(): string
    {
        return match($this) {
            self::INFO => 'Info',
            self::WARNING => 'Warning',
            self::CRITICAL => 'Critical',
            self::SUCCESS => 'Success',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::INFO => 'blue',
            self::WARNING => 'yellow',
            self::CRITICAL => 'red',
            self::SUCCESS => 'green',
        };
    }
}