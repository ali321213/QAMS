<?php

namespace App\Http\Middleware;
<<<<<<< Updated upstream
=======

>>>>>>> Stashed changes
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
<<<<<<< Updated upstream
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! Auth::check() || Auth::user()->role !== $role) {
            return redirect()->route('dashboard');
        }
        return $next($request);
    }
}
=======
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check() || ! in_array(Auth::user()->role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
>>>>>>> Stashed changes
