<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperadminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hasSuperadminSession = $request->session()->get('superadmin_logged_in') === true;
        $currentUser = $request->user();

        if (! $hasSuperadminSession || ! $currentUser || ! $currentUser->isSuperadmin() || $currentUser->id !== $request->session()->get('superadmin_user_id')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Sesi Superadmin belum diautentikasi.',
                    'redirect' => route('superadmin.login'),
                ], 403);
            }

            return redirect()->route('superadmin.login')
                ->with('error', 'Silakan login terlebih dahulu untuk mengakses Control Center Superadmin.');
        }

        return $next($request);
    }
}
