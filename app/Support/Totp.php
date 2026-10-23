<?php

namespace App\Support;

/**
 * Time-based one-time passwords (RFC 6238, SHA-1, 6 digits, 30 s) — what
 * Google Authenticator, Microsoft Authenticator and Authy all speak.
 */
class Totp
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    private const PERIOD   = 30;
    private const DIGITS   = 6;

    /** A new random secret, base32 (160 bits). */
    public static function generateSecret(): string
    {
        return self::base32Encode(random_bytes(20));
    }

    /** The code for one 30-second step (counter). */
    public static function codeForStep(string $secret, int $step): string
    {
        $hash   = hash_hmac('sha1', pack('J', $step), self::base32Decode($secret), true);
        $offset = ord($hash[19]) & 0x0F;
        $value  = ((ord($hash[$offset]) & 0x7F) << 24)
                | (ord($hash[$offset + 1]) << 16)
                | (ord($hash[$offset + 2]) << 8)
                |  ord($hash[$offset + 3]);

        return str_pad((string) ($value % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    public static function stepAt(?int $time = null): int
    {
        return intdiv($time ?? time(), self::PERIOD);
    }

    /**
     * Check a typed code. Allows one step either side for clock drift, and
     * refuses any step at or before $afterStep so a code can't be used twice.
     *
     * @return int|null the step that matched (store it as the new "last used"), or null
     */
    public static function verify(string $secret, string $input, ?int $afterStep = null, ?int $time = null, int $window = 1): ?int
    {
        $input = preg_replace('/\s+/', '', $input);
        if (! preg_match('/^\d{6}$/', (string) $input)) {
            return null;
        }

        $now = self::stepAt($time);
        for ($i = -$window; $i <= $window; $i++) {
            $step = $now + $i;
            if ($afterStep !== null && $step <= $afterStep) {
                continue;
            }
            if (hash_equals(self::codeForStep($secret, $step), $input)) {
                return $step;
            }
        }

        return null;
    }

    /** The otpauth:// link the authenticator app reads from the QR code. */
    public static function uri(string $secret, string $account, string $issuer): string
    {
        return 'otpauth://totp/' . rawurlencode($issuer . ':' . $account)
            . '?secret=' . $secret
            . '&issuer=' . rawurlencode($issuer)
            . '&algorithm=SHA1&digits=' . self::DIGITS . '&period=' . self::PERIOD;
    }

    /** "ABCD EFGH …" for typing by hand. */
    public static function groupedSecret(string $secret): string
    {
        return trim(chunk_split($secret, 4, ' '));
    }

    public static function base32Encode(string $bytes): string
    {
        $bits = '';
        foreach (str_split($bytes) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $out = '';
        foreach (str_split($bits, 5) as $chunk) {
            $out .= self::ALPHABET[bindec(str_pad($chunk, 5, '0'))];
        }

        return $out;
    }

    public static function base32Decode(string $text): string
    {
        $bits = '';
        foreach (str_split(strtoupper(rtrim($text, '='))) as $char) {
            $pos = strpos(self::ALPHABET, $char);
            if ($pos === false) {
                continue;
            }
            $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $out = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $out .= chr(bindec($byte));
            }
        }

        return $out;
    }
}
