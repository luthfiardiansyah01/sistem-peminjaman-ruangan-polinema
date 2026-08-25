<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeUser
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Akses ditolak. Silakan login terlebih dahulu.');
        }

        if (empty($roles)) {
            abort(403, 'Forbidden. Tidak ada role yang ditentukan untuk route ini.');
        }

        $userRole = $request->user()->getRole();

        if ($userRole && in_array($userRole, $roles)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}