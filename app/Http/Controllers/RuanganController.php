<?php

namespace App\Http\Controllers;

use App\Services\ControllerResponseService;
use App\Services\RuanganService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RuanganController extends Controller
{
    public function __construct(protected RuanganService $ruanganService, protected ControllerResponseService $responses)
    {
    }

    public function index()
    {
        return view('ruangan.index', $this->ruanganService->indexData());
    }

    public function list(Request $request)
    {
        try {
            return $this->ruanganService->dataTable(Auth::user()->getRole());
        } catch (\Throwable $e) {
            return $this->responses->dataTableError($request, $e);
        }
    }

    public function create_ajax(Request $request)
    {
        return $request->ajax() ? view('ruangan.create_ajax') : redirect('/');
    }

    public function store_ajax(Request $request)
    {
        // Req 2: kirim semua file ruangan_foto[] sebagai array
        $fotos = $request->file('ruangan_foto');
        if ($fotos && !is_array($fotos)) {
            $fotos = [$fotos];
        }
        return $this->responses->jsonResult($this->ruanganService->create($request->all(), $fotos ?? []), 201);
    }

    public function show_ajax(Request $request, string $id)
    {
        return $this->ruanganModal($request, $id, 'ruangan.show_ajax');
    }

    public function edit_ajax(Request $request, string $id)
    {
        return $this->ruanganModal($request, $id, 'ruangan.edit_ajax');
    }

    public function update_ajax(Request $request, $id)
    {
        if (!$this->responses->isAjax($request)) {
            return redirect('/');
        }
        // Req 2: kirim file baru dan daftar foto yang dihapus ke service
        $newFotos = $request->file('ruangan_foto_baru') ?? [];
        if ($newFotos && !is_array($newFotos)) {
            $newFotos = [$newFotos];
        }
        $deletedFotos = $request->input('hapus_foto', []);
        return $this->responses->jsonResult(
            $this->ruanganService->update((int) $id, $request->all(), $newFotos, $deletedFotos)
        );
    }

    public function confirm_ajax(Request $request, string $id)
    {
        return $this->ruanganModal($request, $id, 'ruangan.confirm_ajax');
    }

    public function delete_ajax(Request $request, $id)
    {
        return $this->responses->isAjax($request) ? $this->responses->jsonResult($this->ruanganService->delete((int) $id)) : redirect('/');
    }

    /**
     * Status ketersediaan seluruh ruangan untuk satu tanggal (poin 2) — dipakai widget
     * di form pengajuan saat memilih tanggal.
     */
    public function availability_ajax(Request $request)
    {
        $tanggal = (string) $request->query('tanggal');
        if (!$tanggal || !strtotime($tanggal)) {
            return response()->json(['status' => false, 'message' => 'Parameter tanggal wajib diisi dan valid.'], 422);
        }

        return response()->json($this->ruanganService->availabilityForAllRoomsOnDate($tanggal));
    }

    public function kalender_ajax(Request $request, string $id)
    {
        return $this->ruanganModal($request, $id, 'ruangan.kalender_ajax');
    }

    /**
     * Status ketersediaan satu ruangan untuk satu bulan (poin 2) — dipakai grid kalender.
     */
    public function kalender_data_ajax(Request $request, string $id)
    {
        $bulan = (string) $request->query('bulan'); // format Y-m
        if (!preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            return response()->json(['status' => false, 'message' => 'Parameter bulan wajib format Y-m.'], 422);
        }

        $ruangan = $this->ruanganService->find((int) $id);
        if (!$ruangan) {
            return $this->notFoundErrorResponse('Data ruangan tidak ditemukan!', 'ruangan', $id);
        }

        [$year, $month] = array_map('intval', explode('-', $bulan));
        return response()->json($this->ruanganService->monthlyAvailability($ruangan, $year, $month));
    }

    protected function ruanganModal(Request $request, string $id, string $view)
    {
        $ruangan = $this->ruanganService->find((int) $id);
        if (!$ruangan) {
            return $this->notFoundErrorResponse('Data ruangan tidak ditemukan!', 'ruangan', $id);
        }
        return $request->ajax() ? view($view, compact('ruangan')) : redirect('/');
    }
}
