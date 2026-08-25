<?php

namespace App\Services;

use Illuminate\Http\Request;

class ControllerResponseService
{
    public function isAjax(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson();
    }

    public function jsonResult(array $result, int $successStatus = 200, int $errorStatus = 422)
    {
        $status = $result['status'] ?? $result['success'] ?? false;
        $httpStatus = $status ? $successStatus : ($result['http_status'] ?? $errorStatus);

        return response()->json($result, $httpStatus);
    }

    public function dataTableError(Request $request, \Throwable $e)
    {
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => 'Error: ' . $e->getMessage(),
        ]);
    }
}
