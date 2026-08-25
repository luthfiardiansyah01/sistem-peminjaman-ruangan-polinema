<?php

namespace App\Http\Controllers;

use App\Models\KelasModel;
use App\Models\MahasiswaModel;
use App\Models\OrganisasiModel;
use App\Models\ProdiModel;
use App\Services\Interfaces\ImportServiceInterface;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Http\Request;

class MahasiswaController extends Controller
{
    protected UserServiceInterface $userService;
    protected ImportServiceInterface $importService;

    public function __construct(UserServiceInterface $userService, ImportServiceInterface $importService)
    {
        $this->userService = $userService;
        $this->importService = $importService;
    }

    public function index()
    {
        return view('mahasiswa.index', [
            'breadcrumb' => (object) ['title' => 'Daftar Mahasiswa', 'list' => ['Home', 'Daftar Mahasiswa']],
            'page' => (object) ['title' => 'Mahasiswa yang terdaftar dalam sistem'],
            'activeMenu' => 'mahasiswa',
            'prodiList' => ProdiModel::all(),
            'kelasList' => KelasModel::all(),
            'mahasiswas' => MahasiswaModel::with(['user', 'prodi', 'kelas', 'organisasis'])->get(),
        ]);
    }

    public function create_ajax(Request $request)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        return view('mahasiswa.create_ajax', ['prodiList' => ProdiModel::all(), 'kelasList' => KelasModel::all(), 'organisasiList' => OrganisasiModel::all()]);
    }

    public function store_ajax(Request $request)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        return $this->serviceJsonResponse($this->userService->createMahasiswa($request->all()), 201);
    }

    public function show_ajax(Request $request, string $id)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $mahasiswa = MahasiswaModel::with(['user', 'prodi', 'kelas'])->find($id);

        if (!$mahasiswa) {
            abort(404, 'Data mahasiswa tidak ditemukan!');
        }

        return view('mahasiswa.show_ajax', compact('mahasiswa'));
    }

    public function edit_ajax(Request $request, string $id)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $mahasiswa = MahasiswaModel::with('organisasis')->find($id);

        if (!$mahasiswa) {
            abort(404, 'Data Mahasiswa tidak ditemukan!');
        }

        return view('mahasiswa.edit_ajax', ['mahasiswa' => $mahasiswa, 'prodiList' => ProdiModel::all(), 'kelasList' => KelasModel::all(), 'organisasiList' => OrganisasiModel::all()]);
    }

    public function update_ajax(Request $request, $id)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $mahasiswa = MahasiswaModel::find($id);
        if (!$mahasiswa) {
            return $this->notFoundErrorResponse('Data mahasiswa tidak ditemukan!', 'mahasiswa', $id);
        }

        return $this->serviceJsonResponse($this->userService->updateMahasiswa($mahasiswa->user_id, $request->all()));
    }

    public function confirm_ajax(Request $request, string $id)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $mahasiswa = MahasiswaModel::with('user', 'prodi', 'kelas')->find($id);

        if (!$mahasiswa) {
            return $this->notFoundErrorResponse('Data mahasiswa tidak ditemukan!', 'mahasiswa', $id);
        }

        return view('mahasiswa.confirm_ajax', compact('mahasiswa'));
    }

    public function delete_ajax(Request $request, $id)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $mahasiswa = MahasiswaModel::find($id);
        if (!$mahasiswa) {
            return $this->notFoundErrorResponse('Data mahasiswa tidak ditemukan!', 'mahasiswa', $id);
        }

        return $this->serviceJsonResponse($this->userService->deleteUser($mahasiswa->user_id), 200, 500);
    }

    public function import(Request $request)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        return view('mahasiswa.import');
    }

    public function import_ajax(Request $request)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $result = $this->importService->importUploadedUserFile($request->file('file_mahasiswa'), 'mahasiswa');

        return $this->importResponse($result);
    }

    public function get_kelas_by_prodi($prodi_id)
    {
        $kelasList = KelasModel::where('prodi_id', $prodi_id)->get();
        $sortedKelas = $kelasList->sortBy('kelas_nama', SORT_NATURAL)->values();

        return response()->json($sortedKelas);
    }

    protected function serviceJsonResponse(array $result, int $successStatus = 200)
    {
        // Use standardized error response system
        return $this->jsonResponse($result, $successStatus);
    }

    protected function importResponse(array $result)
    {
        if ($result['success'] ?? false) {
            return response()->json(['status' => true, 'message' => $result['message']]);
        }

        return response()->json([
            'status' => false,
            'message' => $result['message'],
            'errors' => $result['errors'] ?? [],
        ], $result['status'] ?? 400);
    }

    protected function isAjaxRequest(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson();
    }

}
