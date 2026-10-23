<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * The only way a platform-support (super_admin) account is created or removed.
 * There is deliberately no screen for it, so no owner or admin can grant it.
 */
class CreateSuperAdmin extends Command
{
    protected $signature = 'super:create {email : Login email} {--name=Platform Support : Display name} {--remove : Delete the account instead}';
    protected $description = 'Create (or remove) a platform-support account that manages users but cannot operate the business';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));

        if ($this->option('remove')) {
            $user = User::where('email', $email)->where('role', UserRole::SUPER_ADMIN->value)->first();
            if (! $user) {
                $this->error("No platform-support account with email {$email}.");
                return self::FAILURE;
            }
            $user->forceDelete();
            AuditLogger::log([
                'actor_name' => 'Platform support', 'action' => 'support_account_removed', 'module' => 'users',
                'entity_type' => 'User', 'entity_identifier' => $email, 'severity' => 'warning',
            ]);
            $this->info("Removed {$email}.");
            return self::SUCCESS;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('That is not a valid email address.');
            return self::FAILURE;
        }
        if (User::withTrashed()->where('email', $email)->exists()) {
            $this->error('That email already belongs to an account. Use a different one.');
            return self::FAILURE;
        }

        $password = $this->secret('Password (min 12 characters)');
        if (strlen((string) $password) < 12) {
            $this->error('The password must be at least 12 characters.');
            return self::FAILURE;
        }
        if ($password !== $this->secret('Repeat password')) {
            $this->error('The passwords do not match.');
            return self::FAILURE;
        }

        $user = User::create([
            'name'                 => $this->option('name'),
            'email'                => $email,
            'password'             => Hash::make($password),
            'role'                 => UserRole::SUPER_ADMIN,
            'is_active'            => true,
            'must_change_password' => false,
        ]);

        AuditLogger::log([
            'actor_name' => 'Platform support', 'action' => 'support_account_created', 'module' => 'users',
            'entity' => $user, 'entity_identifier' => $email, 'severity' => 'warning',
        ]);

        $this->info("Platform-support account created for {$email}. Sign in at /login.");
        return self::SUCCESS;
    }
}
