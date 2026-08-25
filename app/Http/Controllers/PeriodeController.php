<?php

namespace App\Http\Controllers;

use App\Services\ControllerResponseService;
use App\Services\PeriodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeriodeController extends Controller
{
    public function __construct(protected PeriodeService $periodeService, protected ControllerResponseService $responses)
    {
    }

    public function index()
    {
        return view('periode.index', $this->periodeService->indexData());
    }

    public function create_ajax(Request $request)
    {
        return $request->ajax() ? view('periode.create_ajax') : redirect('/');
    }

    public function store_ajax(Request $request)
    {
        return $this->responses->jsonResult($this->periodeService->create($request->all(), Auth::id()), 201);
    }

    public function show_ajax(Request $request, string $id)
    {
        return $this->periodeModal($request, $id, 'periode.show_ajax');
    }

    public function edit_ajax(Request $request, string $id)
    {
        return $this->periodeModal($request, $id, 'periode.edit_ajax');
    }

    public function update_ajax(Request $request, $id)
    {
        return $this->responses->isAjax($request) ? $this->responses->jsonResult($this->periodeService->update((int) $id, $request->all(), Auth::id())) : redirect('/');
    }

    public function confirm_ajax(Request $request, string $id)
    {
        return $this->periodeModal($request, $id, 'periode.confirm_ajax');
    }

    public function delete_ajax(Request $request, $id)
    {
        return $this->responses->isAjax($request) ? $this->responses->jsonResult($this->periodeService->delete((int) $id)) : redirect('/');
    }

    protected function periodeModal(Request $request, string $id, string $view)
    {
        $periode = $this->periodeService->find((int) $id);
        if (!$periode) {
            return response()->json(['status' => false, 'message' => 'Data periode tidak ditemukan!'], 404);
        }
        return $request->ajax() ? view($view, compact('periode')) : redirect('/');
    }
}
