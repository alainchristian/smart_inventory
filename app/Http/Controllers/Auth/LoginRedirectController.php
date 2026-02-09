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

        // Use the getDashboardRoute method from User model
        return redirect($user->getDashboardRoute());
    }
}
