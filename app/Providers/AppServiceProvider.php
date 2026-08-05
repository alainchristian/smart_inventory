<?php

namespace App\Providers;

use App\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
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

        Event::listen(Login::class, function (Login $event) {
            AuditLogger::log([
                'actor'             => $event->user,
                'action'            => 'signed_in',
                'module'            => 'auth',
                'entity_type'       => 'User',
                'entity_id'         => $event->user->id,
                'entity_identifier' => $event->user->email,
            ]);
        });

        Event::listen(Logout::class, function (Logout $event) {
            if (!$event->user) {
                return;
            }

            AuditLogger::log([
                'actor'             => $event->user,
                'action'            => 'signed_out',
                'module'            => 'auth',
                'entity_type'       => 'User',
                'entity_id'         => $event->user->id,
                'entity_identifier' => $event->user->email,
            ]);
        });

        Event::listen(Failed::class, function (Failed $event) {
            AuditLogger::log([
                'actor'             => $event->user,
                'actor_name'        => $event->user?->name ?? ($event->credentials['email'] ?? 'Unknown'),
                'action'            => 'failed_login',
                'module'            => 'auth',
                'entity_type'       => 'User',
                'entity_id'         => $event->user?->id,
                'entity_identifier' => $event->credentials['email'] ?? null,
                'status'            => 'failed',
                'severity'          => 'warning',
            ]);
        });

        Event::listen(Lockout::class, function (Lockout $event) {
            AuditLogger::log([
                'action'            => 'lockout',
                'module'            => 'auth',
                'entity_type'       => 'User',
                'entity_identifier' => $event->request->input('email'),
                'status'            => 'failed',
                'severity'          => 'critical',
            ]);
        });
    }
}
