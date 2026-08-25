<?php

namespace App\Services;

use App\Constants\RoleConstants;
use App\Models\JabatanApprovalModel;
use App\Models\PengajuanApprovalModel;
use App\Models\PengajuanModel;
use App\Models\RuanganModel;
use App\Models\JadwalModel;
use App\Services\Interfaces\DashboardServiceInterface;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService implements DashboardServiceInterface
{
    /**
     * Get dashboard statistics
     *
     * @return array
     */
    public function getDashboardStats(): array
    {
        $today = now()->toDateString();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Gabungkan 6 count query individu menjadi SATU query batch
        // menggunakan UNION ALL — lebih cepat daripada 6 query terpisah.
        $counts = DB::select("
            SELECT 'totalRuangan' AS stat, COUNT(*) AS value FROM m_ruangan
            UNION ALL SELECT 'jadwalHariIni', COUNT(*) FROM t_jadwal WHERE DATE(jadwal_tgl) = ?
            UNION ALL SELECT 'totalUser', COUNT(*) FROM m_user
            UNION ALL SELECT 'totalMahasiswa', COUNT(*) FROM m_mahasiswa
            UNION ALL SELECT 'totalDosen', COUNT(*) FROM m_dosen
            UNION ALL SELECT 'totalTendik', COUNT(*) FROM m_tendik
        ", [$today]);

        $statMap = [];
        foreach ($counts as $row) {
            $statMap[$row->stat] = (int) $row->value;
        }

        $totalRuangan = $statMap['totalRuangan'];
        $jadwalHariIni = $statMap['jadwalHariIni'];

        $ruanganTerpakaiHariIni = DB::table('t_jadwal')
            ->join('t_jadwal_ruangan', 't_jadwal.jadwal_id', '=', 't_jadwal_ruangan.jadwal_id')
            ->whereDate('t_jadwal.jadwal_tgl', $today)
            ->distinct('t_jadwal_ruangan.ruangan_id')
            ->count('t_jadwal_ruangan.ruangan_id');

        $totalRuanganKosong = $totalRuangan - $ruanganTerpakaiHariIni;
        $totalUser = $statMap['totalUser'];
        $totalMahasiswa = $statMap['totalMahasiswa'];
        $totalDosen = $statMap['totalDosen'];
        $totalTendik = $statMap['totalTendik'];

        // Bar Chart: Top 5 Ruangan Terfavorit (Bulan Ini)
        // Catatan: filter "count > 0" dilakukan via whereHas (bukan HAVING pada kolom hasil
        // withCount) karena HAVING pada subquery alias tidak didukung SQLite (dipakai di
        // lingkungan testing) meski jalan di MySQL — whereHas portable ke keduanya.
        $topRuanganFilter = function ($q) use ($currentMonth, $currentYear) {
            $q->whereMonth('jadwal_tgl', $currentMonth)->whereYear('jadwal_tgl', $currentYear);
        };
        $topRuangan = RuanganModel::withCount(['jadwal' => $topRuanganFilter])
            ->whereHas('jadwal', $topRuanganFilter)
            ->orderByDesc('jadwal_count')
            ->limit(5)
            ->get();

        // Pie Chart: Distribusi Peminjam (Bulan Ini)
        $distribusiPeminjam = [
            'admin' => 0,
            'dosen' => 0,
            'tendik' => 0,
            'mahasiswa' => 0
        ];

        $peminjamanBulanIni = DB::table('t_jadwal')
            ->join('m_user', 't_jadwal.user_id', '=', 'm_user.user_id')
            ->join('m_level', 'm_user.level_id', '=', 'm_level.level_id')
            ->whereMonth('jadwal_tgl', $currentMonth)
            ->whereYear('jadwal_tgl', $currentYear)
            ->select('m_level.level_kode', DB::raw('count(*) as total'))
            ->groupBy('m_level.level_kode')
            ->get();

        foreach($peminjamanBulanIni as $item){
            if($item->level_kode == RoleConstants::ADMIN) $distribusiPeminjam['admin'] = $item->total;
            if($item->level_kode == RoleConstants::DOSEN) $distribusiPeminjam['dosen'] = $item->total;
            if($item->level_kode == RoleConstants::TENDIK) $distribusiPeminjam['tendik'] = $item->total;
            if($item->level_kode == RoleConstants::MAHASISWA) $distribusiPeminjam['mahasiswa'] = $item->total;
        }

        // Line Chart: Tren Peminjaman (6 Bulan Terakhir)
        // Catatan: pengelompokan per bulan dilakukan di PHP (bukan DATE_FORMAT() SQL, fungsi
        // khusus MySQL) agar portable ke SQLite yang dipakai lingkungan testing.
        // 1. Batas awal query: awal bulan dari 5 bulan yang lalu
        $startDate = now()->subMonths(5)->startOfMonth();

        // 2. Ambil data dari database mulai tanggal tersebut
        $groupedData = DB::table('t_jadwal')
            ->where('jadwal_tgl', '>=', $startDate)
            ->pluck('jadwal_tgl')
            ->groupBy(fn ($tgl) => \Carbon\Carbon::parse($tgl)->format('Y-m'));

        // 3. Susun $trenPeminjaman sebagai Collection pas 6 bulan
        $trenPeminjaman = collect();

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');

            $total = isset($groupedData[$monthKey]) ? count($groupedData[$monthKey]) : 0;

            $trenPeminjaman->push((object) [
                'bulan' => $monthKey,
                'total' => $total
            ]);
        }
        /*$sixMonthsAgo = now()->subMonths(6);
        $groupedByMonth = DB::table('t_jadwal')
            ->where('jadwal_tgl', '>=', $sixMonthsAgo)
            ->pluck('jadwal_tgl')
            ->groupBy(fn ($tgl) => \Carbon\Carbon::parse($tgl)->format('Y-m'));

        $trenPeminjaman = collect($groupedByMonth)
            ->map(fn ($items, $bulan) => (object) ['bulan' => $bulan, 'total' => count($items)])
            ->sortBy('bulan')
            ->values();*/

        // 4. Proses ke $lineChartData tetap bisa berjalan
        $lineChartData = [];
        foreach($trenPeminjaman as $t){
            $lineChartData[] = [
                'tanggal' => $t->bulan,
                'total_peminjaman' => $t->total
            ];
        }

        return [
            'totalRuangan' => $totalRuangan, 
            'jadwalHariIni' => $jadwalHariIni, 
            'totalRuanganKosong' => $totalRuanganKosong, 
            'totalUser' => $totalUser, 
            'topRuangan' => $topRuangan, 
            'distribusiPeminjam' => $distribusiPeminjam, 
            'trenPeminjaman' => $trenPeminjaman,
            'lineChartData' => $lineChartData,
            'totalMahasiswa' => $totalMahasiswa,
            'totalDosen' => $totalDosen,
            'totalTendik' => $totalTendik
        ];
    }

    // Role yang punya dashboard & menu khusus. Akun dengan level_kode DI LUAR daftar ini
    // dianggap "tanpa permission": bisa login, tapi tidak ada satupun menu/route
    // authorize:* yang mengizinkan mereka — lihat resolveWelcomeViewName().
    // VRF: verifikator yang memverifikasi/menyetujui pengajuan langsung (bypass approval
    // berjenjang) — lihat PengajuanController::verifikasi_ajax()/terima_ajax()/tolak_ajax().
    // VRF was removed — approval capability is now tied to m_jabatan_approval entries,
    // not a separate level_kode. Accounts whose level_kode is not in this list are shown
    // dashboard/no-permission.blade.php (see resolveWelcomeViewName()).
    protected const ROLES_WITH_DASHBOARD = ['ADM', 'DSN', 'TDK', 'MHS'];

    /**
     * Build the view name and payload for the welcome dashboard.
     *
     * Optimasi: cek permission SEBELUM memanggil getDashboardStats() yang berat (10+ queries).
     * Akun tanpa permission hanya butuh breadcrumb + accountRoleName, bukan statistik sistem.
     */
    public function getWelcomeDashboardData(?object $user): array
    {
        $role = $user?->getRole();
        $hasPermission = in_array($role, self::ROLES_WITH_DASHBOARD, true);

        $breadcrumb = (object) [
            'title' => 'Selamat Datang!',
            'list' => ['Home', 'Dashboard'],
        ];

        if (!$hasPermission) {
            return [
                'view' => $this->resolveWelcomeViewName($user),
                'data' => [
                    'breadcrumb' => $breadcrumb,
                    'activeMenu' => 'dashboard',
                    'accountRoleName' => $user?->getRoleName() ?: ($role ?: 'Tidak diketahui'),
                ],
            ];
        }

        $stats = $this->getDashboardStats();

        $data = [
            'breadcrumb' => $breadcrumb,
            'activeMenu' => 'dashboard',
            'totalRuangan' => $stats['totalRuangan'],
            'jadwalHariIni' => $stats['jadwalHariIni'],
            'totalRuanganKosong' => $stats['totalRuanganKosong'],
            'totalUser' => $stats['totalUser'],
            'topRuangan' => $stats['topRuangan'],
            'distribusiPeminjam' => $stats['distribusiPeminjam'],
            'trenPeminjaman' => $stats['trenPeminjaman'],
            'lineChartData' => $stats['lineChartData'],
            'totalMahasiswa' => $stats['totalMahasiswa'],
            'totalDosen' => $stats['totalDosen'],
            'totalTendik' => $stats['totalTendik'],
        ];

        // Dashboard Role-Aware (G5): data tambahan disisipkan sesuai role, tanpa mengurangi
        // statistik existing yang sudah ditampilkan di atas (FR-8.5).
        if (in_array($role, ['DSN', 'TDK', 'MHS'], true)) {
            $data['pengajuanAktif'] = $this->getPengajuanAktifFor($user->user_id);
            // Req 9: Ganti 'ruanganHariIni' → 'jadwalBerlangsungHariIni' (jadwal berstatus Berlangsung hari ini)
            $data['jadwalBerlangsungHariIni'] = $this->getJadwalBerlangsungHariIni();
        }

        // Antrian approval ditampilkan untuk siapapun yang memegang jabatan approval,
        // terlepas dari level akunnya (dosen/mahasiswa) — level VRF terpisah sudah dihapus.
        if ($user && JabatanApprovalModel::where('user_id', $user->user_id)->exists()) {
            $data['antrianApproval'] = $this->getAntrianApprovalFor($user->user_id);
        }

        if ($role === 'ADM') {
            $data['earlyWarning'] = $this->getEarlyWarningPengajuan();
        }

        // NOTE: VRF dashboard block removed — VRF is no longer a standalone role.
        // Approval capability is now managed via m_jabatan_approval (any DSN/MHS can hold
        // a jabatan approval); see getAntrianApprovalFor() which is already role-agnostic.

        return [
            'view' => $this->resolveWelcomeViewName($user),
            'data' => $data,
        ];
    }

    /**
     * Data agregat yang aman ditampilkan ke publik (tanpa login) — hanya 4 hal:
     * stat cards (Total Ruangan, Ruangan Kosong, Jadwal Hari Ini), Top 5 Ruangan
     * Terfavorit, Tren Peminjaman (6 bulan), dan Distribusi Peminjam per role.
     * SENGAJA tidak menyertakan pengajuanAktif/antrianApproval/earlyWarning atau
     * data lain yang menyentuh identitas/aktivitas akun tertentu.
     */
    public function getGuestDashboardData(): array
    {
        $stats = $this->getDashboardStats();

        return [
            'totalRuangan' => $stats['totalRuangan'],
            'jadwalHariIni' => $stats['jadwalHariIni'],
            'totalRuanganKosong' => $stats['totalRuanganKosong'],
            'topRuangan' => $stats['topRuangan'],
            'distribusiPeminjam' => $stats['distribusiPeminjam'],
            'trenPeminjaman' => $stats['trenPeminjaman'],
        ];
    }

    /**
     * Status pengajuan aktif milik user login (FR-8.1) — dashboard Peminjam (DSN/TDK/MHS).
     */
    public function getPengajuanAktifFor(int $userId)
    {
        return PengajuanModel::with('ruangans')
            ->where('user_id', $userId)
            ->where('pengajuan_status', 'Diajukan')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Tabel ruangan pada hari berjalan (FR-8.2) — dashboard Peminjam.
     * @deprecated Digantikan oleh getJadwalBerlangsungHariIni() sejak Req 9.
     */
    public function getRuanganHariIni()
    {
        $today = now()->toDateString();

        return RuanganModel::with(['jadwal' => function ($q) use ($today) {
            $q->whereDate('jadwal_tgl', $today);
        }])->get();
    }

    /**
     * Req 9: Jadwal berstatus 'Berlangsung' pada hari ini — dashboard Peminjam.
     * Menggantikan 'Ruangan Hari Ini' sesuai permintaan requirement 9.
     * Max 5 per halaman diatur di sisi view (DataTables pageLength).
     */
    public function getJadwalBerlangsungHariIni()
    {
        $today = now()->toDateString();

        return JadwalModel::with(['ruangans', 'user'])
            ->whereDate('jadwal_tgl', $today)
            ->where('jadwal_status', 'Berlangsung')
            ->orderBy('jadwal_jam_mulai')
            ->get();
    }

    /**
     * Antrian approval yang menjadi tanggung jawab verifikator login (FR-8.3).
     */
    public function getAntrianApprovalFor(int $userId)
    {
        $jabatanIds = JabatanApprovalModel::where('user_id', $userId)->pluck('jabatan_id');
        if ($jabatanIds->isEmpty()) {
            return collect();
        }

        return PengajuanApprovalModel::with(['pengajuan.user', 'pengajuan.organisasi'])
            ->whereIn('jabatan_id', $jabatanIds)
            ->where('status_approval', 'Menunggu')
            ->whereNotNull('batas_waktu')
            ->orderBy('batas_waktu')
            ->get();
    }

    /**
     * Early warning untuk Admin: pengajuan yang mendekati batas_waktu auto reject (FR-8.4).
     * Ambang batas 12 jam tersisa, sesuai contoh di tasks_v2.0.md T5.3.
     */
    public function getEarlyWarningPengajuan()
    {
        return PengajuanApprovalModel::with(['pengajuan.user', 'jabatanApproval.user'])
            ->where('status_approval', 'Menunggu')
            ->whereNotNull('batas_waktu')
            ->whereBetween('batas_waktu', [now(), now()->addHours(12)])
            ->orderBy('batas_waktu')
            ->get();
    }

    /**
     * Resolve the welcome view name based on the authenticated user's role (level_kode,
     * bukan level_id — lebih tahan terhadap perubahan urutan/isi m_level di masa depan).
     * Role di luar ROLES_WITH_DASHBOARD diarahkan ke dashboard "tanpa permission" —
     * lihat resources/views/dashboard/no-permission.blade.php.
     */
    public function resolveWelcomeViewName(?object $user): string
    {
        // VRF was removed as a standalone level — accounts with VRF level_kode (legacy)
        // or any other unrecognized role fall through to the no-permission dashboard.
        return match ($user?->getRole()) {
            'ADM' => 'admin.welcome',
            'DSN' => 'dosen.welcome',
            'TDK' => 'tendik.welcome',
            'MHS' => 'mahasiswa.welcome',
            default => 'dashboard.no-permission',
        };
    }

    /**
     * Get dashboard statistics for a specific month
     *
     * @param int $month
     * @param int $year
     * @return array
     */
    public function getDashboardStatsForMonth(int $month, int $year): array
    {
        $topRuanganFilter = function ($q) use ($month, $year) {
            $q->whereMonth('jadwal_tgl', $month)->whereYear('jadwal_tgl', $year);
        };
        $topRuangan = RuanganModel::withCount(['jadwal' => $topRuanganFilter])
            ->whereHas('jadwal', $topRuanganFilter)
            ->orderByDesc('jadwal_count')
            ->limit(5)
            ->get();

        $distribusiPeminjam = [
            'admin' => 0,
            'dosen' => 0,
            'tendik' => 0,
            'mahasiswa' => 0
        ];

        $peminjamanBulanIni = DB::table('t_jadwal')
            ->join('m_user', 't_jadwal.user_id', '=', 'm_user.user_id')
            ->join('m_level', 'm_user.level_id', '=', 'm_level.level_id')
            ->whereMonth('jadwal_tgl', $month)
            ->whereYear('jadwal_tgl', $year)
            ->select('m_level.level_kode', DB::raw('count(*) as total'))
            ->groupBy('m_level.level_kode')
            ->get();

        foreach($peminjamanBulanIni as $item){
            if($item->level_kode == RoleConstants::ADMIN) $distribusiPeminjam['admin'] = $item->total;
            if($item->level_kode == RoleConstants::DOSEN) $distribusiPeminjam['dosen'] = $item->total;
            if($item->level_kode == RoleConstants::TENDIK) $distribusiPeminjam['tendik'] = $item->total;
            if($item->level_kode == RoleConstants::MAHASISWA) $distribusiPeminjam['mahasiswa'] = $item->total;
        }

        return [
            'topRuangan' => $topRuangan,
            'distribusiPeminjam' => $distribusiPeminjam
        ];
    }
}
