<?php

namespace App\Services;

use App\Models\JadwalModel;
use App\Models\PengajuanModel;
use App\Models\RuanganModel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class RuanganService
{
    // Status jadwal yang dianggap masih "menempati" ruangan (FR terkait status per-tanggal,
    // poin 2). Hanya 'Selesai' yang membebaskan ruangan — konsisten dengan
    // JadwalService::updateStatus() yang mengembalikan ruangan_status ke 'Tersedia'
    // persis saat transisi ke 'Selesai'.
    protected const JADWAL_STATUS_MENEMPATI = ['Akan Datang', 'Berlangsung', 'Ditinjau', 'Dokumentasi Tidak Sesuai'];

    public function indexData(): array
    {
        return [
            'breadcrumb' => (object) ['title' => 'Daftar Ruangan', 'list' => ['Home', 'Daftar Ruangan']],
            'page' => (object) ['title' => 'Daftar ruangan yang terdaftar dalam sistem'],
            'activeMenu' => 'ruangan',
            'ruangan' => RuanganModel::all(),
        ];
    }

    public function dataTable(string $role)
    {
        $query = RuanganModel::select('ruangan_id', 'ruangan_kode', 'ruangan_nama', 'ruangan_fasilitas', 'ruangan_kuota', 'ruangan_status', 'ruangan_kategori');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('aksi', fn ($ruangan) => $this->actionButtons($ruangan, $role))
            ->rawColumns(['aksi'])
            ->make(true);
    }

    /**
     * Req 2: create() sekarang menerima array file (atau single file) untuk multi-foto.
     * Disimpan sebagai JSON array ke kolom ruangan_foto.
     *
     * @param array                    $data
     * @param UploadedFile|UploadedFile[]|null $fotos
     */
    public function create(array $data, $fotos = null): array
    {
        $fotosArr = $fotos ? (is_array($fotos) ? $fotos : [$fotos]) : [];
        // Validasi field non-foto; file sudah divalidasi di level controller jika perlu
        $validator = Validator::make($data, $this->rules());
        if ($validator->fails()) {
            return ['status' => false, 'message' => 'Validasi gagal!', 'errors' => $validator->errors(), 'msgField' => $validator->errors()];
        }

        try {
            $payload = $this->payload($data);
            $stored = [];
            foreach ($fotosArr as $f) {
                if ($f && $f->isValid()) {
                    $stored[] = $f->store('ruangan', 'public');
                }
            }
            $payload['ruangan_foto'] = !empty($stored) ? json_encode($stored) : null;

            $ruangan = RuanganModel::create($payload);
            return ['status' => true, 'message' => 'Data ruangan berhasil ditambahkan!', 'ruangan_id' => $ruangan->ruangan_id];
        } catch (QueryException $e) {
            return $this->error('Penyimpanan data gagal. Error Database: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Req 2: update() sekarang mendukung penambahan foto baru dan penghapusan foto tertentu.
     *
     * @param array            $data          Request fields (tanpa file)
     * @param UploadedFile[]   $newFotos      File-file baru yang di-upload
     * @param string[]         $deletedFotos  Path foto yang ingin dihapus
     */
    public function update(int $id, array $data, array $newFotos = [], array $deletedFotos = []): array
    {
        $ruangan = RuanganModel::find($id);
        if (!$ruangan) {
            return $this->error('Data ruangan tidak ditemukan!', 404);
        }

        $validator = Validator::make($data, $this->rules($id));
        if ($validator->fails()) {
            return ['status' => false, 'message' => 'Validasi Gagal', 'errors' => $validator->errors(), 'msgField' => $validator->errors()];
        }

        $payload = $this->payload($data);

        // Req 2: Kelola array foto — hapus yang dipilih, simpan yang baru
        $existing = $ruangan->fotos; // getFotosAttribute()
        if (!empty($deletedFotos)) {
            foreach ($deletedFotos as $path) {
                Storage::disk('public')->delete($path);
            }
            $existing = array_values(array_filter($existing, fn ($f) => !in_array($f, $deletedFotos)));
        }
        foreach ($newFotos as $f) {
            if ($f && $f->isValid()) {
                $existing[] = $f->store('ruangan', 'public');
            }
        }
        $payload['ruangan_foto'] = !empty($existing) ? json_encode(array_values($existing)) : null;

        $ruangan->update($payload);
        return ['status' => true, 'message' => 'Data ruangan berhasil diupdate!', 'ruangan_id' => $ruangan->ruangan_id];
    }

    public function delete(int $id): array
    {
        $ruangan = RuanganModel::find($id);
        if (!$ruangan) {
            return $this->error('Data ruangan tidak ditemukan!', 404);
        }

        try {
            $ruangan->delete();
            return ['status' => true, 'message' => 'Data ruangan berhasil dihapus!'];
        } catch (QueryException $e) {
            return $this->error('Data gagal dihapus karena masih digunakan di tabel lain!', 500);
        }
    }

    public function find(int $id): ?RuanganModel
    {
        return RuanganModel::find($id);
    }

    /**
     * Status ketersediaan SATU ruangan untuk SATU tanggal (poin 2 — status per-tanggal).
     * Precedence: override manual (ruangan_status='Tidak Tersedia') > jadwal aktif > pengajuan
     * yang masih menunggu approval > Tersedia. Granularitas per-tanggal, bukan per-jam.
     */
    public function availabilityStatusForDate(RuanganModel $ruangan, string $tanggal): string
    {
        if ($ruangan->ruangan_status === 'Tidak Tersedia') {
            return 'Tidak Tersedia';
        }

        $dipakaiJadwal = $ruangan->jadwal()
            ->where('jadwal_tgl', $tanggal)
            ->whereIn('jadwal_status', self::JADWAL_STATUS_MENEMPATI)
            ->exists();
        if ($dipakaiJadwal) {
            return 'Tidak Tersedia';
        }

        $adaPengajuan = $ruangan->pengajuans()
            ->where('pengajuan_tgl', $tanggal)
            ->where('pengajuan_status', 'Diajukan')
            ->exists();
        if ($adaPengajuan) {
            return 'Diajukan';
        }

        return 'Tersedia';
    }

    /**
     * Status ketersediaan SEMUA ruangan untuk SATU tanggal (dipakai form pengajuan).
     * 2 query bulk (bukan N+1 per ruangan) — kumpulkan dulu ruangan_id yang terpakai
     * jadwal & yang punya pengajuan menunggu, baru dipetakan ke seluruh ruangan.
     *
     * @return array<int, array{ruangan_id:int, ruangan_nama:string, status:string}>
     */
    public function availabilityForAllRoomsOnDate(string $tanggal): array
    {
        $ruanganDipakaiJadwal = JadwalModel::where('jadwal_tgl', $tanggal)
            ->whereIn('jadwal_status', self::JADWAL_STATUS_MENEMPATI)
            ->with('ruangans:ruangan_id')
            ->get()
            ->pluck('ruangans')
            ->flatten()
            ->pluck('ruangan_id')
            ->unique();

        $ruanganAdaPengajuan = PengajuanModel::where('pengajuan_tgl', $tanggal)
            ->where('pengajuan_status', 'Diajukan')
            ->with('ruangans:ruangan_id')
            ->get()
            ->pluck('ruangans')
            ->flatten()
            ->pluck('ruangan_id')
            ->unique();

        return RuanganModel::all()->map(function (RuanganModel $ruangan) use ($ruanganDipakaiJadwal, $ruanganAdaPengajuan) {
            $status = 'Tersedia';
            if ($ruangan->ruangan_status === 'Tidak Tersedia' || $ruanganDipakaiJadwal->contains($ruangan->ruangan_id)) {
                $status = 'Tidak Tersedia';
            } elseif ($ruanganAdaPengajuan->contains($ruangan->ruangan_id)) {
                $status = 'Diajukan';
            }

            return ['ruangan_id' => $ruangan->ruangan_id, 'ruangan_nama' => $ruangan->ruangan_nama, 'status' => $status];
        })->values()->all();
    }

    /**
     * Status ketersediaan SATU ruangan untuk SELURUH hari dalam satu bulan (kalender ruangan).
     * 2 query bulk (bukan query per hari) — kumpulkan tanggal yang dipakai jadwal & yang
     * punya pengajuan menunggu dalam bulan itu, baru dipetakan ke tiap tanggal.
     *
     * @return array<string, string> tanggal (Y-m-d) => status
     */
    public function monthlyAvailability(RuanganModel $ruangan, int $year, int $month): array
    {
        $cacheKey = sprintf('ruangan:%d:kalender:%04d-%02d', $ruangan->ruangan_id, $year, $month);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($ruangan, $year, $month) {
            $awalBulan = sprintf('%04d-%02d-01', $year, $month);
            $akhirBulan = date('Y-m-t', strtotime($awalBulan));

            $tanggalDipakaiJadwal = $ruangan->jadwal()
                ->whereBetween('jadwal_tgl', [$awalBulan, $akhirBulan])
                ->whereIn('jadwal_status', self::JADWAL_STATUS_MENEMPATI)
                ->pluck('jadwal_tgl')
                ->map(fn ($tgl) => date('Y-m-d', strtotime($tgl)))
                ->unique();

            $tanggalAdaPengajuan = $ruangan->pengajuans()
                ->whereBetween('pengajuan_tgl', [$awalBulan, $akhirBulan])
                ->where('pengajuan_status', 'Diajukan')
                ->pluck('pengajuan_tgl')
                ->map(fn ($tgl) => date('Y-m-d', strtotime($tgl)))
                ->unique();

            $hasil = [];
            $jumlahHari = (int) date('t', strtotime($awalBulan));
            for ($hari = 1; $hari <= $jumlahHari; $hari++) {
                $tanggal = sprintf('%04d-%02d-%02d', $year, $month, $hari);

                if ($ruangan->ruangan_status === 'Tidak Tersedia' || $tanggalDipakaiJadwal->contains($tanggal)) {
                    $hasil[$tanggal] = 'Tidak Tersedia';
                } elseif ($tanggalAdaPengajuan->contains($tanggal)) {
                    $hasil[$tanggal] = 'Diajukan';
                } else {
                    $hasil[$tanggal] = 'Tersedia';
                }
            }

            return $hasil;
        });
    }

    protected function actionButtons($ruangan, string $role): string
    {
        $btn = '<button onclick="modalAction(\'' . url('/ruangan/' . $ruangan->ruangan_id . '/show_ajax') . '\')" class="btn btn-outline-info btn-sm" title="Detail"><i class="fas fa-eye"></i></button>';
        $btn .= ' <button onclick="modalAction(\'' . url('/ruangan/' . $ruangan->ruangan_id . '/kalender_ajax') . '\')" class="btn btn-outline-primary btn-sm" title="Lihat Kalender"><i class="fas fa-calendar-alt"></i></button>';
        if ($role !== 'ADM') {
            return $btn;
        }

        $btn .= ' <button onclick="modalAction(\'' . url('/ruangan/' . $ruangan->ruangan_id . '/edit_ajax') . '\')" class="btn btn-outline-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></button>';
        return $btn . ' <button onclick="modalAction(\'' . url('/ruangan/' . $ruangan->ruangan_id . '/confirm_ajax') . '\')" class="btn btn-outline-danger btn-sm" title="Hapus"><i class="fas fa-trash"></i></button>';
    }

    protected function rules(?int $id = null): array
    {
        // Catatan: ruangan_foto TIDAK divalidasi di sini karena foto diterima sebagai
        // parameter terpisah ($fotos / $newFotos) langsung dari controller, bukan dari
        // array $data. Validasi file (image/mimes/max) dilakukan di level controller
        // jika diperlukan, atau bisa ditambahkan di sini via request()->file() jika perlu.
        return [
            'ruangan_kode'     => 'required|string|max:10|unique:m_ruangan,ruangan_kode,' . $id . ',ruangan_id',
            'ruangan_nama'     => 'required|string|max:100',
            'ruangan_fasilitas'=> 'nullable|string',
            'ruangan_kuota'    => 'required|integer|min:1',
            'ruangan_status'   => 'nullable|in:Tersedia,Diajukan,Tidak Tersedia',
            'ruangan_kategori' => 'required|in:Jurusan,Umum',
        ];
    }

    protected function payload(array $data): array
    {
        return [
            'ruangan_kode' => $data['ruangan_kode'],
            'ruangan_nama' => $data['ruangan_nama'],
            'ruangan_fasilitas' => $data['ruangan_fasilitas'] ?? '',
            'ruangan_kuota' => $data['ruangan_kuota'],
            'ruangan_status' => $data['ruangan_status'] ?? 'Tersedia',
            'ruangan_kategori' => $data['ruangan_kategori'],
        ];
    }

    protected function error(string $message, int $status): array
    {
        return ['status' => false, 'message' => $message, 'http_status' => $status];
    }
}
