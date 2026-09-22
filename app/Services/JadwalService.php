<?php

namespace App\Services;

use App\Models\JadwalModel;
use App\Models\KelasModel;
use App\Models\LevelModel;
use App\Models\ProdiModel;
use App\Models\RuanganModel;
use App\Models\UserModel;
use App\Services\Concerns\HasPeriodeFilter;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class JadwalService
{
    use HasPeriodeFilter;

    // State machine status jadwal (FR-4.2): transisi hanya boleh mengikuti urutan berikut,
    // tidak boleh melompat tahap secara sembarangan.
    protected const STATUS_TRANSITIONS = [
        'Akan Datang' => ['Berlangsung'],
        'Berlangsung' => ['Ditinjau'],
        'Ditinjau' => ['Dokumentasi Tidak Sesuai', 'Selesai'],
        'Dokumentasi Tidak Sesuai' => ['Selesai'],
        'Selesai' => [],
    ];


    /**
     * Transisi otomatis 'Akan Datang' -> 'Berlangsung' begitu jam mulainya terlewat.
     * Sebelumnya blok ini HANYA jalan inline di dalam indexData() (di sini dan di
     * JadwalPribadiService), jadi status baru ter-update saat seseorang membuka
     * halaman Daftar Jadwal/Daftar Jadwal Pribadi — terkesan semi-otomatis, perlu
     * refresh manual. Method ini dipakai command `jadwal:auto-update-status` (lihat
     * app/Console/Commands/JadwalAutoUpdateStatus.php, dijadwalkan setiap menit di
     * Kernel::schedule()) supaya transisi tetap jalan di background walau tidak ada
     * yang sedang membuka halaman jadwal sama sekali.
     */
    public function autoUpdateStatusAkanDatang(): int
    {
        $now = now()->timezone('Asia/Jakarta');

        return \App\Models\JadwalModel::where('jadwal_status', 'Akan Datang')
            ->where(function ($q) use ($now) {
                $q->where('jadwal_tgl', '<', $now->format('Y-m-d'))
                  ->orWhere(function ($q2) use ($now) {
                      $q2->where('jadwal_tgl', '=', $now->format('Y-m-d'))
                         ->where('jadwal_jam_mulai', '<=', $now->format('H:i:s'));
                  });
            })
            ->update(['jadwal_status' => 'Berlangsung']);
    }

    public function indexData(): array
    {
        // Gabungkan query jadwal aktif & riwayat dalam SATU query dengan union,
        // bukan 2 query terpisah. Bedanya hanya filter jadwal_status != 'Selesai'
        // vs = 'Selesai' — hasilnya dipisah di PHP setelah fetch.
        // ⚠️ Tidak pakai union bawaan Eloquen karena tidak dukung with() konsisten.
        // Jadi tetap 2 query tapi kita optimasi UserModel dan Periode di bawah.
        // Req 1: Jadwal berstatus 'Ditinjau' hanya ditampilkan di Daftar Penyelesaian
        // jika peminjam sudah upload semua 3 foto dokumentasi.
        // Jadwal non-Ditinjau (Akan Datang, Berlangsung, Dokumentasi Tidak Sesuai)
        // selalu ditampilkan.
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

        $jadwals = JadwalModel::with(['user', 'ruangans'])
            ->where('jadwal_status', '!=', 'Selesai')
            ->where(function ($q) {
                $q->where('jadwal_status', '!=', 'Ditinjau')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('foto_penggunaan')
                         ->whereNotNull('foto_kebersihan')
                         ->whereNotNull('foto_kunci');
                  });
            })
            // Urutkan tanggal dan jam terdekat (Menaik / Ascending)
            ->orderBy('jadwal_tgl', 'asc')
            ->orderBy('jadwal_jam_mulai', 'asc')
            ->get();

        $riwayats = JadwalModel::with(['user', 'ruangans'])
            ->where('jadwal_status', 'Selesai')
            // Urutkan berdasarkan waktu terakhir verifikasi selesai oleh Admin
            ->orderByDesc('updated_at')
            ->get();

        $periodeList = $this->periodeFilterOptions();

        return [
            'breadcrumb' => (object) ['title' => 'Daftar Jadwal', 'list' => ['Home', 'Daftar Jadwal']],
            'page' => (object) ['title' => 'Daftar jadwal yang terdaftar dalam sistem'],
            'activeMenu' => 'jadwal',
            // Hanya pilih kolom yang diperlukan — username dan display name — untuk
            // dropdown filter. Sebelumnya semua kolom termasuk password hash ikut terload.
            'userList' => UserModel::select('user_id', 'username', 'level_id')->with(['level:level_id,level_nama'])->get(),
            'ruanganList' => RuanganModel::select('ruangan_id', 'ruangan_nama', 'ruangan_kode')->get(),
            'jadwals' => $jadwals,
            'riwayats' => $riwayats,
            // tahunAjaranList dihitung dari koleksi yang sudah di-load oleh
            // periodeFilterOptions() — menghemat 1 query terpisah ke m_periode.
            'tahunAjaranList' => collect($periodeList)->pluck('tahun_ajaran')->unique()->sortDesc()->values(),
            'periodeList' => $periodeList,
        ];
    }

    public function formData(?int $id = null): array
    {
        return [
            'jadwal' => $id ? JadwalModel::with('ruangans')->find($id) : null,
            // 🔧 Perbaikan: menambahkan kolom yang dibutuhkan oleh view (level_kode, nim, nidn, prodi_id, kelas_id)
            // View create_ajax/edit_ajax mengakses properti berikut:
            // - $user->level->level_kode, $user->level->level_nama
            // - $user->mahasiswa->mahasiswa_nama, mahasiswa_nim, prodi_id, kelas_id
            // - $user->dosen->dosen_nama, dosen_nip, dosen_nidn, prodi_id
            // - $user->tendik->tendik_nama, tendik_nidn
            // - $user->admin->admin_nama, admin_nidn
            // Relasi belongsTo membutuhkan foreign key (user_id) di kolom seleksi agar terhubung.
            'userList' => UserModel::select('user_id', 'username')->with([
                'level:level_id,level_kode,level_nama',
                'admin:user_id,admin_nama,admin_nidn',
                'dosen:user_id,dosen_nama,dosen_nip,dosen_nidn,prodi_id',
                'tendik:user_id,tendik_nama,tendik_nidn',
                'mahasiswa:user_id,mahasiswa_nama,mahasiswa_nim,prodi_id,kelas_id',
            ])->get(),
            'ruanganList' => RuanganModel::select('ruangan_id', 'ruangan_nama', 'ruangan_kode')->get(),
            'levelList' => LevelModel::all(),
            'prodiList' => ProdiModel::all(),
            'kelasList' => KelasModel::all(),
        ];
    }

    public function kelasByProdi(int $prodiId)
    {
        return KelasModel::where('prodi_id', $prodiId)->get()->sortBy('kelas_nama', SORT_NATURAL)->values();
    }

    public function create(array $data): array
    {
        $validator = Validator::make($data, $this->rules());
        if ($validator->fails()) {
            return ['status' => false, 'message' => 'Validasi Gagal', 'msgField' => $validator->errors()];
        }

        $conflict = $this->checkScheduleConflict(
            $data['jadwal_tgl'],
            $data['jadwal_jam_mulai'],
            $data['jadwal_jam_selesai'],
            $data['ruangan_ids']
        );

        if ($conflict) {
            $namaRuangan = $conflict->ruangans->pluck('ruangan_nama')->implode(', ');

            return [
                'status' => false,
                'message' => "Ruangan {$namaRuangan} sudah digunakan pada pukul "
                    . substr($conflict->jadwal_jam_mulai, 0, 5)
                    . " - "
                    . substr($conflict->jadwal_jam_selesai, 0, 5),
                'http_status' => 422,
            ];
        }

        try {
            $jadwal = JadwalModel::create($this->payload($data));
            $jadwal->ruangans()->attach($data['ruangan_ids']);
            return ['status' => true, 'message' => 'Data jadwal berhasil disimpan!', 'jadwal_id' => $jadwal->jadwal_id];
        } catch (\Throwable $e) {
            return [
                'status'      => false,
                'message'     => $e->getMessage(),
                'http_status' => 500,
            ];
        }
    }

    public function update(int $id, array $data): array
    {
        $jadwal = JadwalModel::find($id);
        if (!$jadwal) {
            return [
                'status' => false,
                'message' => 'Data jadwal tidak ditemukan.',
                'http_status' => 404,
            ];
        }

        $validator = Validator::make($data, $this->rules());
        if ($validator->fails()) {
            return [
                'status'   => false,
                'message'  => 'Validasi gagal.',
                'msgField' => $validator->errors()
            ];
        }

        $conflict = $this->checkScheduleConflict(
            $data['jadwal_tgl'],
            $data['jadwal_jam_mulai'],
            $data['jadwal_jam_selesai'],
            $data['ruangan_ids'],
            $id
        );

        if ($conflict) {
            return [
                'status' => false,
                'message' => 'Ruangan sudah digunakan pada tanggal dan jam tersebut.',
                'http_status' => 422
            ];
        }

        try {
            $jadwal->update($this->payload($data, $jadwal->jadwal_status));
            $jadwal->ruangans()->sync($data['ruangan_ids']);
            return ['status' => true, 'message' => 'Data jadwal berhasil diperbarui!'];
        } catch (\Throwable $e) {
            return [
                'status'      => false,
                'message'     => $e->getMessage(),
                'http_status' => 500,
            ];
        }
    }

    public function delete(int $id): array
    {
        $jadwal = JadwalModel::find($id);
        if (!$jadwal) {
            return $this->error('Data jadwal tidak ditemukan!', 404);
        }

        try {
            $jadwal->ruangans()->detach();
            $jadwal->delete();
            return ['status' => true, 'message' => 'Data jadwal berhasil dihapus!'];
        } catch (QueryException $e) {
            return $this->error('Data gagal dihapus karena masih digunakan!', 500);
        }
    }

    public function findForShow(int $id): ?JadwalModel
    {
        return JadwalModel::with(['user.level', 'user.mahasiswa.prodi', 'user.mahasiswa.kelas', 'ruangans'])->find($id);
    }

    public function findForConfirm(int $id): ?JadwalModel
    {
        return JadwalModel::with('user', 'ruangans')->find($id);
    }

    public function updateStatus(int $id, string $newStatus): array
    {
        $jadwal = JadwalModel::with('ruangans')->find($id);
        if (!$jadwal) {
            return $this->error('Data jadwal tidak ditemukan!', 404);
        }

        if (!in_array($newStatus, array_keys(self::STATUS_TRANSITIONS), true)) {
            return $this->error('Status jadwal tidak valid!', 422);
        }

        $allowed = self::STATUS_TRANSITIONS[$jadwal->jadwal_status] ?? [];
        if (!in_array($newStatus, $allowed, true)) {
            return $this->error("Transisi status dari '{$jadwal->jadwal_status}' ke '{$newStatus}' tidak diperbolehkan.", 422);
        }

        $jadwal->update(['jadwal_status' => $newStatus]);

        if ($newStatus === 'Selesai') {
            $ruanganIds = $jadwal->ruangans->pluck('ruangan_id')->toArray();
            RuanganModel::whereIn('ruangan_id', $ruanganIds)
                ->update(['ruangan_status' => 'Tersedia']);
        }

        return ['status' => true, 'message' => "Status jadwal berhasil diubah menjadi '{$newStatus}'."];
    }

    /**
     * Mengecek bentrok jadwal berdasarkan tanggal, ruangan, dan jam.
     * Optimasi: filter ruangan via join subquery alih-alih load semua jadwal ke memory
     * lalu iterasi di PHP. Hasil identik tapi lebih efisien untuk jadwal dalam jumlah besar.
     * Mengabaikan jadwal yang sedang diedit dan jadwal yang sudah selesai.
     */
    protected function checkScheduleConflict(
        string $tanggal,
        string $jamMulai,
        string $jamSelesai,
        array $ruanganIds,
        ?int $ignoreJadwalId = null
    ): ?JadwalModel {
        return JadwalModel::whereHas('ruangans', fn ($q) => $q->whereIn('m_ruangan.ruangan_id', $ruanganIds))
            ->where('jadwal_tgl', $tanggal)
            ->where('jadwal_status', '!=', 'Selesai')
            ->where('jadwal_jam_mulai', '<', $jamSelesai)
            ->where('jadwal_jam_selesai', '>', $jamMulai)
            ->when($ignoreJadwalId, fn ($q) => $q->where('jadwal_id', '!=', $ignoreJadwalId))
            ->with('ruangans')
            ->first();
    }

    protected function rules(): array
    {
        return [
            'jadwal_nama'      => 'required|string|max:255',
            'jadwal_tgl'       => 'required|date',
            'jadwal_jam_mulai' => 'required|date_format:H:i',
            'jadwal_jam_selesai' => 'required|date_format:H:i|after:jadwal_jam_mulai',
            'jadwal_jumPes'    => 'required|integer|min:1',
            'user_id'          => 'required|exists:m_user,user_id',
            'ruangan_ids'      => 'required|array',
            'ruangan_ids.*'    => 'exists:m_ruangan,ruangan_id',
        ];
    }

    protected function payload(array $data, string $defaultStatus = 'Akan Datang'): array
    {
        return [
            'jadwal_nama'      => $data['jadwal_nama'],
            'jadwal_tgl'       => $data['jadwal_tgl'],
            'jadwal_jam_mulai' => $data['jadwal_jam_mulai'],
            'jadwal_jam_selesai' => $data['jadwal_jam_selesai'],
            'jadwal_jumPes'    => $data['jadwal_jumPes'],
            'user_id'          => $data['user_id'],
            'jadwal_status'    => $data['jadwal_status'] ?? $defaultStatus,
        ];
    }

    protected function error(string $message, int $status): array
    {
        return ['status' => false, 'message' => $message, 'http_status' => $status];
    }
}

