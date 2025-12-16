<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || $user->usertype !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required'
            ], 403);
        }

        return $next($request);
    }
}
