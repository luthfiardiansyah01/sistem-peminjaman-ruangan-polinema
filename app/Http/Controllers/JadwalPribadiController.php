<?php

namespace App\Http\Controllers;

use App\Services\ControllerResponseService;
use App\Services\JadwalPribadiService;
use App\Services\JadwalService;
use Illuminate\Http\Request;

class JadwalPribadiController extends Controller
{
    protected JadwalPribadiService $pribadiService;
    protected JadwalService $jadwalService;
    protected ControllerResponseService $responses;

    public function __construct(
        JadwalPribadiService $pribadiService,
        JadwalService $jadwalService,
        ControllerResponseService $responses
    ) {
        $this->pribadiService = $pribadiService;
        $this->jadwalService = $jadwalService;
        $this->responses = $responses;
    }

    /**
     * ==========================================================
     * INDEX
     * ==========================================================
     */
    public function index()
    {
        return view(
            'jadwal.pribadi.index',
            $this->pribadiService->indexData()
        );
    }

    /**
     * ==========================================================
     * CREATE
     * ==========================================================
     */
    public function create_ajax(Request $request)
    {
        return $request->ajax()
            ? view(
                'jadwal.pribadi.create_ajax',
                $this->jadwalService->formData()
            )
            : redirect('/');
    }

    /**
     * ==========================================================
     * STORE
     * ==========================================================
     */
    public function store_ajax(Request $request)
    {
        $payload = $this->pribadiService
            ->requestData($request);

        return $this->responses->isAjax($request)
            ? $this->responses->jsonResult(
                $this->jadwalService->create($payload)
            )
            : redirect('/');
    }

    /**
     * ==========================================================
     * SHOW
     * ==========================================================
     */
    public function show_ajax(Request $request, int $id)
    {
        $jadwal = $this->pribadiService->authorizeOwner($id);

        $jadwal->load([
            'user.level',
            'user.mahasiswa.prodi',
            'user.mahasiswa.kelas',
            'ruangans'
        ]);

        if (!$jadwal) {
            return response()->json([
                'status' => false,
                'message' => 'Data jadwal tidak ditemukan.'
            ], 404);
        }

        return $request->ajax()
            ? view(
                'jadwal.show_ajax',
                compact('jadwal')
            )
            : redirect('/');
    }

    /**
     * ==========================================================
     * EDIT
     * ==========================================================
     */
    public function edit_ajax(Request $request, int $id)
    {
        if ($request->user()->getRole() === \App\Constants\RoleConstants::ADMIN) {
            abort(403, 'Role ADM tidak memiliki akses untuk mengubah jadwal pribadi.');
        }

        $jadwal = $this->pribadiService->authorizeOwner($id);

        $data = $this->jadwalService->formData($id);

        $data['jadwal'] = $jadwal;

        if (!$data['jadwal']) {
            return response()->json([
                'status' => false,
                'message' => 'Data jadwal tidak ditemukan.'
            ], 404);
        }

        return $request->ajax()
            ? view(
                'jadwal.pribadi.edit_ajax',
                $data
            )
            : redirect('/');
    }

    /**
     * ==========================================================
     * SELESAIKAN PEMINJAMAN (upload 3 foto dokumentasi -> status Ditinjau)
     * ==========================================================
     */
    public function selesaikan_ajax(Request $request, int $id)
    {
        $jadwal = $this->pribadiService->authorizeOwner($id);

        return $request->ajax()
            ? view('jadwal.pribadi.selesaikan_ajax', compact('jadwal'))
            : redirect('/');
    }

    public function selesaikan_store_ajax(Request $request, int $id)
    {
        $this->pribadiService->authorizeOwner($id);

        $files = [
            'foto_penggunaan' => $request->file('foto_penggunaan'),
            'foto_kebersihan' => $request->file('foto_kebersihan'),
            'foto_kunci' => $request->file('foto_kunci'),
        ];

        return $this->responses->jsonResult(
            $this->pribadiService->selesaikan($id, $files)
        );
    }

    /**
     * ==========================================================
     * UPDATE
     * ==========================================================
     */
    public function update_ajax(Request $request, int $id)
    {
        if ($request->user()->getRole() === \App\Constants\RoleConstants::ADMIN) {
            abort(403, 'Role ADM tidak memiliki akses untuk mengubah jadwal pribadi.');
        }

        $this->pribadiService->authorizeOwner($id);

        $payload = $this->pribadiService
            ->requestData($request);

        return $this->responses->isAjax($request)
            ? $this->responses->jsonResult(
                $this->jadwalService->update(
                    $id,
                    $payload
                )
            )
            : redirect('/');
    }
}