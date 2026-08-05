<?php

namespace App\Http\Middleware;

use App\Enums\LocationType;
use App\Services\AuditLogger;
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
                $this->logDenied($request, $user, 'warehouse', $warehouseId);
                abort(403, 'You do not have access to this warehouse.');
            }
        }

        // Check shop access
        if ($shopId) {
            if ($user->location_type !== LocationType::SHOP || $user->location_id != $shopId) {
                $this->logDenied($request, $user, 'shop', $shopId);
                abort(403, 'You do not have access to this shop.');
            }
        }

        // Generic location check
        if ($locationId) {
            if ($user->location_id != $locationId) {
                $this->logDenied($request, $user, 'location', $locationId);
                abort(403, 'You do not have access to this location.');
            }
        }

        return $next($request);
    }

    private function logDenied(Request $request, $user, string $locationKind, $requestedLocationId): void
    {
        AuditLogger::log([
            'actor'             => $user,
            'action'            => 'permission_denied',
            'module'            => 'auth',
            'entity_type'       => 'Route',
            'entity_identifier' => $request->path(),
            'details'           => [
                'location_kind'      => $locationKind,
                'requested_location' => $requestedLocationId,
                'user_location_type' => $user->location_type?->value,
                'user_location_id'   => $user->location_id,
            ],
            'status'            => 'failed',
            'severity'          => 'warning',
        ]);
    }
}
