<?php

namespace App\Support;

use App\Models\User;

/**
 * Per-request state for platform support's read-only "view as" mode.
 * Bound as a scoped singleton, so it never leaks between requests.
 *
 * While active, auth()->user() is the TARGET account (so every screen shows
 * what that person sees) but nothing can be written: see
 * App\Http\Middleware\ViewAsMiddleware and the statement guard in
 * AppServiceProvider.
 */
class ViewAs
{
    public const SESSION_KEY = 'view_as_user_id';

    /** Tables that legitimately get written while just looking at a page. */
    public const WRITABLE_TABLES = ['cache', 'cache_locks'];

    private ?User $real   = null;
    private ?User $target = null;

    public function start(User $real, User $target): void
    {
        $this->real   = $real;
        $this->target = $target;
    }

    public function stop(): void
    {
        $this->real = $this->target = null;
    }

    public function active(): bool
    {
        return $this->target !== null;
    }

    /** The support account that is actually signed in. */
    public function real(): ?User
    {
        return $this->real;
    }

    /** The account whose screens are being shown. */
    public function target(): ?User
    {
        return $this->target;
    }

    /** True when this SQL would change data (and is not on the harmless list). */
    public static function isBlockedWrite(string $sql): bool
    {
        if (! preg_match('/^\s*(insert\s+into|update|delete\s+from|truncate(?:\s+table)?|alter\s+table|drop\s+table|create\s+table)\s+(?:only\s+)?"?([a-z0-9_]+)"?/i', $sql, $m)) {
            return false;
        }

        return ! in_array(strtolower($m[2]), self::WRITABLE_TABLES, true);
    }
}
