<?php

namespace App\Http\Middleware;

use App\Enums\LocationType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLocation
{
    /**
     * Handle an incoming request.
     *
     * Ensures that non-owner users can only access resources from their assigned location.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Owners can access all locations
        if ($user->isOwner()) {
            return $next($request);
        }

        // Get location ID from route parameters
        $warehouseId = $request->route('warehouse');
        $shopId = $request->route('shop');
        $locationId = $request->route('location');

        // Check warehouse access
        if ($warehouseId) {
            if ($user->location_type !== LocationType::WAREHOUSE || $user->location_id != $warehouseId) {
                abort(403, 'You do not have access to this warehouse.');
            }
        }

        // Check shop access
        if ($shopId) {
            if ($user->location_type !== LocationType::SHOP || $user->location_id != $shopId) {
                abort(403, 'You do not have access to this shop.');
            }
        }

        // Generic location check
        if ($locationId) {
            if ($user->location_id != $locationId) {
                abort(403, 'You do not have access to this location.');
            }
        }

        return $next($request);
    }
}
