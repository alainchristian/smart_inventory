<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\ViewAs;
use Illuminate\Http\Request;

class ViewAsController extends Controller
{
    public function start(Request $request, int $user)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $target = User::visibleToBusiness()->where('is_active', true)->findOrFail($user);

        $request->session()->put(ViewAs::SESSION_KEY, $target->id);

        AuditLogger::log([
            'action'            => 'support_view_as_started',
            'module'            => 'users',
            'entity'            => $target,
            'entity_identifier' => $target->email,
            'details'           => ['role' => $target->role->value, 'read_only' => true],
        ]);

        return redirect($target->getDashboardRoute());
    }

    public function stop(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        if ($id = $request->session()->pull(ViewAs::SESSION_KEY)) {
            $target = User::find($id);
            AuditLogger::log([
                'action'            => 'support_view_as_stopped',
                'module'            => 'users',
                'entity_type'       => 'User',
                'entity_id'         => $id,
                'entity_identifier' => $target?->email,
            ]);
        }

        return redirect()->route('support.accounts');
    }
}
