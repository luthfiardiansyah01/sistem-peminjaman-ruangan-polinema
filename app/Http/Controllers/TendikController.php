<?php

namespace App\Http\Controllers;

use App\Models\TendikModel;
use App\Services\Interfaces\ImportServiceInterface;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Http\Request;

class TendikController extends Controller
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
        return view('tendik.index', [
            'breadcrumb' => (object) ['title' => 'Daftar Tendik', 'list' => ['Home', 'Daftar Tendik']],
            'page' => (object) ['title' => 'Tendik yang terdaftar dalam sistem'],
            'activeMenu' => 'tendik',
            'tendiks' => TendikModel::with(['user'])->get(),
        ]);
    }

    public function create_ajax(Request $request)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        return view('tendik.create_ajax');
    }

    public function store_ajax(Request $request)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        return $this->serviceJsonResponse($this->userService->createTendik($request->all()), 201);
    }

    public function show_ajax(Request $request, string $id)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $tendik = TendikModel::with(['user'])->find($id);

        return view('tendik.show_ajax', compact('tendik'));
    }

    public function edit_ajax(Request $request, string $id)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $tendik = TendikModel::find($id);

        if (!$tendik) {
            abort(404, 'Data tendik tidak ditemukan!');
        }

        return view('tendik.edit_ajax', compact('tendik'));
    }

    public function update_ajax(Request $request, $id)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $tendik = TendikModel::find($id);
        if (!$tendik) {
            return $this->notFoundErrorResponse('Data tendik tidak ditemukan!', 'tendik', $id);
        }

        return $this->serviceJsonResponse($this->userService->updateTendik($tendik->user_id, $request->all()));
    }

    public function confirm_ajax(Request $request, string $id)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $tendik = TendikModel::with('user')->find($id);

        if (!$tendik) {
            return $this->notFoundErrorResponse('Data tendik tidak ditemukan!', 'tendik', $id);
        }

        return view('tendik.confirm_ajax', compact('tendik'));
    }

    public function delete_ajax(Request $request, $id)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $tendik = TendikModel::find($id);
        if (!$tendik) {
            return $this->notFoundErrorResponse('Data tendik tidak ditemukan!', 'tendik', $id);
        }

        return $this->serviceJsonResponse($this->userService->deleteUser($tendik->user_id), 200, 500);
    }

    public function import(Request $request)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        return view('tendik.import');
    }

    public function import_ajax(Request $request)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $result = $this->importService->importUploadedUserFile($request->file('file_tendik'), 'tendik');

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
