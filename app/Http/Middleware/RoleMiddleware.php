<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage: middleware('role:admin') — checks is_admin flag.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role = 'admin'): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'error'   => 'unauthenticated',
                'message' => 'Authentication required.',
            ], 401);
        }

        if ($role === 'admin' && ! $user->is_admin) {
            return response()->json([
                'success' => false,
                'error'   => 'forbidden',
                'message' => 'Admin access required.',
            ], 403);
        }

        return $next($request);
    }
}
