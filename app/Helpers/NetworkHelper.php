<?php

namespace App\Helpers;

class NetworkHelper
{
    /**
     * Get the accessible URL for mobile devices
     * Uses the server's IP address instead of localhost
     */
    public static function getAccessibleUrl(string $path = ''): string
    {
        $scheme = request()->getScheme();
        $host = request()->getHost();
        $port = request()->getPort();

        // If host is localhost or 127.0.0.1, try to get actual IP
        if (in_array($host, ['localhost', '127.0.0.1', '::1'])) {
            // Try to get server IP from $_SERVER
            $serverIp = $_SERVER['SERVER_ADDR'] ?? null;

            // Fallback: Get from environment or config
            if (!$serverIp || $serverIp === '127.0.0.1') {
                $serverIp = env('SERVER_IP', config('app.url'));
            }

            $host = $serverIp;
        }

        // Build base URL
        $baseUrl = $scheme . '://' . $host;

        // Add port if not default
        if ($port && !in_array($port, [80, 443])) {
            $baseUrl .= ':' . $port;
        }

        // Add path
        return $baseUrl . '/' . ltrim($path, '/');
    }

    /**
     * Get the current server's accessible IP address
     */
    public static function getServerIp(): string
    {
        // Try multiple sources
        $ip = $_SERVER['SERVER_ADDR'] ??
              $_SERVER['LOCAL_ADDR'] ??
              gethostbyname(gethostname());

        // If still localhost, return empty to indicate issue
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost', ''])) {
            return '';
        }

        return $ip;
    }
}
