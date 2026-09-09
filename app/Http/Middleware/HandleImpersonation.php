<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleImpersonation
{
    /**
     * Share the impersonation state with all views so the banner can render.
     * The actual user-swapping happens in ImpersonateController via Auth::loginUsingId,
     * so nothing needs to be done here except expose the flag to Blade.
     */
    public function handle(Request $request, Closure $next): Response
    {
        view()->share('isImpersonating', $request->session()->has('impersonating_id'));
        view()->share('impersonatingAdminId', $request->session()->get('impersonating_id'));

        return $next($request);
    }
}
