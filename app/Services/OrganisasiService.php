<?php

namespace App\Services;

use App\Models\OrganisasiModel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class OrganisasiService
{
    public function indexData(): array
    {
        return [
            'breadcrumb' => (object) ['title' => 'Daftar Organisasi', 'list' => ['Home', 'Daftar Organisasi']],
            'page' => (object) ['title' => 'Daftar organisasi mahasiswa yang terdaftar dalam sistem'],
            'activeMenu' => 'organisasi',
            'organisasi' => OrganisasiModel::all(),
        ];
    }

    public function dataTable(string $role)
    {
        $query = OrganisasiModel::select('organisasi_id', 'organisasi_kode', 'organisasi_nama', 'organisasi_logo');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('aksi', fn ($organisasi) => $this->actionButtons($organisasi, $role))
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create(array $data): array
    {
        $validator = Validator::make($data, $this->rules());
        if ($validator->fails()) {
            return ['status' => false, 'message' => 'Validasi gagal!', 'errors' => $validator->errors(), 'msgField' => $validator->errors()];
        }

        try {
            $organisasi = OrganisasiModel::create($this->payload($data));
            return ['status' => true, 'message' => 'Data organisasi berhasil ditambahkan!', 'data' => ['organisasi_id' => $organisasi->organisasi_id]];
        } catch (QueryException $e) {
            return $this->error('Penyimpanan data gagal. Error Database: ' . $e->getMessage(), 500);
        }
    }

    public function update(int $id, array $data): array
    {
        $organisasi = OrganisasiModel::find($id);
        if (!$organisasi) {
            return $this->error('Data organisasi tidak ditemukan!', 404);
        }

        $validator = Validator::make($data, $this->rules($id));
        if ($validator->fails()) {
            return ['status' => false, 'message' => 'Validasi Gagal', 'msgField' => $validator->errors()];
        }

        $organisasi->update($this->payload($data));
        return ['status' => true, 'message' => 'Data organisasi berhasil diupdate!'];
    }

    public function delete(int $id): array
    {
        $organisasi = OrganisasiModel::find($id);
        if (!$organisasi) {
            return $this->error('Data organisasi tidak ditemukan!', 404);
        }

        try {
            $organisasi->delete();
            return ['status' => true, 'message' => 'Data organisasi berhasil dihapus!'];
        } catch (QueryException $e) {
            return $this->error('Data gagal dihapus karena masih digunakan di tabel lain!', 500);
        }
    }

    public function find(int $id): ?OrganisasiModel
    {
        return OrganisasiModel::find($id);
    }

    protected function actionButtons($organisasi, string $role): string
    {
        $btn = '<button onclick="modalAction(\'' . url('/organisasi/' . $organisasi->organisasi_id . '/show_ajax') . '\')" class="btn btn-outline-info btn-sm" title="Detail"><i class="fas fa-eye"></i></button>';
        if ($role !== 'ADM') {
            return $btn;
        }

        $btn .= ' <button onclick="modalAction(\'' . url('/organisasi/' . $organisasi->organisasi_id . '/edit_ajax') . '\')" class="btn btn-outline-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></button>';
        return $btn . ' <button onclick="modalAction(\'' . url('/organisasi/' . $organisasi->organisasi_id . '/confirm_ajax') . '\')" class="btn btn-outline-danger btn-sm" title="Hapus"><i class="fas fa-trash"></i></button>';
    }

    protected function rules(?int $id = null): array
    {
        return [
            'organisasi_kode' => 'required|string|max:10|unique:m_organisasi,organisasi_kode,' . $id . ',organisasi_id',
            'organisasi_nama' => 'required|string|max:255',
            'organisasi_logo' => 'nullable|string|max:255',
        ];
    }

    protected function payload(array $data): array
    {
        return [
            'organisasi_kode' => $data['organisasi_kode'],
            'organisasi_nama' => $data['organisasi_nama'],
            'organisasi_logo' => $data['organisasi_logo'] ?? null,
        ];
    }

    protected function error(string $message, int $status): array
    {
        return ['status' => false, 'message' => $message, 'http_status' => $status];
    }
}
