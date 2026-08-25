@extends('layouts.template')

@section('content')

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="icon fas fa-ban"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="icon fas fa-check"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="row mb-4">
    <div class="col-lg-4 col-6">
        <div class="card stat-card bg-primary-custom" style="background-color: #3b82f6;">
            <div class="card-body">
                <h3>{{ $pengajuanMenungguVerifikasi->count() }}</h3>
                <p>Menunggu Verifikasi</p>
                <div class="icon">
                    <i class="fas fa-gavel"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="card stat-card bg-warning-custom" style="background-color: #fbbf24; color: #000;">
            <div class="card-body">
                <h3>{{ $jadwalHariIni }}</h3>
                <p>Jadwal Hari Ini</p>
                <div class="icon">
                    <i class="far fa-clock"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="card stat-card bg-purple-custom" style="background-color: #a855f7;">
            <div class="card-body">
                <h3>{{ $totalRuanganKosong }}</h3>
                <p>Total Ruangan Kosong</p>
                <div class="icon">
                    <i class="fas fa-door-open"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .stat-card {
        border: none;
        border-radius: 10px;
        color: #fff;
        height: 140px;
        position: relative;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .stat-card .card-body { padding: 20px; z-index: 2; position: relative; }
    .stat-card h3 { font-size: 3rem; font-weight: 700; margin-bottom: 5px; }
    .stat-card p { font-size: 1.1rem; font-weight: 500; margin: 0; }
    .stat-card .icon { position: absolute; top: 50%; transform: translateY(-50%); right: 15px; opacity: 0.2; }
    .stat-card .icon i { font-size: 5rem; }
</style>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title" style="font-weight: bold;">Pengajuan Menunggu Verifikasi</h3>
        <div class="card-tools">
            <a href="{{ url('/pengajuan') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-list"></i> Lihat Semua Pengajuan
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>Kegiatan</th>
                    <th>Pengaju</th>
                    <th>Ruangan</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuanMenungguVerifikasi as $p)
                <tr>
                    <td>{{ Str::limit($p->pengajuan_nama, 30, '...') }}</td>
                    <td>{{ $p->user->getDisplayName() ?? '-' }}</td>
                    <td>{{ $p->ruangans->pluck('ruangan_nama')->implode(', ') }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->pengajuan_tgl)->locale('id')->translatedFormat('d M Y') }}</td>
                    <td>
                        <button onclick="modalAction('{{ url('/pengajuan/' . $p->pengajuan_id . '/verifikasi_ajax') }}')" class="btn btn-outline-primary btn-sm" title="Verifikasi">
                            <i class="fas fa-gavel"></i> Verifikasi
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center">Tidak ada pengajuan yang menunggu verifikasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalPengajuan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" id="modalSize">
        <div class="modal-content"></div>
    </div>
</div>

@endsection

@push('js')
<script>
    function modalAction(url) {
        $('#modalSize').removeClass('modal-xl modal-lg modal-md modal-sm').addClass('modal-lg');
        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                $('#modalPengajuan .modal-content').html(data);
                $('#modalPengajuan').modal('show');
            },
            error: function(xhr) {
                alert('Gagal memuat modal. Cek console log.');
                console.error('AJAX Error:', xhr.responseText);
            }
        });
    }
</script>
@endpush
