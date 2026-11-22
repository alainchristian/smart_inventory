<?php

namespace App\Livewire\Forms;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /** Failed attempts allowed per email (from any IP) before it is locked for a while. */
    public const MAX_FAILS_PER_EMAIL = 10;
    public const EMAIL_LOCK_SECONDS  = 900;

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only(['email', 'password']), $this->remember)) {
            RateLimiter::hit($this->throttleKey());
            RateLimiter::hit(self::emailThrottleKey($this->email), self::EMAIL_LOCK_SECONDS);

            throw ValidationException::withMessages([
                'form.email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        RateLimiter::clear(self::emailThrottleKey($this->email));
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        // Two limits: 5 tries a minute per email + IP, and a cap per email from ANY
        // IP, so guessing one account's password from many addresses doesn't get
        // 5 free tries per address.
        $ipKeyLimited    = RateLimiter::tooManyAttempts($this->throttleKey(), 5);
        $emailKeyLimited = RateLimiter::tooManyAttempts(self::emailThrottleKey($this->email), self::MAX_FAILS_PER_EMAIL);

        if (! $ipKeyLimited && ! $emailKeyLimited) {
            return;
        }

        event(new Lockout(request()));

        $seconds = max(
            $ipKeyLimited ? RateLimiter::availableIn($this->throttleKey()) : 0,
            $emailKeyLimited ? RateLimiter::availableIn(self::emailThrottleKey($this->email)) : 0,
        );

        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /** Throttle key for an email on its own, whatever the IP. */
    public static function emailThrottleKey(string $email): string
    {
        return 'login-email|' . Str::transliterate(Str::lower(trim($email)));
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
