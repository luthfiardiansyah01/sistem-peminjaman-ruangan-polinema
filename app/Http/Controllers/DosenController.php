<?php

namespace App\Http\Controllers;

use App\Models\DosenModel;
use App\Models\ProdiModel;
use App\Services\Interfaces\ImportServiceInterface;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Http\Request;

class DosenController extends Controller
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
        return view('dosen.index', [
            'breadcrumb' => (object) ['title' => 'Daftar Dosen', 'list' => ['Home', 'Daftar Dosen']],
            'page' => (object) ['title' => 'Dosen yang terdaftar dalam sistem'],
            'activeMenu' => 'dosen',
            'prodiList' => ProdiModel::all(),
            'dosens' => DosenModel::with(['user', 'prodi'])->get(),
        ]);
    }

    public function create_ajax(Request $request)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        return view('dosen.create_ajax', ['prodiList' => ProdiModel::all()]);
    }

    public function store_ajax(Request $request)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        return $this->serviceJsonResponse($this->userService->createDosen($request->all()), 201);
    }

    public function show_ajax(Request $request, string $id)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $dosen = DosenModel::with(['user', 'prodi'])->find($id);

        if (!$dosen) {
            abort(404, 'Data dosen tidak ditemukan!');
        }

        return view('dosen.show_ajax', compact('dosen'));
    }

    public function edit_ajax(Request $request, string $id)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $dosen = DosenModel::find($id);

        if (!$dosen) {
            abort(404, 'Data dosen tidak ditemukan!');
        }

        return view('dosen.edit_ajax', ['dosen' => $dosen, 'prodiList' => ProdiModel::all()]);
    }

    public function update_ajax(Request $request, $id)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $dosen = DosenModel::find($id);
        if (!$dosen) {
            return $this->notFoundErrorResponse('Data dosen tidak ditemukan!', 'dosen', $id);
        }

        return $this->serviceJsonResponse($this->userService->updateDosen($dosen->user_id, $request->all()));
    }

    public function confirm_ajax(Request $request, string $id)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $dosen = DosenModel::with('user', 'prodi')->find($id);

        if (!$dosen) {
            return $this->notFoundErrorResponse('Data dosen tidak ditemukan!', 'dosen', $id);
        }

        return view('dosen.confirm_ajax', compact('dosen'));
    }

    public function delete_ajax(Request $request, $id)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $dosen = DosenModel::find($id);
        if (!$dosen) {
            return $this->notFoundErrorResponse('Data dosen tidak ditemukan!', 'dosen', $id);
        }

        return $this->serviceJsonResponse($this->userService->deleteUser($dosen->user_id), 200, 500);
    }

    public function import(Request $request)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        return view('dosen.import');
    }

    public function import_ajax(Request $request)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $result = $this->importService->importUploadedUserFile($request->file('file_dosen'), 'dosen');

        return $this->importResponse($result);
    }

    protected function serviceJsonResponse(array $result, int $successStatus = 200)
    {
        // Use standardized error response system
        return $this->jsonResponse($result, $successStatus);
    }

    protected function importResponse(array $result)
    {
        // Use standardized error response system
        return $this->jsonResponse($result);
    }

    protected function isAjaxRequest(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson();
    }

}
