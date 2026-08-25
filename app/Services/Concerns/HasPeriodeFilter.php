<?php

namespace App\Services\Concerns;

use App\Models\PeriodeModel;

/**
 * Trait untuk menyediakan metode `periodeFilterOptions()` yang digunakan oleh
 * beberapa service (JadwalService, JadwalPribadiService, PengajuanService).
 *
 * Menghilangkan duplikasi kode yang identik di ketiga service tersebut.
 * Data periode (m_periode) untuk filter Tahun Ajaran/Semester di index.
 * Di-map ke array plain dengan tanggal ber-format 'Y-m-d' (bukan objek Carbon)
 * supaya aman di-@json() ke JS tanpa berubah menjadi string ISO datetime
 * dengan timezone.
 */
trait HasPeriodeFilter
{
    /**
     * Mendapatkan daftar periode untuk opsi filter.
     *
     * @return array<int, array{periode_id: int, tahun_ajaran: string, semester: string, tanggal_mulai: string, tanggal_selesai: string}>
     */
    protected function periodeFilterOptions(): array
    {
        return PeriodeModel::select('periode_id', 'tahun_ajaran', 'semester', 'tanggal_mulai', 'tanggal_selesai')
            ->get()
            ->map(fn (PeriodeModel $p) => [
                'periode_id' => $p->periode_id,
                'tahun_ajaran' => $p->tahun_ajaran,
                'semester' => $p->semester,
                'tanggal_mulai' => $p->tanggal_mulai->format('Y-m-d'),
                'tanggal_selesai' => $p->tanggal_selesai->format('Y-m-d'),
            ])
            ->values()
            ->all();
    }
}

