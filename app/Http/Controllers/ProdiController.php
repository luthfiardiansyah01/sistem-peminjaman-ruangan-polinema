<?php

namespace App\Http\Controllers;

use App\Services\ControllerResponseService;
use App\Services\ProdiCrudService;
use Illuminate\Http\Request;

class ProdiController extends Controller
{
    public function __construct(protected ProdiCrudService $prodiService, protected ControllerResponseService $responses)
    {
    }

    public function index()
    {
        return view('prodi.index', $this->prodiService->indexData());
    }

    public function create_ajax(Request $request)
    {
        return $request->ajax() ? view('prodi.create_ajax') : redirect('/');
    }

    public function store_ajax(Request $request)
    {
        return $this->responses->jsonResult($this->prodiService->create($request->all()), 201);
    }

    public function edit_ajax(Request $request, string $id)
    {
        return $this->prodiModal($request, $id, 'prodi.edit_ajax');
    }

    public function update_ajax(Request $request, $id)
    {
        return $this->responses->isAjax($request) ? $this->responses->jsonResult($this->prodiService->update((int) $id, $request->all())) : redirect('/');
    }

    public function confirm_ajax(Request $request, string $id)
    {
        return $this->prodiModal($request, $id, 'prodi.confirm_ajax');
    }

    public function delete_ajax(Request $request, $id)
    {
        return $this->responses->isAjax($request) ? $this->responses->jsonResult($this->prodiService->delete((int) $id)) : redirect('/');
    }

    protected function prodiModal(Request $request, string $id, string $view)
    {
        $prodi = $this->prodiService->find((int) $id);
        if (!$prodi) {
            return response()->json(['status' => false, 'message' => 'Data program studi tidak ditemukan!'], 404);
        }
        return $request->ajax() ? view($view, compact('prodi')) : redirect('/');
    }
}
