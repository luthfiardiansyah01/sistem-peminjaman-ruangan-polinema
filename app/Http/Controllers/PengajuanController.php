<?php

namespace App\Http\Controllers;

use App\Services\ControllerResponseService;
use App\Services\PengajuanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanController extends Controller
{
    public function __construct(protected PengajuanService $pengajuanService, protected ControllerResponseService $responses)
    {
    }


    public function index()
    {
        return view('pengajuan.index', $this->pengajuanService->indexData(Auth::user()));
    }

    // Listing "Daftar Penyelesaian" — khusus ADM (rute /penyelesaian). Menampilkan data
    // yang sama dengan pengajuanService->indexData(), hanya breadcrumb/judul/activeMenu-nya
    // disesuaikan menjadi "Daftar Penyelesaian" (lihat PengajuanService::indexData()).
    public function index_penyelesaian()
    {
        return view('pengajuan.index', $this->pengajuanService->indexData(Auth::user()));
    }

    public function create_ajax(Request $request)
    {
        return $request->ajax() ? view('pengajuan.create_ajax', $this->pengajuanService->createData()) : redirect('/');
    }

    public function store_ajax(Request $request)
    {
        return $this->responses->isAjax($request) ? $this->responses->jsonResult($this->pengajuanService->create($request->all(), Auth::id())) : redirect('/');
    }

    public function show_ajax(Request $request, string $id)
    {
        return $this->detailModal($request, $id, 'pengajuan.show_ajax');
    }

    // Sumber dropdown "Panitia" di form pengajuan: mahasiswa anggota organisasi_id terpilih
    // (lihat MahasiswaModel::organisasis(), m_mahasiswa_organisasi).
    public function panitia_by_organisasi_ajax(Request $request, string $organisasi_id)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }

        return response()->json($this->pengajuanService->panitiaCandidates((int) $organisasi_id));
    }

    public function edit_ajax(Request $request, string $id)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }

        $result = $this->pengajuanService->findEditable((int) $id, Auth::id());
        if (!$result['status']) {
            return response()->json(['status' => false, 'message' => $result['message']], $result['http_status']);
        }

        return view('pengajuan.edit_ajax', $this->pengajuanService->createData() + ['pengajuan' => $result['pengajuan']]);
    }

    public function update_ajax(Request $request, string $id)
    {
        return $this->responses->isAjax($request) ? $this->responses->jsonResult($this->pengajuanService->update((int) $id, $request->all(), Auth::id())) : redirect('/');
    }

    public function verifikasi_ajax(Request $request, string $id)
    {
        return $this->detailModal($request, $id, 'pengajuan.verifikasi_ajax');
    }

    public function terima_ajax(Request $request, $id)
    {
        return $this->responses->isAjax($request) ? $this->responses->jsonResult($this->pengajuanService->accept((int) $id)) : redirect('/');
    }

    public function tolak_ajax(Request $request, $id)
    {
        return $this->responses->isAjax($request) ? $this->responses->jsonResult($this->pengajuanService->reject((int) $id, $request->all())) : redirect('/');
    }

    public function antrian_ajax(Request $request)
    {
        return view('pengajuan.antrian', [
            'breadcrumb' => (object) ['title' => 'Antrian Approval', 'list' => ['Home', 'Antrian Approval']],
            'page' => (object) ['title' => 'Daftar pengajuan yang menunggu persetujuan Anda'],
            'activeMenu' => 'antrian_approval',
            'antrian' => $this->pengajuanService->antrianFor(Auth::user()),
        ]);
    }

    public function proses_approval_ajax(Request $request, string $approvalId)
    {
        $result = $this->pengajuanService->processApproval(
            (int) $approvalId,
            Auth::id(),
            (string) $request->input('status_approval'),
            $request->input('alasan_penolakan')
        );
        return $this->responses->jsonResult($result);
    }

    public function timeline_ajax(Request $request, string $id)
    {
        $result = $this->pengajuanService->timelineFor((int) $id, Auth::id());
        if (!$result['status']) {
            return response()->json(['status' => false, 'message' => $result['message']], $result['http_status']);
        }

        $timeline = $result['timeline'];
        return $request->ajax() ? view('pengajuan.timeline_ajax', compact('timeline', 'id')) : redirect('/');
    }

    public function cetak_surat_ajax(string $id)
    {
        $result = $this->pengajuanService->findForCetakSurat((int) $id);
        if (!$result['status']) {
            abort($result['http_status'], $result['message']);
        }

        $pengajuan = $result['pengajuan'];
        $pdf = Pdf::loadView('pdf.surat_peminjaman', compact('pengajuan'));

        return $pdf->download('Surat_Peminjaman_Ruangan_' . $pengajuan->pengajuan_id . '.pdf');
    }

    protected function detailModal(Request $request, string $id, string $view)
    {
        $pengajuan = $this->pengajuanService->findDetailed((int) $id);
        if (!$pengajuan) {
            return response()->json(['status' => false, 'message' => 'Data pengajuan tidak ditemukan!'], 404);
        }
        return $request->ajax() ? view($view, compact('pengajuan')) : redirect('/');
    }
}
