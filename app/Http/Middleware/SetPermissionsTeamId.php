<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class SetPermissionsTeamId
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if ($user !== null) {
            app(PermissionRegistrar::class)->setPermissionsTeamId(
                $user->workspaces()->value('workspaces.id')
            );
        }

        return $next($request);
    }
}
