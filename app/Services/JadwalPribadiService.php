<?php

namespace App\Services;

use App\Models\JadwalModel;
use App\Models\PengajuanModel;
use App\Models\RuanganModel;
use App\Services\Concerns\HasPeriodeFilter;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class JadwalPribadiService
{
    use HasPeriodeFilter;

    public function indexData(): array
    {
        // Paksa ke zona waktu WIB dan simpan ke variabel
        $now = now()->timezone('Asia/Jakarta');

        // BLOK AUTO-UPDATE STATUS JADWAL
        \App\Models\JadwalModel::where('jadwal_status', 'Akan Datang')
            ->where(function ($q) use ($now) {
                // Jika hari H sudah terlewat
                $q->where('jadwal_tgl', '<', $now->format('Y-m-d'))
                  // Atau jika hari H adalah hari ini, cek apakah jam mulai sudah terlewat
                  ->orWhere(function ($q2) use ($now) {
                      $q2->where('jadwal_tgl', '=', $now->format('Y-m-d'))
                         ->where('jadwal_jam_mulai', '<=', $now->format('H:i:s')); // Tambahkan :s untuk detik
                  });
            })
            ->update(['jadwal_status' => 'Berlangsung']);

        $userId = auth()->id();

        $jadwals = JadwalModel::with(['ruangans'])
            ->where('user_id', $userId)
            ->where('jadwal_status', '!=', 'Selesai')
            // Urutkan tanggal dan jam terdekat
            ->orderBy('jadwal_tgl', 'asc')
            ->orderBy('jadwal_jam_mulai', 'asc')
            ->get();

        // Perbaikan: status pengajuan yang sudah diproses adalah 'Diterima', bukan 'Disetujui'
        $pengajuans = PengajuanModel::with(['ruangans'])
            ->where('user_id', $userId)
            ->whereIn('pengajuan_status', ['Diterima', 'Ditolak', 'Diajukan'])
            ->orderByDesc('created_at')
            ->get();

        $riwayats = JadwalModel::with(['ruangans'])
            ->where('user_id', $userId)
            ->where('jadwal_status', 'Selesai')
            //->orderByDesc('created_at')
            ->orderByDesc('updated_at')
            ->get();

        $periodeList = $this->periodeFilterOptions();

        return [
            'breadcrumb' => (object) [
                'title' => 'Daftar Jadwal Pribadi',
                'list' => ['Home', 'Daftar Jadwal Pribadi'],
            ],
            'page' => (object) ['title' => 'Daftar Jadwal Pribadi'],
            'activeMenu' => 'jadwal_pribadi',
            'jadwals' => $jadwals,
            'pengajuans' => $pengajuans,
            'riwayats' => $riwayats,
            // Hanya kolom yang diperlukan untuk dropdown filter
            'ruanganList' => RuanganModel::select('ruangan_id', 'ruangan_nama', 'ruangan_kode')->get(),
            'tahunAjaranList' => collect($periodeList)->pluck('tahun_ajaran')->unique()->sortDesc()->values(),
            'periodeList' => $periodeList,
        ];
    }

    public function findOwnedJadwal(int $jadwalId): ?JadwalModel
    {
        return JadwalModel::with('ruangans')
            ->where('jadwal_id', $jadwalId)
            ->where('user_id', auth()->id())
            ->first();
    }

    public function authorizeOwner(int $jadwalId): JadwalModel
    {
        $jadwal = $this->findOwnedJadwal($jadwalId);

        if (!$jadwal) {
            abort(403, 'Anda tidak memiliki akses terhadap jadwal ini.');
        }

        return $jadwal;
    }

    /**
     * Penyelesaian peminjaman: tombol muncul saat jadwal_status 'Berlangsung', wajib
     * upload 3 foto dokumentasi (penggunaan, kebersihan, pengembalian kunci), lalu
     * status berubah menjadi 'Ditinjau' untuk ditinjau admin.
     *
     * @param array{foto_penggunaan:?UploadedFile,foto_kebersihan:?UploadedFile,foto_kunci:?UploadedFile} $files
     */
    public function selesaikan(int $jadwalId, array $files): array
    {
        $jadwal = $this->findOwnedJadwal($jadwalId);
        if (!$jadwal) {
            return ['status' => false, 'message' => 'Data jadwal tidak ditemukan.', 'http_status' => 404];
        }

        // Izinkan proses upload jika status 'Berlangsung' ATAU 'Dokumentasi Tidak Sesuai'
        if (!in_array($jadwal->jadwal_status, ['Berlangsung', 'Dokumentasi Tidak Sesuai'])) {
            return ['status' => false, 'message' => 'Penyelesaian hanya dapat dilakukan saat jadwal berstatus Berlangsung atau saat perbaikan dokumentasi.', 'http_status' => 422];
        }

        /*if ($jadwal->jadwal_status !== 'Berlangsung') {
            return ['status' => false, 'message' => 'Penyelesaian hanya dapat dilakukan saat jadwal berstatus Berlangsung.', 'http_status' => 422];
        }*/

        $validator = Validator::make($files, [
            'foto_penggunaan' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'foto_kebersihan' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'foto_kunci' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => 'Validasi gagal!', 'errors' => $validator->errors(), 'msgField' => $validator->errors()];
        }

        $jadwal->update([
            'foto_penggunaan' => $files['foto_penggunaan']->store('jadwal_dokumentasi', 'public'),
            'foto_kebersihan' => $files['foto_kebersihan']->store('jadwal_dokumentasi', 'public'),
            'foto_kunci' => $files['foto_kunci']->store('jadwal_dokumentasi', 'public'),
            'jadwal_status' => 'Ditinjau',
        ]);

        return ['status' => true, 'message' => 'Dokumentasi berhasil diunggah dan akan segera ditinjau oleh Admin.', 'jadwal_id' => $jadwal->jadwal_id];
    }

    /**
     * Data request untuk CRUD Jadwal Pribadi.
     * user_id selalu menggunakan user yang sedang login.
     */
    public function requestData(Request $request): array
    {
        return [
            'jadwal_nama' => $request->input('jadwal_nama'),
            'jadwal_tgl' => $request->input('jadwal_tgl'),
            'jadwal_jam_mulai' => $request->input('jadwal_jam_mulai'),
            'jadwal_jam_selesai' => $request->input('jadwal_jam_selesai'),
            'jadwal_jumPes' => $request->input('jadwal_jumPes'),
            'user_id' => auth()->id(),
            'ruangan_ids' => $request->input('ruangan_ids', []),
        ];
    }
}

