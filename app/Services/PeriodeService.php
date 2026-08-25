<?php

namespace App\Services;

use App\Models\PeriodeModel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

/**
 * Fitur Periode (tahun ajaran + semester) — CRUD penuh oleh Admin, sekaligus
 * gerbang global untuk seluruh transactional operation (pengajuan peminjaman,
 * approval berjenjang, jadwal) lewat status Open/Closed.
 *
 * HANYA SATU baris yang boleh berstatus 'Open' dalam satu waktu (lihat
 * enforceSingleOpen()) — meng-Open-kan satu baris otomatis meng-Closed-kan
 * baris lain. isOpen() dipakai PeriodeGate middleware: transaksi aktif selama
 * ADA SATU baris Open, apa pun tahun_ajaran/semester-nya.
 */
class PeriodeService
{
    public function isOpen(): bool
    {
        return PeriodeModel::where('periode_status', 'Open')->exists();
    }

    public function indexData(): array
    {
        return [
            'breadcrumb' => (object) ['title' => 'Daftar Periode', 'list' => ['Home', 'Daftar Periode']],
            'page' => (object) ['title' => 'Daftar Periode'],
            'activeMenu' => 'periode',
            'periodes' => PeriodeModel::with('updater')->orderByDesc('tanggal_mulai')->get(),
            'tahunAjaranList' => PeriodeModel::select('tahun_ajaran')->distinct()->orderByDesc('tahun_ajaran')->pluck('tahun_ajaran'),
        ];
    }

    public function find(int $id): ?PeriodeModel
    {
        return PeriodeModel::with('updater')->find($id);
    }

    public function create(array $data, int $userId): array
    {
        $validator = Validator::make($data, $this->rules(null, $data['tahun_ajaran'] ?? null));
        if ($validator->fails()) {
            return ['status' => false, 'message' => 'Validasi Gagal', 'msgField' => $validator->errors()];
        }

        try {
            DB::transaction(function () use ($data, $userId) {
                if (($data['periode_status'] ?? 'Closed') === 'Open') {
                    $this->closeAllExcept(null);
                }

                PeriodeModel::create($this->payload($data, $userId));
            });

            return ['status' => true, 'message' => 'Data periode berhasil ditambahkan!'];
        } catch (QueryException $e) {
            return ['status' => false, 'message' => 'Penyimpanan data gagal. Error Database: ' . $e->getMessage(), 'http_status' => 500];
        }
    }

    public function update(int $id, array $data, int $userId): array
    {
        $periode = PeriodeModel::find($id);
        if (!$periode) {
            return ['status' => false, 'message' => 'Data periode tidak ditemukan!', 'http_status' => 404];
        }

        $validator = Validator::make($data, $this->rules($id, $data['tahun_ajaran'] ?? null));
        if ($validator->fails()) {
            return ['status' => false, 'message' => 'Validasi Gagal', 'msgField' => $validator->errors()];
        }

        DB::transaction(function () use ($periode, $data, $userId) {
            if (($data['periode_status'] ?? 'Closed') === 'Open') {
                $this->closeAllExcept($periode->periode_id);
            }

            $periode->update($this->payload($data, $userId));
        });

        return ['status' => true, 'message' => 'Data periode berhasil diupdate!'];
    }

    public function delete(int $id): array
    {
        $periode = PeriodeModel::find($id);
        if (!$periode) {
            return ['status' => false, 'message' => 'Data periode tidak ditemukan!', 'http_status' => 404];
        }

        try {
            $periode->delete();
            return ['status' => true, 'message' => 'Data periode berhasil dihapus!'];
        } catch (QueryException $e) {
            return ['status' => false, 'message' => 'Data gagal dihapus karena masih digunakan di tabel lain!', 'http_status' => 500];
        }
    }

    /**
     * Tutup seluruh baris periode LAIN (kecuali $exceptId) agar hanya satu baris
     * yang tetap berstatus Open (FR: satu periode aktif dalam satu waktu).
     */
    protected function closeAllExcept(?int $exceptId): void
    {
        PeriodeModel::where('periode_status', 'Open')
            ->when($exceptId !== null, fn ($q) => $q->where('periode_id', '!=', $exceptId))
            ->update(['periode_status' => 'Closed']);
    }

    protected function rules(?int $id = null, ?string $tahunAjaran = null): array
    {
        return [
            'tahun_ajaran' => 'required|string|max:9|regex:/^\d{4}\/\d{4}$/',
            'semester' => ['required', Rule::in(['Ganjil', 'Genap']), Rule::unique('m_periode', 'semester')
                ->where(fn ($q) => $q->where('tahun_ajaran', $tahunAjaran))
                ->ignore($id, 'periode_id')],
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'periode_status' => ['required', Rule::in(['Open', 'Closed'])],
        ];
    }

    protected function payload(array $data, int $userId): array
    {
        return [
            'tahun_ajaran' => $data['tahun_ajaran'],
            'semester' => $data['semester'],
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'periode_status' => $data['periode_status'],
            'updated_by' => $userId,
        ];
    }
}
