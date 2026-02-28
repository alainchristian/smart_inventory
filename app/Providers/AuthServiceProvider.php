<?php

namespace App\Providers;

use App\Models\Box;
use App\Models\DamagedGood;
use App\Models\Product;
use App\Models\ReturnModel;
use App\Models\Sale;
use App\Models\Transfer;
use App\Policies\BoxPolicy;
use App\Policies\DamagedGoodPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ReturnPolicy;
use App\Policies\SalePolicy;
use App\Policies\TransferPolicy;
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
        Transfer::class => TransferPolicy::class,
        Sale::class => SalePolicy::class,
        ReturnModel::class => ReturnPolicy::class,
        Box::class => BoxPolicy::class,
        Product::class => ProductPolicy::class,
        DamagedGood::class => DamagedGoodPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('viewOwnerDashboard', fn (User $user) =>
            $user->role === 'owner'
        );

        Gate::define('viewPurchasePrice', fn (User $user) =>
            $user->role === 'owner'
        );
    }
}
