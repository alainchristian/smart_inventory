<?php

namespace App\Services;

use App\Models\User;
use DomainException;

/**
 * Clears an account's two-step sign-in so it must be set up again at the next
 * sign-in (lost phone and lost recovery codes). One path for the server
 * command, the owner's Users screen and the support console.
 *
 * Who may reset whom:
 *  - platform support: any owner or admin
 *  - an owner: an admin or another owner (never their own: that would defeat it)
 *  - an admin: nobody
 *  - the server command (no actor): any support, owner or admin
 */
class TwoFactorReset
{
    public static function allowed(?User $actor, User $target): bool
    {
        if (! $target->isSuperUser() || ! $target->hasTwoFactor()) {
            return false;
        }
        if ($actor === null) {
            return true;
        }
        if ($actor->id === $target->id) {
            return false;
        }

        return $actor->isSuperAdmin() || $actor->isOwner();
    }

    /** @throws DomainException when the actor may not do this */
    public function reset(User $target, ?User $actor, string $via): void
    {
        if (! self::allowed($actor, $target)) {
            throw new DomainException('You cannot reset two-step sign-in for this account.');
        }

        $target->forceFill([
            'two_factor_secret' => null, 'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null, 'two_factor_last_step' => null,
        ])->save();

        AuditLogger::log([
            'action' => 'two_factor_reset', 'module' => 'auth',
            'entity' => $target, 'entity_identifier' => $target->email, 'severity' => 'warning',
            'details' => ['by' => $via, 'reset_by' => $actor?->name],
        ]);
    }
}
