<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\RequireTwoFactor;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\Totp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\HtmlString;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Two-step sign-in for platform support: an authenticator-app code (or a
 * one-time recovery code) after the password. See RequireTwoFactor.
 */
class TwoFactorController extends Controller
{
    private const PENDING_SECRET = 'two_factor_pending_secret';
    private const NEW_CODES      = 'two_factor_new_codes';
    private const MAX_ATTEMPTS   = 5;
    private const LOCK_SECONDS   = 900;

    // ── Code after the password ──────────────────────────────────────────────

    public function challenge(Request $request)
    {
        $user = $request->user();
        if ($redirect = $this->misrouted($request, needsSetup: false)) return $redirect;

        return $this->page('auth.two-factor.challenge', ['user' => $user]);
    }

    public function verify(Request $request)
    {
        $user = $request->user();
        if ($redirect = $this->misrouted($request, needsSetup: false)) return $redirect;

        // Counted per account, not per IP: the forwarded IP can be spoofed
        $key = 'two-factor|' . $user->id;
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
            return back()->withErrors(['code' => "Too many wrong codes. Try again in {$minutes} minute(s)."]);
        }

        $request->validate(['code' => 'required|string|max:32']);
        $input    = (string) $request->input('code');
        $recovery = $request->boolean('recovery');

        $ok = $recovery
            ? $this->useRecoveryCode($user, $input)
            : $this->useAuthenticatorCode($user, $input);

        if (! $ok) {
            RateLimiter::hit($key, self::LOCK_SECONDS);
            AuditLogger::log([
                'action' => 'two_factor_failed', 'module' => 'auth', 'entity' => $user,
                'entity_identifier' => $user->email, 'status' => 'failed', 'severity' => 'warning',
                'details' => ['method' => $recovery ? 'recovery_code' : 'authenticator'],
            ]);
            return back()->withErrors(['code' => $recovery ? 'That recovery code is not valid.' : 'That code is not valid. Check the code in your app and try again.'])
                ->withInput(['recovery' => $recovery ? 1 : 0]);
        }

        RateLimiter::clear($key);
        $this->pass($request, $user);

        if ($recovery) {
            $left = count($user->fresh()->two_factor_recovery_codes ?? []);
            AuditLogger::log([
                'action' => 'two_factor_recovery_used', 'module' => 'auth', 'entity' => $user,
                'entity_identifier' => $user->email, 'severity' => 'warning', 'details' => ['codes_left' => $left],
            ]);
        }

        return redirect()->route('dashboard');
    }

    // ── First-time setup ─────────────────────────────────────────────────────

    public function setup(Request $request)
    {
        $user = $request->user();
        if ($redirect = $this->misrouted($request, needsSetup: true)) return $redirect;

        $secret = $request->session()->get(self::PENDING_SECRET);
        if (! $secret) {
            $secret = Totp::generateSecret();
            $request->session()->put(self::PENDING_SECRET, $secret);
        }

        $uri = Totp::uri($secret, $user->email, config('tenant.name') ?: config('app.name'));

        return $this->page('auth.two-factor.setup', [
            'user'   => $user,
            'qr'     => QrCode::size(190)->margin(1)->generate($uri),
            'manual' => Totp::groupedSecret($secret),
        ]);
    }

    public function confirm(Request $request)
    {
        $user = $request->user();
        if ($redirect = $this->misrouted($request, needsSetup: true)) return $redirect;

        $request->validate(['code' => 'required|string|max:16']);

        $secret = $request->session()->get(self::PENDING_SECRET);
        $step   = $secret ? Totp::verify($secret, (string) $request->input('code')) : null;

        if ($step === null) {
            return back()->withErrors(['code' => 'That code is not valid. Scan the QR code again if needed, then enter the 6 digits shown in your app.']);
        }

        $codes = $this->newRecoveryCodes();

        $user->forceFill([
            'two_factor_secret'         => $secret,
            'two_factor_recovery_codes' => array_map(fn ($c) => Hash::make($c), $codes),
            'two_factor_confirmed_at'   => now(),
            'two_factor_last_step'      => $step,
        ])->save();

        $request->session()->forget(self::PENDING_SECRET);
        $request->session()->put(self::NEW_CODES, $codes);
        $this->pass($request, $user);

        AuditLogger::log([
            'action' => 'two_factor_enabled', 'module' => 'auth', 'entity' => $user,
            'entity_identifier' => $user->email, 'severity' => 'warning',
        ]);

        return redirect()->route('two-factor.codes');
    }

    // ── Recovery codes (shown once) ──────────────────────────────────────────

    public function codes(Request $request)
    {
        $codes = $request->session()->get(self::NEW_CODES);
        if (! $codes) {
            return redirect()->route('dashboard');
        }

        return $this->page('auth.two-factor.codes', ['codes' => $codes]);
    }

    public function codesDone(Request $request)
    {
        $request->session()->forget(self::NEW_CODES);

        return redirect()->route('dashboard');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Wrong page for this account's state: send it where it belongs. */
    private function misrouted(Request $request, bool $needsSetup)
    {
        $user = $request->user();

        if (! $user->requiresTwoFactor()) {
            return redirect()->route('dashboard');
        }
        if ($needsSetup && $user->hasTwoFactor()) {
            return redirect()->route($request->session()->get(RequireTwoFactor::SESSION_KEY) === $user->id ? 'dashboard' : 'two-factor.challenge');
        }
        if (! $needsSetup && ! $user->hasTwoFactor()) {
            return redirect()->route('two-factor.setup');
        }
        if (! $needsSetup && $request->session()->get(RequireTwoFactor::SESSION_KEY) === $user->id) {
            return redirect()->route('dashboard');
        }

        return null;
    }

    private function pass(Request $request, User $user): void
    {
        $request->session()->put(RequireTwoFactor::SESSION_KEY, $user->id);
        $request->session()->regenerate();
    }

    private function useAuthenticatorCode(User $user, string $input): bool
    {
        $step = Totp::verify((string) $user->two_factor_secret, $input, $user->two_factor_last_step);
        if ($step === null) {
            return false;
        }

        $user->forceFill(['two_factor_last_step' => $step])->save();

        return true;
    }

    private function useRecoveryCode(User $user, string $input): bool
    {
        $input = strtolower(preg_replace('/\s+/', '', $input));
        $codes = $user->two_factor_recovery_codes ?? [];

        foreach ($codes as $i => $hash) {
            if (Hash::check($input, $hash)) {
                unset($codes[$i]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();
                return true;
            }
        }

        return false;
    }

    /** 8 one-time codes like "3fa9c-81d2e". */
    private function newRecoveryCodes(): array
    {
        return collect(range(1, 8))->map(function () {
            $hex = bin2hex(random_bytes(5));
            return substr($hex, 0, 5) . '-' . substr($hex, 5);
        })->all();
    }

    /** Render a body inside the sign-in page's layout. */
    private function page(string $view, array $data)
    {
        return view('layouts.login', ['slot' => new HtmlString(view($view, $data)->render())]);
    }
}
