<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $userRole = Auth::user()->role;

        if (!in_array($userRole, $roles)) {
            return response()->json([
                'message' => 'Unauthorized role access'
            ], 403);
        }

        return $next($request);
    }
}