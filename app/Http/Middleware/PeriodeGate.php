<?php

namespace App\Http\Middleware;

use App\Services\PeriodeService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gerbang Periode (Open/Closed) untuk seluruh transactional operation (create
 * pengajuan, proses approval, create/update/delete jadwal). Saat Periode
 * 'Closed', request diblokir di sini SEBELUM mencapai controller/service —
 * dipasang di routes/web.php pada masing-masing route transaksional, mengikuti
 * pola AuthorizeUser (middleware alias 'authorize').
 */
class PeriodeGate
{
    public function __construct(protected PeriodeService $periodeService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->periodeService->isOpen()) {
            return $next($request);
        }

        $message = 'Periode sedang ditutup. Operasi transaksional tidak dapat dilakukan saat ini.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => false, 'message' => $message], 423);
        }

        return back()->with('error', $message);
    }
}
