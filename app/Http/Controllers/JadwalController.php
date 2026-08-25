<?php

namespace App\Http\Controllers;

use App\Services\ControllerResponseService;
use App\Services\JadwalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalController extends Controller
{
    public function __construct(protected JadwalService $jadwalService, protected ControllerResponseService $responses)
    {
    }

    public function index()
    {
        return view('jadwal.index', $this->jadwalService->indexData());
    }

    public function list(Request $request)
    {
        // Return empty DataTables-compatible response as the primary data
        // is rendered server-side in the index view via JadwalService::indexData().
        // The DataTable in the client is configured for client-side processing.
        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
        ]);
    }

    public function create_ajax(Request $request)
    {
        if (!in_array($request->user()->getRole(), ['ADM'])) {
            return response()->json(['status' => false, 'message' => 'Akses ditolak! Hanya Admin yang dapat menambah data jadwal.'], 403);
        }
        return $request->ajax() ? view('jadwal.create_ajax', $this->jadwalService->formData()) : redirect('/');
    }

    public function get_kelas_by_prodi($prodi_id)
    {
        return response()->json($this->jadwalService->kelasByProdi((int) $prodi_id));
    }

    public function store_ajax(Request $request)
    {
        if (!in_array($request->user()->getRole(), ['ADM'])) {
            return response()->json(['status' => false, 'message' => 'Akses ditolak! Hanya Admin yang dapat menyimpan data jadwal.'], 403);
        }
        return $this->responses->isAjax($request) ? $this->responses->jsonResult($this->jadwalService->create($request->all())) : redirect('/');
    }

    public function show_ajax(Request $request, string $id)
    {
        $jadwal = $this->jadwalService->findForShow((int) $id);
        if (!$jadwal) {
            return response()->json(['status' => false, 'message' => 'Data jadwal tidak ditemukan!'], 404);
        }
        return $request->ajax() ? view('jadwal.show_ajax', compact('jadwal')) : redirect('/');
    }

    public function edit_ajax(Request $request, string $id)
    {
        if (!in_array($request->user()->getRole(), ['ADM'])) {
            return response()->json(['status' => false, 'message' => 'Akses ditolak! Hanya Admin yang dapat mengubah data jadwal.'], 403);
        }
        $data = $this->jadwalService->formData((int) $id);
        if (!$data['jadwal']) {
            return response()->json(['status' => false, 'message' => 'Data jadwal tidak ditemukan!'], 404);
        }
        return $request->ajax() ? view('jadwal.edit_ajax', $data) : redirect('/');
    }

    public function update_ajax(Request $request, $id)
    {
        if (!in_array($request->user()->getRole(), ['ADM'])) {
            return response()->json(['status' => false, 'message' => 'Akses ditolak! Hanya Admin yang dapat mengupdate data jadwal.'], 403);
        }
        return $this->responses->isAjax($request) ? $this->responses->jsonResult($this->jadwalService->update((int) $id, $request->all())) : redirect('/');
    }

    public function confirm_ajax(Request $request, string $id)
    {
        if (!in_array($request->user()->getRole(), ['ADM'])) {
            return response()->json(['status' => false, 'message' => 'Akses ditolak! Hanya Admin yang dapat menghapus data jadwal.'], 403);
        }
        $jadwal = $this->jadwalService->findForConfirm((int) $id);
        if (!$jadwal) {
            return response()->json(['status' => false, 'message' => 'Data jadwal tidak ditemukan!'], 404);
        }
        return view('jadwal.confirm_ajax', compact('jadwal'));
    }

    public function delete_ajax(Request $request, $id)
    {
        if (!in_array($request->user()->getRole(), ['ADM'])) {
            return response()->json(['status' => false, 'message' => 'Akses ditolak! Hanya Admin yang dapat menghapus data jadwal.'], 403);
        }
        return $this->responses->isAjax($request) ? $this->responses->jsonResult($this->jadwalService->delete((int) $id)) : redirect('/');
    }

    public function update_status_ajax(Request $request, $id)
    {
        if (!in_array($request->user()->getRole(), ['ADM'])) {
            return response()->json(['status' => false, 'message' => 'Akses ditolak! Hanya Admin yang dapat mengubah status jadwal.'], 403);
        }
        return $this->responses->isAjax($request) ? $this->responses->jsonResult($this->jadwalService->updateStatus((int) $id, (string) $request->input('jadwal_status', ''))) : redirect('/');
    }
}
