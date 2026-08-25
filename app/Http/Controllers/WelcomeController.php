<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Interfaces\DashboardServiceInterface;
use App\Services\Interfaces\FileServiceInterface;

class WelcomeController extends Controller
{
    protected DashboardServiceInterface $dashboardService;
    protected FileServiceInterface $fileService;

    public function __construct(DashboardServiceInterface $dashboardService, FileServiceInterface $fileService)
    {
        $this->dashboardService = $dashboardService;
        $this->fileService = $fileService;
    }

    public function index()
    {
        $dashboard = $this->dashboardService->getWelcomeDashboardData(Auth::user());

        return view($dashboard['view'], $dashboard['data']);
    }

    /**
     * Dashboard publik (tanpa login) — hanya statistik agregat yang aman dilihat siapa saja,
     * lihat DashboardService::getGuestDashboardData(). Route ini di LUAR middleware 'auth'.
     */
    public function guestDashboard()
    {
        return view('guest.dashboard', $this->dashboardService->getGuestDashboardData());
    }

    public function landing()
    {
        // Landing page dihapus — root "/" selalu diarahkan ke /dashboard. Route /dashboard
        // ada di dalam middleware 'auth' (routes/web.php), yang otomatis redirect ke /login
        // untuk guest (lihat app/Http/Middleware/Authenticate::redirectTo()) — jadi tidak perlu
        // cek Auth::check() di sini lagi. Untuk user yang sudah login, /dashboard menampilkan
        // dashboard sesuai permission: role dashboard, atau dashboard/no-permission.blade.php
        // kalau akun tidak punya permission apa pun (lihat DashboardService).
        return redirect()->route('dashboard');
    }

    public function uploadFormulir(Request $request)
    {
        $request->validate([
            'file_formulir' => 'required|mimes:pdf|max:2048',
        ]);

        $result = $this->fileService->uploadFormulir($request->file('file_formulir'));

        return response()->json([
            'status' => $result['success'],
            'message' => $result['message']
        ], $result['success'] ? 200 : 400);
    }

    public function downloadFormulir()
    {
        $result = $this->fileService->downloadFormulir();

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        $headers = $result['headers'];

        return response()->download($result['path'], $result['downloadName'], $headers);
    }
}
