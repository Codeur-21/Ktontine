<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user(), 403);
        $request->user()->activeTenantId();

        return $next($request);
    }
}
