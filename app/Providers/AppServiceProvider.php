<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure morph map for polymorphic relationships
        // This maps the enum values ('warehouse', 'shop') to actual model classes
        Relation::enforceMorphMap([
            'warehouse'        => \App\Models\Warehouse::class,
            'shop'             => \App\Models\Shop::class,
            'user'             => \App\Models\User::class,
            'daily_session'    => \App\Models\DailySession::class,
            'expense'          => \App\Models\Expense::class,
            'expense_category' => \App\Models\ExpenseCategory::class,
            'expense_request'  => \App\Models\ExpenseRequest::class,
            'owner_withdrawal' => \App\Models\OwnerWithdrawal::class,
            'customer'         => \App\Models\Customer::class,
            'sale'             => \App\Models\Sale::class,
        ]);

        // Force HTTPS for all URLs when behind ngrok or other proxy
        // This fixes Livewire file upload mixed content errors
        if (request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }
    }
}
