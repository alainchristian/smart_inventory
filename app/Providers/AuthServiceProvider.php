<?php

namespace App\Providers;

use App\Models\BankDeposit;
use App\Models\Box;
use App\Models\DamagedGood;
use App\Models\DailySession;
use App\Models\Expense;
use App\Models\ExpenseRequest;
use App\Models\OwnerWithdrawal;
use App\Models\Product;
use App\Models\ReturnModel;
use App\Models\Sale;
use App\Models\Transfer;
use App\Policies\BankDepositPolicy;
use App\Policies\BoxPolicy;
use App\Policies\DamagedGoodPolicy;
use App\Policies\DailySessionPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\ExpenseRequestPolicy;
use App\Policies\OwnerWithdrawalPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ReturnPolicy;
use App\Policies\SalePolicy;
use App\Policies\TransferPolicy;
use App\Services\AuditLogger;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Transfer::class     => TransferPolicy::class,
        Sale::class         => SalePolicy::class,
        ReturnModel::class  => ReturnPolicy::class,
        Box::class          => BoxPolicy::class,
        Product::class      => ProductPolicy::class,
        DamagedGood::class  => DamagedGoodPolicy::class,
        DailySession::class    => DailySessionPolicy::class,
        Expense::class         => ExpensePolicy::class,
        ExpenseRequest::class  => ExpenseRequestPolicy::class,
        OwnerWithdrawal::class => OwnerWithdrawalPolicy::class,
        BankDeposit::class     => BankDepositPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('viewOwnerDashboard', fn (User $user) =>
            $user->isOwner()
        );

        Gate::define('viewPurchasePrice', fn (User $user) =>
            $user->isOwner()
        );

        Gate::define('open-daily-session', fn (User $user) =>
            $user->isShopManager() || $user->isOwner()
        );

        Gate::define('close-daily-session', fn (User $user) =>
            $user->isShopManager() || $user->isOwner()
        );

        Gate::define('create-expense-request', fn (User $user) =>
            $user->isWarehouseManager()
        );

        Gate::define('view-finance-reports', fn (User $user) =>
            $user->isOwner()
        );

        Gate::define('manage-daily-session', fn (User $user) =>
            $user->isShopManager() || $user->isOwner()
        );

        Gate::define('lock-daily-session', fn (User $user) =>
            $user->isOwner()
        );

        // Defensive coverage for the Policy classes registered above and any
        // future Gate::allows()/$this->authorize() calls. Note: this app's real
        // authorization currently happens via direct abort(403) in CheckRole/
        // CheckLocation middleware (see AuditLogger calls there) — this listener
        // only fires on genuine Gate/Policy checks, of which there are none yet
        // in the codebase, so it's inert today but ready if that changes.
        Gate::after(function (?User $user, string $ability, ?bool $result, mixed $arguments) {
            if ($result !== false) {
                return;
            }

            AuditLogger::log([
                'actor'             => $user,
                'action'            => 'permission_denied',
                'module'            => 'auth',
                'entity_type'       => is_object($arguments) ? class_basename($arguments) : 'Gate',
                'entity_identifier' => $ability,
                'details'           => ['ability' => $ability],
                'status'            => 'failed',
                'severity'          => 'warning',
            ]);
        });
    }
}
