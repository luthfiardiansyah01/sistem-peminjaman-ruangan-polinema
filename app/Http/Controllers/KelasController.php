<?php

namespace App\Http\Controllers;

use App\Services\ControllerResponseService;
use App\Services\KelasService;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function __construct(protected KelasService $kelasService, protected ControllerResponseService $responses)
    {
    }

    public function index()
    {
        return view('kelas.index', $this->kelasService->indexData());
    }

    public function create_ajax(Request $request)
    {
        return $this->responses->isAjax($request) ? view('kelas.create_ajax', $this->kelasService->formData()) : redirect('/');
    }

    public function store_ajax(Request $request)
    {
        return $this->responses->jsonResult($this->kelasService->create($request->all()), 200);
    }

    public function edit_ajax(Request $request, $id)
    {
        $data = $this->kelasService->formData((int) $id);
        abort_if(!$data['kelas'], 404, 'Data kelas tidak ditemukan!');
        return $this->responses->isAjax($request) ? view('kelas.edit_ajax', $data) : redirect('/');
    }

    public function update_ajax(Request $request, $id)
    {
        return $this->responses->isAjax($request) ? $this->responses->jsonResult($this->kelasService->update((int) $id, $request->all())) : redirect('/');
    }

    public function confirm_ajax(Request $request, $id)
    {
        $kelas = $this->kelasService->findWithProdi((int) $id);
        if (!$kelas) {
            return response()->json(['status' => false, 'message' => 'Data kelas tidak ditemukan!'], 404);
        }
        return $this->responses->isAjax($request) ? view('kelas.confirm_ajax', compact('kelas')) : redirect('/');
    }

    public function delete_ajax(Request $request, $id)
    {
        return $this->responses->isAjax($request) ? $this->responses->jsonResult($this->kelasService->delete((int) $id)) : redirect('/');
    }
}
