<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsEmployer
{
    /**
     * Handle an incoming request — ensure user has an employer role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isEmployer()) {
            return response()->json([
                'success' => false,
                'error' => 'forbidden',
                'message' => 'This action requires an employer account.',
            ], 403);
        }

        return $next($request);
    }
}
