<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Console\Command;

/**
 * Lost phone and recovery codes: clear the account's two-step setup so the
 * next sign-in walks through setup again. Server access only, by design.
 */
class ResetSuperAdminTwoFactor extends Command
{
    protected $signature = 'account:reset-2fa {email : Email of the account (platform support, owner or admin)}';
    protected $aliases = ['super:reset-2fa'];
    protected $description = 'Clear two-step verification for a support, owner or admin account (it must be set up again at next sign-in)';

    public function handle(): int
    {
        $user = User::where('email', strtolower(trim($this->argument('email'))))
            ->whereIn('role', [UserRole::SUPER_ADMIN->value, UserRole::OWNER->value, UserRole::ADMIN->value])->first();

        if (! $user) {
            $this->error('No support, owner or admin account with that email.');
            return self::FAILURE;
        }

        $user->forceFill([
            'two_factor_secret' => null, 'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null, 'two_factor_last_step' => null,
        ])->save();

        AuditLogger::log([
            'actor_name' => $user->isSuperAdmin() ? 'Platform support' : 'Server command', 'action' => 'two_factor_reset', 'module' => 'auth',
            'entity' => $user, 'entity_identifier' => $user->email, 'severity' => 'warning',
            'details' => ['by' => 'server command'],
        ]);

        $this->info("Two-step verification cleared for {$user->email}. It must be set up again at the next sign-in.");
        return self::SUCCESS;
    }
}
