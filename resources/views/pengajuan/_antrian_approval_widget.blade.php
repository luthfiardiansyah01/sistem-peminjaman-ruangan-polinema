{{-- Widget "Antrian Approval" (FR-8.3) — ditampilkan di welcome dashboard dosen/mahasiswa
     manapun yang memegang jabatan approval (lihat DashboardService::getWelcomeDashboardData()).
     Sebelumnya halaman welcome tersendiri untuk level VRF (verifikator/welcome.blade.php),
     sekarang jadi widget karena pemegang jabatan approval bisa jadi dosen/mahasiswa biasa. --}}
@if(isset($antrianApproval))
<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="font-weight: bold;">Antrian Approval ({{ $antrianApproval->count() }})</h3>
    </div>
    <div class="card-body">
        <table class="table table-sm">
            <thead style="background-color: #e3e3e3;">
                <tr>
                    <th>No.</th>
                    <th>Nama Kegiatan</th>
                    <th>Pengaju</th>
                    <th>Organisasi</th>
                    <th>Batas Waktu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($antrianApproval as $key => $a)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ Str::limit($a->pengajuan->pengajuan_nama, 30, '...') }}</td>
                    <td>{{ $a->pengajuan->user->getDisplayName() }}</td>
                    <td>{{ optional($a->pengajuan->organisasi)->organisasi_nama ?? '-' }}</td>
                    <td>{{ $a->batas_waktu?->translatedFormat('d F Y H:i') }}</td>
                    <td>
                        <a href="{{ url('/pengajuan/approval/antrian_ajax') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-gavel"></i> Proses
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center">Tidak ada pengajuan yang menunggu persetujuan Anda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
