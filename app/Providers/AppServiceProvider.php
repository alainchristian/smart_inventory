<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
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
            'warehouse' => \App\Models\Warehouse::class,
            'shop' => \App\Models\Shop::class,
        ]);
    }
}
