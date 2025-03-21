<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();
        
        // If the user has any of the allowed roles, allow access
        foreach ($roles as $role) {
            if ($user->roles->contains('name', $role)) {
                return $next($request);
            }
        }

        // If the user doesn't have any allowed role, abort with 403 Forbidden
        abort(403, 'Unauthorized action. You do not have the necessary permissions.');
    }
}
