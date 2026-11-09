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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (empty($roles)) {
            return $next($request);
        }

        // Check if user role matches any allowed role
        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        // Bagian Finance can access routes requiring 'finance'
        if (in_array('finance', $roles, true) && $user->isFinance()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Akses ditolak. Peran Anda tidak memiliki otoritas untuk aksi ini.'
            ], 403);
        }

        abort(403, 'Akses ditolak. Peran Anda (' . ucwords(str_replace('_', ' ', $user->role)) . ') tidak memiliki wewenang untuk mengakses menu ini.');
    }
}
