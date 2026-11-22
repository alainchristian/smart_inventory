<?php

namespace App\Support;

/**
 * One place for the password rule, so the Users screen, the support console,
 * the forced change-password page and password reset can't drift apart.
 * Existing passwords are untouched until they are next changed.
 */
final class PasswordPolicy
{
    public const MIN_LENGTH = 12;

    /** For string-style validation rules: 'required|string|' . PasswordPolicy::rule() */
    public static function rule(): string
    {
        return 'min:' . self::MIN_LENGTH;
    }

    public static function message(): string
    {
        return 'Password must be at least ' . self::MIN_LENGTH . ' characters.';
    }

    public static function hint(): string
    {
        return 'Min. ' . self::MIN_LENGTH . ' characters';
    }
}
