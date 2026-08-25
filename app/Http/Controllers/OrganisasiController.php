<?php

namespace App\Http\Controllers;

use App\Services\ControllerResponseService;
use App\Services\OrganisasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganisasiController extends Controller
{
    public function __construct(protected OrganisasiService $organisasiService, protected ControllerResponseService $responses)
    {
    }

    public function index()
    {
        return view('organisasi.index', $this->organisasiService->indexData());
    }

    public function list(Request $request)
    {
        try {
            return $this->organisasiService->dataTable(Auth::user()->getRole());
        } catch (\Throwable $e) {
            return $this->responses->dataTableError($request, $e);
        }
    }

    public function create_ajax(Request $request)
    {
        return $request->ajax() ? view('organisasi.create_ajax') : redirect('/');
    }

    public function store_ajax(Request $request)
    {
        return $this->responses->jsonResult($this->organisasiService->create($request->all()), 201);
    }

    public function show_ajax(Request $request, string $id)
    {
        return $this->organisasiModal($request, $id, 'organisasi.show_ajax');
    }

    public function edit_ajax(Request $request, string $id)
    {
        return $this->organisasiModal($request, $id, 'organisasi.edit_ajax');
    }

    public function update_ajax(Request $request, $id)
    {
        return $this->responses->isAjax($request) ? $this->responses->jsonResult($this->organisasiService->update((int) $id, $request->all())) : redirect('/');
    }

    public function confirm_ajax(Request $request, string $id)
    {
        return $this->organisasiModal($request, $id, 'organisasi.confirm_ajax');
    }

    public function delete_ajax(Request $request, $id)
    {
        return $this->responses->isAjax($request) ? $this->responses->jsonResult($this->organisasiService->delete((int) $id)) : redirect('/');
    }

    protected function organisasiModal(Request $request, string $id, string $view)
    {
        $organisasi = $this->organisasiService->find((int) $id);
        if (!$organisasi) {
            return $this->notFoundErrorResponse('Data organisasi tidak ditemukan!', 'organisasi', $id);
        }
        return $request->ajax() ? view($view, compact('organisasi')) : redirect('/');
    }
}
