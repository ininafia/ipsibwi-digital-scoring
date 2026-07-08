<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!session('user_id')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            return redirect()->route('login');
        }

        $userRole = (string) session('role');
        
        if (!empty($roles) && !in_array($userRole, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
            }
            abort(403, 'Akses ditolak');
        }

        return $next($request);
    }
}
