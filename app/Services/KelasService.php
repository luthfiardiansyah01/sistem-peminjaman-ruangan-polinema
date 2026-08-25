<?php

namespace App\Services;

use App\Models\KelasModel;
use App\Models\ProdiModel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class KelasService
{
    public function indexData(): array
    {
        return [
            'breadcrumb' => (object) ['title' => 'Daftar Kelas', 'list' => ['Home', 'Daftar Kelas']],
            'page' => (object) ['title' => 'Daftar kelas yang terdaftar dalam sistem'],
            'activeMenu' => 'kelas',
            'kelas' => $this->orderedQuery()->get(),
            'prodiList' => ProdiModel::all(),
        ];
    }

    public function formData(?int $id = null): array
    {
        return ['kelas' => $id ? KelasModel::find($id) : null, 'prodiList' => ProdiModel::all()];
    }

    public function create(array $data): array
    {
        $validator = Validator::make($data, $this->rules());
        if ($validator->fails()) {
            return ['status' => false, 'message' => 'Validasi gagal!', 'errors' => $validator->errors()];
        }

        try {
            return ['status' => true, 'message' => 'Data kelas berhasil disimpan!', 'data' => KelasModel::create($data)];
        } catch (QueryException $e) {
            return $this->error('Penyimpanan data gagal. Error Database: ' . $e->getMessage(), 500);
        } catch (\Exception $e) {
            return $this->error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function update(int $id, array $data): array
    {
        $kelas = KelasModel::find($id);
        if (!$kelas) {
            return $this->error('Data kelas tidak ditemukan!', 404);
        }

        $validator = Validator::make($data, $this->rules($id));
        if ($validator->fails()) {
            return ['status' => false, 'message' => 'Validasi Gagal', 'msgField' => $validator->errors()];
        }

        $kelas->update(['kelas_nama' => $data['kelas_nama'], 'prodi_id' => $data['prodi_id']]);
        return ['status' => true, 'message' => 'Data kelas berhasil diupdate!'];
    }

    public function delete(int $id): array
    {
        $kelas = KelasModel::find($id);
        if (!$kelas) {
            return $this->error('Data kelas tidak ditemukan!', 404);
        }

        try {
            $kelas->delete();
            return ['status' => true, 'message' => 'Data kelas berhasil dihapus!'];
        } catch (QueryException $e) {
            return $this->error('Data gagal dihapus karena masih digunakan di tabel lain!', 500);
        }
    }

    public function findWithProdi(int $id): ?KelasModel
    {
        return KelasModel::with('prodi')->find($id);
    }

    protected function orderedQuery()
    {
        return KelasModel::select('m_kelas.*')
            ->join('m_prodi', 'm_kelas.prodi_id', '=', 'm_prodi.prodi_id')
            ->orderBy('m_prodi.prodi_nama')
            ->orderByRaw('CAST(m_kelas.kelas_nama AS UNSIGNED) ASC')
            ->orderBy('m_kelas.kelas_nama')
            ->with('prodi');
    }

    protected function rules(?int $id = null): array
    {
        return ['kelas_nama' => 'required|string|max:5|unique:m_kelas,kelas_nama,' . $id . ',kelas_id', 'prodi_id' => 'required|integer|exists:m_prodi,prodi_id'];
    }

    protected function error(string $message, int $status): array
    {
        return ['status' => false, 'message' => $message, 'http_status' => $status];
    }
}
