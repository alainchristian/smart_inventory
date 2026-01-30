<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginRedirectController extends Controller
{
    /**
     * Redirect user to appropriate dashboard based on their role
     */
    public function redirect()
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Update last login timestamp
        $user->recordLogin();

        // Redirect based on role
        return match ($user->role) {
            UserRole::OWNER => redirect()->route('owner.dashboard'),
            UserRole::WAREHOUSE_MANAGER => redirect()->route('warehouse.dashboard'),
            UserRole::SHOP_MANAGER => redirect()->route('shop.dashboard'),
            default => redirect()->route('login')->with('error', 'Invalid user role'),
        };
    }
}
