<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * "Trust this device for 30 days" for owners and admins: a browser that passed
 * the authenticator code once is not asked again until the trust runs out.
 *
 * The cookie holds a random token; only its hash is stored, so a database copy
 * can't be turned into a working cookie. The token is replaced every time it is
 * used, but the expiry stays fixed from the day the device was trusted.
 * Never offered to platform support.
 */
class TrustedDevice extends Model
{
    public const COOKIE = 'trusted_device';
    public const DAYS   = 30;

    protected $fillable = ['user_id', 'token_hash', 'user_agent', 'ip_address', 'last_used_at', 'expires_at'];

    protected $casts = ['last_used_at' => 'datetime', 'expires_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    /** Trust the browser that is making this request, and queue its cookie. */
    public static function trust(User $user, Request $request): self
    {
        $device = self::create([
            'user_id'      => $user->id,
            'token_hash'   => self::hash($token = Str::random(60)),
            'user_agent'   => Str::limit((string) $request->userAgent(), 250, ''),
            'ip_address'   => $request->ip(),
            'last_used_at' => now(),
            'expires_at'   => now()->addDays(self::DAYS),
        ]);

        self::queueCookie($token, $device->expires_at);

        return $device;
    }

    /** The trusted device this request's cookie belongs to for this user, if still valid. */
    public static function forRequest(User $user, Request $request): ?self
    {
        $token = $request->cookie(self::COOKIE);
        if (! is_string($token) || $token === '') {
            return null;
        }

        return self::where('user_id', $user->id)
            ->where('token_hash', self::hash($token))
            ->where('expires_at', '>', now())
            ->first();
    }

    /** Use it: replace the token and note the time. Expiry does not move. */
    public function touchAndRotate(Request $request): void
    {
        $this->forceFill([
            'token_hash'   => self::hash($token = Str::random(60)),
            'last_used_at' => now(),
            'ip_address'   => $request->ip(),
            'user_agent'   => Str::limit((string) $request->userAgent(), 250, ''),
        ])->save();

        self::queueCookie($token, $this->expires_at);
    }

    public static function forget(): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE));
    }

    private static function queueCookie(string $token, $expiresAt): void
    {
        $minutes = max(1, (int) now()->diffInMinutes($expiresAt, false));

        // Encrypted by the framework; HttpOnly so page scripts can't read it
        Cookie::queue(Cookie::make(self::COOKIE, $token, $minutes, '/', null, request()->isSecure(), true, false, 'lax'));
    }
}
