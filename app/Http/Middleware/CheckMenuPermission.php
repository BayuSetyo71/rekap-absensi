<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $menuIdentifier (code menu, misal: 'menus', 'roles', 'users')
     * @param  string  $action ('view', 'create', 'update', 'delete', 'export')
     */
    public function handle(Request $request, Closure $next, string $menuIdentifier, string $action = 'view'): Response
    {
        $user = Auth::user();

        // Jika belum login, redirect ke halaman login
        if (!$user) {
            return redirect()->route('login');
        }

        // Cek apakah akun aktif
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda dinonaktifkan. Silakan hubungi administrator.',
            ]);
        }

        // Cek izin akses menu & aksi
        if (!$user->canAccessMenu($menuIdentifier, $action)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki izin (' . strtoupper($action) . ') untuk mengakses modul ini.',
                ], 403);
            }

            abort(403, 'Akses Ditolak: Anda tidak memiliki izin (' . strtoupper($action) . ') untuk mengakses menu/halaman ini.');
        }

        return $next($request);
    }
}
