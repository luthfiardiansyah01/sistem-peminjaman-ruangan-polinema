<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Constants\RoleConstants;
use App\Models\JabatanApprovalModel;
use App\Models\JadwalModel;
use App\Models\OrganisasiModel;
use App\Models\PengajuanApprovalModel;
use App\Models\PengajuanModel;
use App\Models\PengajuanPanitiaModel;
use App\Models\RuanganModel;
use App\Models\UserModel;
use App\Services\Concerns\HasPeriodeFilter;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class PengajuanService
{
    use HasPeriodeFilter;
    // Urutan tahap approval berjenjang (FR-6.1). Tahap 0 (Ketua Pelaksana) dipilih bebas
    // per-pengajuan (bukan posisi tetap) — lihat generateApprovalStages(). Tahap 1..N
    // sesudahnya SEKARANG SELALU sekuensial (tidak ada lagi tahap paralel) dan urutannya
    // berbeda tergantung role pemohon & kategori ruangan — lihat alurApprovalUntukPengajuan().
    protected const STAGE_KETUA_PELAKSANA = 0;

    // posisi_approval bukan enum DB (lihat m_jabatan_approval) supaya posisi baru
    // bisa ditambah tanpa migration — validasi dipindah kesini.
    public const VALID_POSISI_APPROVAL = ['Ketua Pelaksana', 'Ketua Umum', 'DPK', 'Presiden BEM', 'Ketua Jurusan', 'Wakil Direktur II'];

    // Role end-permission: posisi approval yang BOLEH dipegang oleh masing-masing role akun.
    // 'Ketua Umum' di kode/DB = 'Ketua Organisasi' pada istilah bisnis klien (posisi yang sama,
    // nama internal dipertahankan agar data & e2e test yang sudah ada tidak perlu dimigrasi).
    public const POSISI_BY_ROLE = [
        RoleConstants::MAHASISWA => ['Ketua Pelaksana', 'Ketua Umum', 'Presiden BEM'],
        RoleConstants::DOSEN => ['Ketua Pelaksana', 'DPK', 'Ketua Jurusan', 'Wakil Direktur II'],
    ];
    public function indexData($user): array
    {
        $periodeList = $this->periodeFilterOptions();

        // ADM melihat halaman ini sebagai "Daftar Penyelesaian" (route /penyelesaian),
        // role peminjam (DSN/TDK/MHS) tetap melihat "Daftar Pengajuan" (route /pengajuan) —
        // lihat PengajuanController::index() vs index_penyelesaian().
        $isAdmin = $user->getRole() === RoleConstants::ADMIN;

        return [
            'breadcrumb' => $isAdmin
                ? (object) ['title' => 'Daftar Penyelesaian', 'list' => ['Home', 'Daftar Penyelesaian']]
                : (object) ['title' => 'Daftar Pengajuan', 'list' => ['Home', 'Daftar Pengajuan']],
            'page' => (object) ['title' => $isAdmin ? 'Daftar penyelesaian peminjaman ruangan' : 'Daftar pengajuan peminjaman ruangan'],
            'activeMenu' => $isAdmin ? 'penyelesaian' : 'pengajuan',
            'pengajuans' => $this->visibleFor($user),
            // tahunAjaranList dihitung dari koleksi yang sudah di-load oleh periodeFilterOptions()
            // menghemat 1 query terpisah ke m_periode.
            'ruanganList' => RuanganModel::select('ruangan_id', 'ruangan_nama', 'ruangan_kode')->get(),
            'tahunAjaranList' => collect($periodeList)->pluck('tahun_ajaran')->unique()->sortDesc()->values(),
            'periodeList' => $periodeList,
            // Req 8: Antrian approval untuk user yang punya jabatan — ADM tidak perlu antrian
            'antrian' => $isAdmin ? collect() : $this->antrianFor($user),
        ];
    }

    public function createData(): array
    {
        return [
            'ruanganList' => RuanganModel::all(),
            'organisasiList' => OrganisasiModel::all(),
            'ketuaPelaksanaList' => $this->ketuaPelaksanaCandidates(),
        ];
    }

    /**
     * Sumber dropdown "Panitia" pada form pengajuan: mahasiswa yang menjadi anggota
     * organisasi_id terpilih (lihat m_mahasiswa_organisasi). Kosong kalau organisasi_id
     * tidak dipilih (mis. pengaju Dosen/Tendik).
     */
    public function panitiaCandidates(?int $organisasiId): array
    {
        if (!$organisasiId) {
            return [];
        }

        return UserModel::query()
            ->whereHas('mahasiswa.organisasis', fn ($q) => $q->where('m_organisasi.organisasi_id', $organisasiId))
            ->with('mahasiswa')
            ->get()
            ->map(fn (UserModel $u) => ['user_id' => $u->user_id, 'nama' => $u->getDisplayName()])
            ->sortBy('nama')
            ->values()
            ->all();
    }

    /**
     * Simpan/ganti daftar panitia pengajuan dari input panitia_user_id[]/panitia_keterangan[]
     * (dua array paralel by index). sync() lewat delete+insert, bukan diff, karena jumlah
     * panitia per acara kecil dan ini dipanggil di dalam transaksi create/update sekali saja.
     */
    protected function syncPanitia(PengajuanModel $pengajuan, array $data): void
    {
        $userIds = (array) ($data['panitia_user_id'] ?? []);
        $keterangans = (array) ($data['panitia_keterangan'] ?? []);

        $pengajuan->panitias()->delete();

        $seen = [];
        foreach ($userIds as $index => $userId) {
            if ($userId === null || $userId === '' || isset($seen[$userId])) {
                continue;
            }
            $seen[$userId] = true;

            $keterangan = trim((string) ($keterangans[$index] ?? ''));
            PengajuanPanitiaModel::create([
                'pengajuan_id' => $pengajuan->pengajuan_id,
                'user_id' => (int) $userId,
                'keterangan' => $keterangan !== '' ? $keterangan : null,
            ]);
        }
    }

    /**
     * Daftar dosen+mahasiswa yang bisa dipilih sebagai Ketua Pelaksana (poin 1) — dipilih
     * bebas per-pengajuan, bukan posisi tetap, jadi daftarnya tidak difilter organisasi.
     * Optimasi: hanya select kolom user_id untuk identitas + person data untuk display name.
     */

    protected function ketuaPelaksanaCandidates()
    {
        $role = auth()->user()?->getRole();

        $query = UserModel::query()->with(['dosen', 'mahasiswa']);

        if ($role === 'MHS') {
            $query->whereHas('mahasiswa');
        } elseif (in_array($role, ['DSN', 'TDK'])) {
            $query->whereHas('dosen');
        } else {
            $query->whereHas('dosen')->orWhereHas('mahasiswa');
        }

        return $query->get()
            ->map(fn (UserModel $u) => (object) ['user_id' => $u->user_id, 'nama' => $u->getDisplayName()])
            ->sortBy('nama')
            ->values();
    }
    /*protected function ketuaPelaksanaCandidates()
    {
        return UserModel::whereHas('dosen')->orWhereHas('mahasiswa')
            ->select('user_id')
            ->with(['dosen:user_id,dosen_nama', 'mahasiswa:user_id,mahasiswa_nama'])
            ->get()
            ->map(fn (UserModel $u) => (object) ['user_id' => $u->user_id, 'nama' => $u->getDisplayName()])
            ->sortBy('nama')
            ->values();
    }*/

    public function create(array $data, int $userId): array
    {
        // 1. Ambil data user pengaju untuk mengetahui role-nya
        $pengaju = UserModel::find($userId);
        $rolePengaju = $pengaju?->getRole();

        // 2. Lempar role pengaju ke dalam rules()
        $validator = Validator::make($data, $this->rules($rolePengaju));

        //$validator = Validator::make($data, $this->rules());
        if ($validator->fails()) {
            return ['status' => false, 'message' => 'Validasi Gagal', 'msgField' => $validator->errors()];
        }

        $ketuaPelaksanaRole = UserModel::find($data['ketua_pelaksana_user_id'])?->getRole();
        if (!in_array($ketuaPelaksanaRole, [RoleConstants::MAHASISWA, RoleConstants::DOSEN], true)) {
            return ['status' => false, 'message' => 'Validasi Gagal', 'msgField' => ['ketua_pelaksana_user_id' => ['Ketua Pelaksana harus akun Mahasiswa atau Dosen.']]];
        }

        // Hitung total kapasitas dari semua ruangan yang di-select
        $totalKapasitas = RuanganModel::whereIn('ruangan_id', $data['ruangan_ids'])->sum('ruangan_kuota');
        
        // Cek apakah jumlah peserta melebihi kapasitas
        if ($data['pengajuan_jumPes'] > $totalKapasitas) {
            return [
                'status' => false, 
                'message' => 'Validasi Gagal', 
                'msgField' => [
                    'pengajuan_jumPes' => ['Jumlah peserta (' . $data['pengajuan_jumPes'] . ') melebihi total kapasitas ruangan yang dipilih (' . $totalKapasitas . ').']
                ]
            ];
        }

        // Validasi Tanggal Kegiatan harus berada dalam Periode yang 'Open'
        $tanggalPengajuan = $data['pengajuan_tgl'];
        $periodeBuka = DB::table('m_periode')
            ->where('periode_status', 'Open')
            ->where('tanggal_mulai', '<=', $tanggalPengajuan)
            ->where('tanggal_selesai', '>=', $tanggalPengajuan)
            ->first();

        if (!$periodeBuka) {
            return [
                'status' => false, 
                'message' => 'Validasi Gagal', 
                'msgField' => [
                    'pengajuan_tgl' => ['Periode untuk tanggal yang dipilih sedang tutup.']
                ]
            ];
        }

        $pengajuan = PengajuanModel::create($this->payload($data, $userId));
        $pengajuan->ruangans()->attach($data['ruangan_ids']);
        $this->syncRuanganStatus($data['ruangan_ids'], 'Diajukan');
        $this->syncPanitia($pengajuan, $data);
        // Refresh & eager-load user + ruangans untuk generateApprovalStages() tanpa query terpisah
        $pengajuan->refresh()->load(['user', 'ruangans']);
        $this->generateApprovalStages($pengajuan);
        return ['status' => true, 'message' => 'Pengajuan peminjaman berhasil diajukan!', 'pengajuan_id' => $pengajuan->pengajuan_id];
    }

    /**
     * Generate seluruh baris tahap approval berjenjang saat pengajuan pertama kali dibuat (FR-6.3).
     * Alur bercabang menurut role akun pemohon:
     * - Mahasiswa: Ketua Pelaksana -> Ketua Organisasi -> Presiden BEM -> DPK -> Ketua Jurusan
     * - Dosen/Tendik: Ketua Pelaksana -> Ketua Jurusan
     * Lalu, jika salah satu ruangan yang dipinjam berkategori 'Umum' (fasilitas kampus),
     * alur di atas ditambah satu tahap eskalasi terakhir ke Wakil Direktur II (arahan klien)
     * — lihat alurApprovalUntukPengajuan().
     * Seluruhnya SEKUENSIAL (tidak ada lagi tahap paralel). Tahap 0 (Ketua Pelaksana) langsung
     * aktif (batas_waktu diisi); tahap berikutnya dibuat 'Menunggu' tanpa batas_waktu sampai
     * tahap sebelumnya disetujui (lihat processApproval()).
     */
    public function generateApprovalStages(PengajuanModel $pengajuan): void
    {
        if ($pengajuan->ketua_pelaksana_user_id) {
            $jabatanKetuaPelaksana = $this->findOrCreateJabatanKetuaPelaksana($pengajuan->ketua_pelaksana_user_id);

            PengajuanApprovalModel::create([
                'pengajuan_id' => $pengajuan->pengajuan_id,
                'jabatan_id' => $jabatanKetuaPelaksana->jabatan_id,
                'urutan_tahap' => self::STAGE_KETUA_PELAKSANA,
                'status_approval' => 'Menunggu',
                'batas_waktu' => now()->addDays(2),
            ]);
        }

        $posisiUrutan = $this->alurApprovalUntukPengajuan($pengajuan);

        // Bulk-load semua posisi approval yang diperlukan dalam SATU query,
        // daripada N query terpisah di loop (query pertama untuk tiap posisi).
        // Gunakan ->get()->keyBy() untuk akses O(1) alih-alih ->first() per iterasi.
        $jabatanByPosisi = JabatanApprovalModel::whereIn('posisi_approval', $posisiUrutan)
            ->get()
            ->groupBy('posisi_approval');

        foreach ($posisiUrutan as $index => $posisi) {
            $jabatan = $jabatanByPosisi->get($posisi)?->first(function ($j) use ($pengajuan) {
                if ($j->posisi_approval === 'Ketua Umum') {
                    return $j->organisasi_id === $pengajuan->organisasi_id;
                }
                return true;
            });

            if (!$jabatan) {
                continue;
            }

            PengajuanApprovalModel::create([
                'pengajuan_id' => $pengajuan->pengajuan_id,
                'jabatan_id' => $jabatan->jabatan_id,
                'urutan_tahap' => $index + 1,
                'batas_waktu' => null,
            ]);
        }
    }

    /**
     * Urutan posisi_approval (tanpa Ketua Pelaksana, sudah ditangani terpisah) untuk sebuah
     * pengajuan: role akun pemohon menentukan rantai dasarnya, lalu kategori ruangan yang
     * dipinjam menentukan apakah rantai itu perlu diperpanjang ke Wakil Direktur II.
     */
    protected function alurApprovalUntukPengajuan(PengajuanModel $pengajuan): array
    {
        $role = $pengajuan->user->getRole();

        $alur = in_array($role, [RoleConstants::DOSEN, RoleConstants::TENDIK], true)
            ? ['Ketua Jurusan']
            : ['Ketua Umum', 'Presiden BEM', 'DPK', 'Ketua Jurusan'];

        // Ruangan kategori 'Umum' (fasilitas kampus) butuh eskalasi tambahan ke Wadir II
        // setelah Ketua Jurusan. Pengajuan dengan ruangan campuran (ada Umum + Jurusan)
        // dianggap Umum — aturan terketat yang berlaku (arahan klien).
        if ($pengajuan->ruangans->contains('ruangan_kategori', 'Umum')) {
            $alur[] = 'Wakil Direktur II';
        }

        return $alur;
    }

    /**
     * Cari baris m_jabatan_approval untuk user ini sebagai Ketua Pelaksana, atau buat baru
     * kalau belum pernah. Dibungkus try/catch unique-constraint (user_id+posisi_approval)
     * untuk menangani race condition: dua submission nyaris bersamaan memilih orang yang
     * sama sebagai Ketua Pelaksana — yang gagal insert cukup fetch ulang baris yang menang.
     */
    protected function findOrCreateJabatanKetuaPelaksana(int $userId): JabatanApprovalModel
    {
        $existing = JabatanApprovalModel::where('user_id', $userId)->where('posisi_approval', 'Ketua Pelaksana')->first();
        if ($existing) {
            return $existing;
        }

        try {
            return JabatanApprovalModel::create([
                'user_id' => $userId,
                'organisasi_id' => null,
                'posisi_approval' => 'Ketua Pelaksana',
                'urutan_approval' => 0,
            ]);
        } catch (QueryException $e) {
            return JabatanApprovalModel::where('user_id', $userId)->where('posisi_approval', 'Ketua Pelaksana')->firstOrFail();
        }
    }

    /**
     * Ambil pengajuan untuk diedit oleh pemiliknya sendiri. Hanya boleh diedit selama
     * masih berstatus 'Diajukan' (belum diproses approval manapun) — sama seperti guard
     * pada accept()/reject(), lihat guardProcessable().
     */
    public function findEditable(int $id, int $loggedInUserId): array
    {
        $pengajuan = PengajuanModel::with(['ruangans', 'panitias'])->find($id);
        if (!$pengajuan) {
            return ['status' => false, 'message' => 'Data pengajuan tidak ditemukan!', 'http_status' => 404];
        }

        if ($pengajuan->user_id !== $loggedInUserId) {
            return ['status' => false, 'message' => 'Anda tidak berwenang mengedit pengajuan ini.', 'http_status' => 403];
        }

        $guard = $this->guardProcessable($pengajuan);
        if ($guard) {
            return $guard;
        }

        return ['status' => true, 'pengajuan' => $pengajuan];
    }

    public function update(int $id, array $data, int $loggedInUserId): array
    {
        $existing = $this->findEditable($id, $loggedInUserId);
        if (!$existing['status']) {
            return $existing;
        }
        $pengajuan = $existing['pengajuan'];

        // AMBIL ROLE USER SEPERTI DI FUNGSI CREATE
        $pengaju = UserModel::find($loggedInUserId);
        $rolePengaju = $pengaju?->getRole();

        // LEMPAR ROLE KE DALAM RULES
        $validator = Validator::make($data, $this->rules($rolePengaju));

        //$validator = Validator::make($data, $this->rules());
        if ($validator->fails()) {
            return ['status' => false, 'message' => 'Validasi Gagal', 'msgField' => $validator->errors()];
        }

        $ketuaPelaksanaRole = UserModel::find($data['ketua_pelaksana_user_id'])?->getRole();
        if (!in_array($ketuaPelaksanaRole, [RoleConstants::MAHASISWA, RoleConstants::DOSEN], true)) {
            return ['status' => false, 'message' => 'Validasi Gagal', 'msgField' => ['ketua_pelaksana_user_id' => ['Ketua Pelaksana harus akun Mahasiswa atau Dosen.']]];
        }

        $ruanganLamaIds = $pengajuan->ruangans->pluck('ruangan_id')->toArray();

        // Validasi Tanggal Kegiatan harus berada dalam Periode yang 'Open'
        $tanggalPengajuan = $data['pengajuan_tgl'];
        $periodeBuka = DB::table('m_periode')
            ->where('periode_status', 'Open')
            ->where('tanggal_mulai', '<=', $tanggalPengajuan)
            ->where('tanggal_selesai', '>=', $tanggalPengajuan)
            ->first();

        if (!$periodeBuka) {
            return [
                'status' => false, 
                'message' => 'Validasi Gagal', 
                'msgField' => [
                    'pengajuan_tgl' => ['Periode untuk tanggal yang dipilih sedang tutup.']
                ]
            ];
        }

        $pengajuan->update($this->payload($data, $loggedInUserId));
        $pengajuan->ruangans()->sync($data['ruangan_ids']);
        $this->syncPanitia($pengajuan, $data);

        // Bebaskan ruangan lama yang tidak lagi dipakai, tandai ruangan baru sebagai Diajukan.
        $this->syncRuanganStatus(array_diff($ruanganLamaIds, $data['ruangan_ids']), 'Tersedia');
        $this->syncRuanganStatus($data['ruangan_ids'], 'Diajukan');

        return ['status' => true, 'message' => 'Pengajuan peminjaman berhasil diperbarui!', 'pengajuan_id' => $pengajuan->pengajuan_id];
    }

    public function accept(int $id): array
    {
        $pengajuan = PengajuanModel::find($id);
        if (!$pengajuan) {
            return ['status' => false, 'message' => 'Data pengajuan tidak ditemukan!', 'http_status' => 404];
        }

        // Cari jadwal operasional terkait berdasarkan heuristik
        $jadwal = JadwalModel::with('ruangans')
            ->where('user_id', $pengajuan->user_id)
            ->where('jadwal_nama', $pengajuan->pengajuan_nama)
            ->whereDate('jadwal_tgl', \Carbon\Carbon::parse($pengajuan->pengajuan_tgl)->format('Y-m-d'))
            ->first();

        if (!$jadwal) {
            return ['status' => false, 'message' => 'Jadwal operasional tidak ditemukan!', 'http_status' => 404];
        }

        if ($jadwal->jadwal_status !== 'Ditinjau') {
            return ['status' => false, 'message' => 'Status jadwal saat ini bukan Ditinjau. Tidak bisa diproses.', 'http_status' => 422];
        }

        // 1. Ubah status operasional jadwal menjadi 'Selesai'
        $jadwal->update(['jadwal_status' => 'Selesai']);

        // 2. Bebaskan status ruangan agar kembali 'Tersedia' untuk dipinjam orang lain
        $ruanganIds = $jadwal->ruangans->pluck('ruangan_id')->toArray();
        if (!empty($ruanganIds)) {
            $this->syncRuanganStatus($ruanganIds, 'Tersedia');
        }

        return ['status' => true, 'message' => 'Penyelesaian berhasil diterima! Peminjaman selesai dan ruangan kembali tersedia.'];
    }

    /*public function accept(int $id): array
    {
        $pengajuan = PengajuanModel::with('ruangans')->find($id);
        $guard = $this->guardProcessable($pengajuan);
        if ($guard) {
            return $guard;
        }

        $pengajuan->update(['pengajuan_status' => 'Diterima', 'nomor_surat' => $this->generateNomorSurat($pengajuan)]);
        $this->createJadwalFromPengajuan($pengajuan);
        return ['status' => true, 'message' => 'Pengajuan diterima dan jadwal berhasil dibuat!'];
    }*/

    /**
     * Generate & simpan nomor surat saat pengajuan mencapai status Diterima (FR-9.3).
     * Format belum dikonfirmasi klien (lihat tasks_v2.0.md T6.3) — sementara memakai
     * konvensi umum surat resmi Indonesia: {urutan}/SPR-JTI/{bulan_romawi}/{tahun}.
     */
    protected function generateNomorSurat(PengajuanModel $pengajuan): string
    {
        $romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $tahun = now()->year;
        $urutan = PengajuanModel::whereYear('created_at', $tahun)->whereNotNull('nomor_surat')->count() + 1;

        return "{$urutan}/SPR-JTI/{$romawi[now()->month]}/{$tahun}";
    }

    protected function createJadwalFromPengajuan(PengajuanModel $pengajuan): void
    {
        $jadwal = JadwalModel::create($this->jadwalPayload($pengajuan));
        $ruanganIds = $pengajuan->ruangans->pluck('ruangan_id')->toArray();
        $jadwal->ruangans()->attach($ruanganIds);
        // Ruangan tetap berstatus 'Diajukan' (terpakai) selama jadwal aktif — enum status
        // hanya menyediakan Tersedia/Diajukan/Tidak Tersedia (lihat migration m_ruangan),
        // sehingga 'Diajukan' dipakai untuk merepresentasikan baik masa review maupun terpakai.
        $this->syncRuanganStatus($ruanganIds, 'Diajukan');
    }

    /**
     * Proses persetujuan/penolakan satu tahap approval oleh verifikator yang login (FR-6.5–FR-6.9).
     * Tahap paralel (urutan_tahap sama, mis. DPK & Presiden BEM) baru mengaktifkan tahap
     * berikutnya setelah SELURUH baris pada urutan_tahap tersebut berstatus 'Disetujui' (FR-6.4).
     */
    public function processApproval(int $approvalId, int $loggedInUserId, string $statusApproval, ?string $alasanPenolakan): array
    {
        $approval = PengajuanApprovalModel::with(['pengajuan.ruangans', 'jabatanApproval'])->find($approvalId);

        $guard = $this->guardApprovalProcessable($approval, $loggedInUserId, $statusApproval, $alasanPenolakan);
        if ($guard !== null) {
            return $guard;
        }

        $approval->update([
            'status_approval' => $statusApproval,
            'alasan_penolakan' => $statusApproval === 'Ditolak' ? $alasanPenolakan : null,
            'diproses_pada' => now(),
        ]);

        if ($statusApproval === 'Ditolak') {
            return $this->handleRejection($approval);
        }

        return $this->activateNextStageOrFinalize($approval);
    }

    /**
     * Validasi apakah approval dapat diproses. Mengembalikan array error atau null jika valid.
     */
    protected function guardApprovalProcessable(
        $approval,
        int $loggedInUserId,
        string $statusApproval,
        ?string $alasanPenolakan
    ): ?array {
        if (!$approval) {
            return ['status' => false, 'message' => 'Data tahap persetujuan tidak ditemukan!', 'http_status' => 404];
        }

        if (!$approval->jabatanApproval || $approval->jabatanApproval->user_id !== $loggedInUserId) {
            return ['status' => false, 'message' => 'Anda tidak berwenang memproses tahap persetujuan ini.', 'http_status' => 403];
        }

        if ($approval->status_approval !== 'Menunggu') {
            return ['status' => false, 'message' => 'Tahap persetujuan ini sudah diproses sebelumnya.', 'http_status' => 422];
        }

        if ($approval->batas_waktu === null) {
            return ['status' => false, 'message' => 'Tahap persetujuan ini belum aktif. Selesaikan tahap sebelumnya terlebih dahulu.', 'http_status' => 422];
        }

        if (!in_array($statusApproval, ['Disetujui', 'Ditolak'], true)) {
            return ['status' => false, 'message' => 'Status approval tidak valid.', 'http_status' => 422];
        }

        if ($statusApproval === 'Ditolak' && empty($alasanPenolakan)) {
            return ['status' => false, 'message' => 'Validasi Gagal', 'msgField' => ['alasan_penolakan' => ['Alasan penolakan wajib diisi.']]];
        }

        return null;
    }

    /**
     * Tangani penolakan approval: akhiri seluruh proses (FR-6.8) dan bebaskan ruangan.
     */
    protected function handleRejection($approval): array
    {
        $pengajuan = $approval->pengajuan;
        $pengajuan->update([
            'pengajuan_status' => 'Ditolak',
            'catatan_verifikator' => $approval->alasan_penolakan,
        ]);
        $this->syncRuanganStatus($pengajuan->ruangans->pluck('ruangan_id')->toArray(), 'Tersedia');

        return ['status' => true, 'message' => 'Pengajuan ditolak pada tahap ini.'];
    }

    /**
     * Setelah persetujuan: cek apakah semua approval pada urutan_tahap ini sudah selesai.
     * Jika ya, aktifkan tahap berikutnya atau finalisasi pengajuan menjadi Diterima (FR-6.9).
     */
    protected function activateNextStageOrFinalize($approval): array
    {
        $pengajuan = $approval->pengajuan;

        $stageFullyApproved = PengajuanApprovalModel::where('pengajuan_id', $pengajuan->pengajuan_id)
            ->where('urutan_tahap', $approval->urutan_tahap)
            ->where('status_approval', '!=', 'Disetujui')
            ->doesntExist();

        if (!$stageFullyApproved) {
            return ['status' => true, 'message' => 'Persetujuan tahap ini tercatat, menunggu persetujuan tahap lainnya.'];
        }

        $nextStage = PengajuanApprovalModel::where('pengajuan_id', $pengajuan->pengajuan_id)
            ->where('urutan_tahap', '>', $approval->urutan_tahap)
            ->orderBy('urutan_tahap')
            ->value('urutan_tahap');

        if (!$nextStage) {
            $pengajuan->update(['pengajuan_status' => 'Diterima', 'nomor_surat' => $this->generateNomorSurat($pengajuan)]);
            $this->createJadwalFromPengajuan($pengajuan);

            return ['status' => true, 'message' => 'Pengajuan berhasil diterima. Jadwal berhasil dibuat.'];
        }

        PengajuanApprovalModel::where('pengajuan_id', $pengajuan->pengajuan_id)
            ->where('urutan_tahap', $nextStage)
            ->update(['batas_waktu' => now()->addDays(2)]);

        return ['status' => true, 'message' => 'Pengajuan berhasil diterima. Pengajuan telah dikirimkan ke tahap berikutnya.'];
    }

    public function reject(int $id, array $data): array
    {
        $pengajuan = PengajuanModel::find($id);
        if (!$pengajuan) {
            return ['status' => false, 'message' => 'Data pengajuan tidak ditemukan!', 'http_status' => 404];
        }

        // Cari jadwal operasional terkait
        $jadwal = JadwalModel::where('user_id', $pengajuan->user_id)
            ->where('jadwal_nama', $pengajuan->pengajuan_nama)
            ->whereDate('jadwal_tgl', \Carbon\Carbon::parse($pengajuan->pengajuan_tgl)->format('Y-m-d'))
            ->first();

        if (!$jadwal) {
            return ['status' => false, 'message' => 'Jadwal operasional tidak ditemukan!', 'http_status' => 404];
        }

        if ($jadwal->jadwal_status !== 'Ditinjau') {
            return ['status' => false, 'message' => 'Status jadwal saat ini bukan Ditinjau. Tidak bisa diproses.', 'http_status' => 422];
        }

        $validator = Validator::make($data, [
            'catatan_verifikator' => 'required|string|max:500'
        ], [
            'catatan_verifikator.required' => 'Alasan penolakan wajib diisi untuk memberi tahu peminjam.'
        ]);

        if ($validator->fails()) {
            // Kita return pesan utamanya agar bisa ditangkap oleh alert bawaan
            return ['status' => false, 'message' => $validator->errors()->first('catatan_verifikator'), 'http_status' => 422];
        }

        // 1. Ubah status operasional mundur menjadi 'Dokumentasi Tidak Sesuai'
        $jadwal->update(['jadwal_status' => 'Dokumentasi Tidak Sesuai']);

        // 2. Simpan alasan penolakan ke pengajuan induk
        $pengajuan->update(['catatan_verifikator' => $data['catatan_verifikator']]);

        return ['status' => true, 'message' => 'Penyelesaian berhasil ditolak. Status dikembalikan ke peminjam untuk diperbaiki.'];
    }

    /*public function reject(int $id, array $data): array
    {
        $pengajuan = PengajuanModel::with('ruangans')->find($id);
        $guard = $this->guardProcessable($pengajuan);
        if ($guard) {
            return $guard;
        }

        $validator = Validator::make($data, ['catatan_verifikator' => 'nullable|string|max:500']);
        if ($validator->fails()) {
            return ['status' => false, 'message' => 'Validasi Gagal', 'msgField' => $validator->errors()];
        }

        $pengajuan->update(['pengajuan_status' => 'Ditolak', 'catatan_verifikator' => $data['catatan_verifikator'] ?? null]);
        $this->syncRuanganStatus($pengajuan->ruangans->pluck('ruangan_id')->toArray(), 'Tersedia');
        return ['status' => true, 'message' => 'Pengajuan berhasil ditolak.'];
    }*/

    /**
     * Sinkronisasi status ruangan mengikuti status pengajuan terkait (FR-3.2).
     * Trigger dilakukan langsung di Service (bukan Eloquent Observer), konsisten dengan
     * pola existing di mana seluruh logika bisnis pengajuan berada di PengajuanService.
     */
    protected function syncRuanganStatus(array $ruanganIds, string $status): void
    {
        if (empty($ruanganIds)) {
            return;
        }

        RuanganModel::whereIn('ruangan_id', $ruanganIds)->update(['ruangan_status' => $status]);
    }

    /**
     * Ambil pengajuan untuk dicetak sebagai surat digital (FR-9.1). Hanya boleh dicetak
     * jika pengajuan_status = 'Diterima'.
     */
    public function findForCetakSurat(int $id): array
    {
        $pengajuan = PengajuanModel::with([
            'user.dosen',
            'user.mahasiswa',
            'user.tendik',
            'organisasi',
            'ruangans',
            'ketuaPelaksana.dosen',
            'ketuaPelaksana.mahasiswa',
            'panitias.user.dosen',
            'panitias.user.mahasiswa.prodi',
            'panitias.user.tendik',
            'panitias.user.admin',
            // Rantai tanda tangan surat (FR-9.2): urutkan per tahap approval, lalu eager-load
            // nama + NIDN/NIM penandatangan (dosen pakai dosen_nidn, mahasiswa pakai mahasiswa_nim
            // — sistem ini tidak menyimpan NIP dosen, lihat DosenModel::$fillable).
            'approvals' => fn ($query) => $query->orderBy('urutan_tahap'),
            'approvals.jabatanApproval.user.dosen',
            'approvals.jabatanApproval.user.mahasiswa',
            'approvals.jabatanApproval.user.tendik',
        ])->find($id);
        if (!$pengajuan) {
            return ['status' => false, 'message' => 'Data pengajuan tidak ditemukan!', 'http_status' => 404];
        }

        if ($pengajuan->pengajuan_status !== 'Diterima') {
            return ['status' => false, 'message' => 'Surat hanya dapat dicetak untuk pengajuan berstatus Diterima.', 'http_status' => 422];
        }

        return ['status' => true, 'pengajuan' => $pengajuan];
    }

    public function findDetailed(int $id): ?PengajuanModel
    {
        return PengajuanModel::with(['user.level', 'user.mahasiswa.prodi', 'user.mahasiswa.kelas', 'user.dosen.prodi', 'user.tendik', 'ruangans', 'ketuaPelaksana.dosen', 'ketuaPelaksana.mahasiswa'])->find($id);
    }

    /**
     * Daftar tahap approval yang menunggu persetujuan verifikator yang login (FR-6.5).
     * Hanya tahap yang sudah AKTIF (batas_waktu terisi) yang ditampilkan — tahap yang
     * masih menunggu tahap sebelumnya (batas_waktu masih null) belum jadi giliran verifikator.
     */
    public function antrianFor($user)
    {
        // Satu user bisa memegang lebih dari satu jabatan approval (lihat
        // UserModel::jabatanApprovals(), hasMany) — kumpulkan semua jabatan_id miliknya.
        $jabatanIds = JabatanApprovalModel::where('user_id', $user->user_id)->pluck('jabatan_id');
        if ($jabatanIds->isEmpty()) {
            return collect();
        }

        return PengajuanApprovalModel::with(['pengajuan.user', 'pengajuan.user.level', 'pengajuan.user.mahasiswa.prodi', 'pengajuan.user.mahasiswa.kelas', 'pengajuan.ruangans', 'pengajuan.organisasi', 'pengajuan.ketuaPelaksana.dosen', 'pengajuan.ketuaPelaksana.mahasiswa'])
            ->whereIn('jabatan_id', $jabatanIds)
            ->where('status_approval', 'Menunggu')
            ->whereNotNull('batas_waktu')
            //Memastikan status induknya masih murni 'Diajukan'
            ->whereHas('pengajuan', function ($query) {
                $query->where('pengajuan_status', 'Diajukan');
            })
            ->orderBy('batas_waktu')
            ->get();
    }

    /**
     * Timeline seluruh tahap approval sebuah pengajuan, diurutkan sesuai urutan tahap (FR-5.3).
     * Hanya boleh dilihat oleh user peminjam ruangan yang mengajukan (pengajuan->user_id) —
     * verifikator/admin/user lain TIDAK boleh mengakses timeline pengajuan milik orang lain,
     * meskipun mereka boleh melihat detail pengajuannya sendiri lewat show_ajax/antrian.
     */
    public function timelineFor(int $pengajuanId, int $loggedInUserId): array
    {
        $pengajuan = PengajuanModel::find($pengajuanId);
        if (!$pengajuan) {
            return ['status' => false, 'message' => 'Data pengajuan tidak ditemukan!', 'http_status' => 404];
        }

        if ($pengajuan->user_id !== $loggedInUserId) {
            return ['status' => false, 'message' => 'Anda tidak berwenang melihat timeline persetujuan pengajuan ini.', 'http_status' => 403];
        }

        $timeline = PengajuanApprovalModel::with('jabatanApproval.user')
            ->where('pengajuan_id', $pengajuanId)
            ->orderBy('urutan_tahap')
            ->get();

        return ['status' => true, 'timeline' => $timeline];
    }

    protected function visibleFor($user)
    {
        $isAdmin = $user->getRole() === RoleConstants::ADMIN;
        $query = PengajuanModel::with(['user', 'ruangans'])->orderByDesc('created_at');
        $pengajuans = $isAdmin ? $query->get() : $query->where('user_id', $user->user_id)->get();

        // Bulk-load operational status untuk SEMUA pengajuan Diterima dalam 1 query,
        // bukan N+1 query terpisah via operationalStatusFor() per record.
        // Sekaligus men-set jadwal_foto_lengkap pada tiap record (dipakai filter ADM di bawah).
        $this->attachBulkOperationalStatus($pengajuans);

        // REQ 1 — Daftar Penyelesaian (ADM): hanya tampilkan pengajuan yang jadwal terkaitnya
        // sudah memiliki foto_penggunaan, foto_kebersihan, DAN foto_kunci semuanya tidak null
        // dan tidak kosong. Pengajuan tanpa jadwal yang cocok (jadwal_foto_lengkap === false)
        // tidak masuk daftar ini.
        if ($isAdmin) {
            $pengajuans = $pengajuans->filter(fn ($p) => $p->jadwal_foto_lengkap === true && $p->status_operasional === 'Ditinjau')->values();
            //$pengajuans = $pengajuans->filter(fn ($p) => $p->jadwal_foto_lengkap === true)->values();
        }

        return $pengajuans;
    }

    /**
     * Attach operational status (jadwal_status) dan flag foto-lengkap ke setiap pengajuan
     * Diterima dalam SATU bulk query, menghilangkan N+1 dari operationalStatusFor() yang
     * dipanggil per record.
     * Jadwal dibuat dari pengajuan Diterima dengan heuristic (user_id + nama + tanggal),
     * karena tidak ada FK langsung antara t_pengajuan dan t_jadwal.
     *
     * Properti yang di-attach ke setiap pengajuan:
     *   - status_operasional : nilai jadwal_status dari t_jadwal, atau null jika tidak cocok.
     *   - jadwal_foto_lengkap: true hanya jika jadwal yang cocok memiliki foto_penggunaan,
     *                          foto_kebersihan, DAN foto_kunci semuanya tidak null dan tidak
     *                          kosong (REQ 1 — filter Daftar Penyelesaian ADM).
     */
    protected function attachBulkOperationalStatus($pengajuans): void
    {
        $diterima = $pengajuans->where('pengajuan_status', 'Diterima');

        // Set semua ke null / false terlebih dahulu
        foreach ($pengajuans as $pengajuan) {
            $pengajuan->status_operasional  = null;
            $pengajuan->jadwal_foto_lengkap = false;
        }

        if ($diterima->isEmpty()) {
            return;
        }

        // Satu query bulk untuk semua kemungkinan kecocokan jadwal.
        // Tambahkan kolom foto agar dapat dicek kelengkapannya tanpa query tambahan.
        $jadwalByKey = JadwalModel::whereIn('user_id', $diterima->pluck('user_id')->unique())
            ->whereIn('jadwal_nama', $diterima->pluck('pengajuan_nama')->unique())
            ->whereIn('jadwal_tgl', $diterima->pluck('pengajuan_tgl')->unique())
            ->get(['user_id', 'jadwal_nama', 'jadwal_tgl', 'jadwal_status', 'foto_penggunaan', 'foto_kebersihan', 'foto_kunci'])
            ->mapWithKeys(fn ($j) => [$j->user_id . '|' . $j->jadwal_nama . '|' . $j->jadwal_tgl => $j]);

        foreach ($pengajuans as $pengajuan) {
            if ($pengajuan->pengajuan_status !== 'Diterima') {
                continue;
            }
            $key    = $pengajuan->user_id . '|' . $pengajuan->pengajuan_nama . '|' . $pengajuan->pengajuan_tgl;
            $jadwal = $jadwalByKey[$key] ?? null;

            $pengajuan->status_operasional  = $jadwal?->jadwal_status;

            // REQ 1: flag kelengkapan foto — true hanya jika ketiga kolom foto terisi
            $pengajuan->jadwal_foto_lengkap = $jadwal !== null
                && !empty($jadwal->foto_penggunaan)
                && !empty($jadwal->foto_kebersihan)
                && !empty($jadwal->foto_kunci);
        }
    }

    /**
     * Auto reject tahap approval yang lewat batas_waktu tanpa respons verifikator (FR-7.1–FR-7.3).
     * Idempotent (NFR-3.3): hanya memproses baris berstatus 'Menunggu' dengan batas_waktu terlampaui;
     * baris yang sudah diubah menjadi 'Auto Reject' tidak akan terjaring lagi pada run berikutnya.
     */
    public function autoRejectExpiredApprovals(): int
    {
        $expired = PengajuanApprovalModel::with('pengajuan.ruangans')
            ->where('status_approval', 'Menunggu')
            ->whereNotNull('batas_waktu')
            ->where('batas_waktu', '<', now())
            ->get();

        $count = 0;
        $alasan = 'Tidak diproses dalam batas waktu 2 hari';

        foreach ($expired as $approval) {
            $pengajuan = $approval->pengajuan;
            if (!$pengajuan || $pengajuan->pengajuan_status !== 'Diajukan') {
                // Pengajuan sudah diproses/dihapus di antara pengecekan — lewati agar idempotent
                continue;
            }

            $approval->update([
                'status_approval' => 'Auto Reject',
                'alasan_penolakan' => $alasan,
                'diproses_pada' => now(),
            ]);

            $pengajuan->update(['pengajuan_status' => 'Ditolak', 'catatan_verifikator' => $alasan]);
            $this->syncRuanganStatus($pengajuan->ruangans->pluck('ruangan_id')->toArray(), 'Tersedia');
            $count++;
        }

        return $count;
    }

    protected function guardProcessable($pengajuan): ?array
    {
        if (!$pengajuan) {
            return ['status' => false, 'message' => 'Data pengajuan tidak ditemukan!', 'http_status' => 404];
        }

        return $pengajuan->pengajuan_status === 'Diajukan' ? null : ['status' => false, 'message' => 'Pengajuan ini sudah diproses sebelumnya.', 'http_status' => 422];
    }

    protected function rules(?string $role = null): array
    {
        $rules = [
            'pengajuan_nama' => 'required|string|max:255',
            'pengajuan_tgl' => 'required|date',
            'pengajuan_jam_mulai' => 'required|date_format:H:i',
            'pengajuan_jam_selesai' => 'required|date_format:H:i|after:pengajuan_jam_mulai',
            'pengajuan_jumPes' => 'required|integer|min:1',
            'pengajuan_keterangan' => 'nullable|string',
            'ketua_pelaksana_user_id' => 'required|exists:m_user,user_id',
            'ruangan_ids' => 'required|array',
            'ruangan_ids.*' => 'exists:m_ruangan,ruangan_id',
            'panitia_user_id' => 'nullable|array',
            'panitia_user_id.*' => 'nullable|exists:m_user,user_id',
            'panitia_keterangan' => 'nullable|array',
            'panitia_keterangan.*' => 'nullable|string|max:100',
        ];

        // Buat validasi dinamis: Jika Mahasiswa maka Wajib (required), selain itu boleh kosong (nullable)
        if ($role === 'MHS') {
            $rules['organisasi_id'] = 'required|exists:m_organisasi,organisasi_id';
        } else {
            $rules['organisasi_id'] = 'nullable|exists:m_organisasi,organisasi_id';
        }

        return $rules;
    }

    /*protected function rules(): array
    {
        $rules = [
            'pengajuan_nama' => 'required|string|max:255',
            'pengajuan_tgl' => 'required|date',
            'pengajuan_jam_mulai' => 'required|date_format:H:i',
            'pengajuan_jam_selesai' => 'required|date_format:H:i|after:pengajuan_jam_mulai',
            'pengajuan_jumPes' => 'required|integer|min:1',
            'pengajuan_keterangan' => 'nullable|string',
            'ketua_pelaksana_user_id' => 'required|exists:m_user,user_id',
            'ruangan_ids' => 'required|array',
            'ruangan_ids.*' => 'exists:m_ruangan,ruangan_id'
        ];

        // Buat validasi dinamis: Jika Mahasiswa maka Wajib (required), selain itu boleh kosong (nullable)
        if ($role === 'MHS') {
            $rules['organisasi_id'] = 'required|exists:m_organisasi,organisasi_id';
        } else {
            $rules['organisasi_id'] = 'nullable|exists:m_organisasi,organisasi_id';
        }

        return $rules;
        //return ['pengajuan_nama' => 'required|string|max:255', 'pengajuan_tgl' => 'required|date', 'pengajuan_jam_mulai' => 'required|date_format:H:i', 'pengajuan_jam_selesai' => 'required|date_format:H:i|after:pengajuan_jam_mulai', 'pengajuan_jumPes' => 'required|integer|min:1', 'pengajuan_keterangan' => 'nullable|string', 'organisasi_id' => 'required|exists:m_organisasi,organisasi_id', 'ketua_pelaksana_user_id' => 'required|exists:m_user,user_id', 'ruangan_ids' => 'required|array', 'ruangan_ids.*' => 'exists:m_ruangan,ruangan_id'];
    }*/

    protected function payload(array $data, int $userId): array
    {
        return ['user_id' => $userId, 'organisasi_id' => $data['organisasi_id'] ?? null, 'ketua_pelaksana_user_id' => $data['ketua_pelaksana_user_id'], 'pengajuan_nama' => $data['pengajuan_nama'], 'pengajuan_tgl' => $data['pengajuan_tgl'], 'pengajuan_jam_mulai' => $data['pengajuan_jam_mulai'], 'pengajuan_jam_selesai' => $data['pengajuan_jam_selesai'], 'pengajuan_jumPes' => $data['pengajuan_jumPes'], 'pengajuan_keterangan' => $data['pengajuan_keterangan'] ?? null, 'pengajuan_status' => 'Diajukan'];
    }

    protected function jadwalPayload($pengajuan): array
    {
        return ['user_id' => $pengajuan->user_id, 'jadwal_nama' => $pengajuan->pengajuan_nama, 'jadwal_tgl' => $pengajuan->pengajuan_tgl, 'jadwal_jam_mulai' => $pengajuan->pengajuan_jam_mulai, 'jadwal_jam_selesai' => $pengajuan->pengajuan_jam_selesai, 'jadwal_jumPes' => $pengajuan->pengajuan_jumPes];
    }
}
