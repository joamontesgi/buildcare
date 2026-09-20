<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Permite acceso solo a usuarios con rol admin o scheduler_coordinator.
 * Aplicado a las rutas de schedule-days.
 */
class EnsureUserCanManageSchedule
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->canManageSchedule()) {
            abort(
                Response::HTTP_FORBIDDEN,
                'Solo los administradores y los Scheduler Coordinators pueden gestionar la agenda.'
            );
        }

        return $next($request);
    }
}
