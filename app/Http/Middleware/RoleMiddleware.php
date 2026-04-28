<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        // User login check
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Role check
        if (auth()->user()->role !== $role) {
            abort(403, 'Unauthorized Access');
        }

        return $next($request);
    }
}
