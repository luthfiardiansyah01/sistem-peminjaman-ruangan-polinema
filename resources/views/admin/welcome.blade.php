@extends('layouts.template')

@section('content')

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- PERBAIKAN Q1: Gunakan service yang sama dengan tabel agar jumlahnya 100% presisi --}}
@php
    $penyelesaianMenanti = app(\App\Services\PengajuanService::class)->indexData(Auth::user())['pengajuans']->count();
@endphp

@if($penyelesaianMenanti > 0)
<div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    <h5><i class="icon fas fa-exclamation-triangle"></i> Penyelesaian Peminjaman Menanti!</h5>
    Terdapat <strong>{{ $penyelesaianMenanti }}</strong> peminjaman ruangan yang telah selesai dan menunggu verifikasi dokumentasi dari Anda.
    <a href="{{ url('/penyelesaian') }}" class="alert-link">Lihat daftar penyelesaian</a>.
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- Stat Cards --}}
<div class="row mb-4">
    <div class="col-lg-3 col-6">
        <div class="card stat-card bg-primary-custom" style="background-color: #3b82f6;">
            <div class="card-body">
                <h3>{{ $totalRuangan }}</h3>
                <p>Total Ruangan</p>
                <div class="icon">
                    <i class="fas fa-door-closed"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
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
    <div class="col-lg-3 col-6">
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
    <div class="col-lg-3 col-6">
        <div class="card stat-card bg-danger-custom" style="background-color: #f43f5e;">
            <div class="card-body">
                <h3>{{ $totalUser }}</h3>
                <p>Total Pengguna Aktif</p>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom Stat Card Styles */
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
    .stat-card .card-body {
        padding: 20px;
        z-index: 2;
        position: relative;
    }
    .stat-card h3 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    .stat-card p {
        font-size: 1.1rem;
        font-weight: 500;
        margin: 0;
    }
    .stat-card .icon {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        right: 15px;
        opacity: 0.2;
        transition: all 0.3s;
    }
    .stat-card .icon i {
        font-size: 5rem;
    }
    .stat-card:hover .icon {
        transform: translateY(-50%) scale(1.1); /* Keep centered vertically */
        opacity: 0.3;
    }
</style>

{{-- Early Warning: Pengajuan mendekati batas waktu auto reject (FR-8.4) --}}
@if($earlyWarning->count() > 0)
<div class="card card-outline card-warning mb-4">
    <div class="card-header">
        <h3 class="card-title" style="font-weight: bold;"><i class="fas fa-exclamation-triangle text-warning"></i> Early Warning: Mendekati Batas Waktu Auto Reject</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead style="background-color: #e3e3e3;">
                <tr>
                    <th>Kegiatan</th>
                    <th>Verifikator Tertunda</th>
                    <th>Batas Waktu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($earlyWarning as $w)
                <tr>
                    <td>{{ Str::limit($w->pengajuan->pengajuan_nama, 30, '...') }}</td>
                    <td>{{ $w->jabatanApproval->posisi_approval }} ({{ optional($w->jabatanApproval->user)->getDisplayName() }})</td>
                    <td class="text-danger">{{ $w->batas_waktu?->translatedFormat('d F Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Chart Layout: Side by Side --}}
<div class="row">
    {{-- Top 5 Ruangan --}}
    <div class="col-md-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title" style="font-weight: bold;">Top 5 Ruangan Terfavorit</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if($topRuangan->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-chart-bar fa-2x mb-2"></i>
                        <p class="mb-0">Belum ada data peminjaman ruangan bulan ini.</p>
                    </div>
                @else
                    <div class="chart">
                        <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Distribusi Status Peminjam --}}
    <div class="col-md-6">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title" style="font-weight: bold;">Distribusi Status Peminjam</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if(array_sum($distribusiPeminjam) === 0)
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-chart-pie fa-2x mb-2"></i>
                        <p class="mb-0">Belum ada pengguna peminjam terdaftar.</p>
                    </div>
                @else
                    <canvas id="pieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Tren Peminjaman Row --}}
<div class="row">
    <div class="col-md-12">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title" style="font-weight: bold;">Tren Peminjaman (6 Bulan)</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="lineChart" style="min-height: 250px; height: 250px; max-height: 350px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!--div class="text-center pb-5 pt-3">
    <button onclick="showUploadModal()" class="btn btn-import btn-sm shadow-sm">
        <i class="fas fa-upload"></i> Unggah Formulir
    </button>
</div>

{{-- Modal Upload Formulir --}}
<div class="modal fade" id="modalUploadFormulir" tabindex="-1" role="dialog" aria-labelledby="modalUploadTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUploadTitle" style="font-weight: bold;">Unggah Formulir</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-upload-formulir" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Pilih File PDF</label>
                        <input type="file" name="file_formulir" class="form-control" style="padding: 0px 0px 0px 0px; height: 32px;" required accept=".pdf">
                        <small class="text-muted">Maksimal 2MB. File baru akan menggantikan file lama.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal</button>
                    <button type="submit" class="btn btn-import">
                        <i class="fas fa-upload"></i> Unggah</button>
                </div>
            </form>
        </div>
    </div>
</div-->

@endsection

@push('css')
<style>
    /* Custom CSS for Import Button */
    .btn-import {
        background-color: #ff851b;
        color: #fff;
    }
    .btn-import:hover {
        background-color: #e07415; /* Darker shade of orange */
        color: #fff;
    }
    .small-box:hover {
        transform: scale(1.02);
    }
</style>
@endpush

@push('js')
<script>
    //PIE CHART
    // Canvas hanya dirender kalau ada data (lihat kondisi empty-state di blade
    // sebelum canvas ini) — dicek dulu supaya tidak error "Cannot read
    // properties of null" saat kosong.
    var pieCanvas = document.getElementById('pieChart');
    if (pieCanvas) {
    var ctx = pieCanvas.getContext('2d');
    const dataDistribusiPeminjam = {
        labels: ['Admin', 'Dosen', 'Tendik', 'Mahasiswa'],
        datasets: [{
            data: [
                {{ $distribusiPeminjam['admin'] }}, 
                {{ $distribusiPeminjam['dosen'] }}, 
                {{ $distribusiPeminjam['tendik'] }}, 
                {{ $distribusiPeminjam['mahasiswa'] }}
            ],
            backgroundColor: ['#fa8607', '#219ebc', '#f56954', '#ffb703'],
        }]
    };
    var myPieChart = new Chart(ctx, {
        type: 'pie',
        data: dataDistribusiPeminjam,
        options: {
            responsive: true,
            legend: {
                position: 'top',
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });
    }

    //BAR CHART
    var barCanvas = document.getElementById('barChart');
    if (barCanvas) {
    var ctx2 = barCanvas.getContext('2d');
    const gradasiHijau = [
    '#1B8A2C', 
    '#27C840', 
    '#2EDB4D', 
    '#52D669', 
    '#81E492', 
    ];
    const dataTopRuangan = {
        labels: [
            @foreach ($topRuangan as $item) 
                "{{ $item->ruangan_nama }}", 
            @endforeach
        ],
        datasets: [{
            label: 'Total Peminjaman',
            data: [
                @foreach ($topRuangan as $item) 
                    {{ $item->jadwal_count }}, 
                @endforeach
            ],
            backgroundColor: [
                @foreach ($topRuangan as $index => $item)
                    gradasiHijau[{{ $index }} % gradasiHijau.length],
                @endforeach
            ],
            borderWidth: 1
        }]
    };

    var myBarChart = new Chart(ctx2, {
        type: 'horizontalBar',
        data: dataTopRuangan,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                display: false,
                position: 'top',
            },
            scales: {
                xAxes: [{ 
                    display: true,
                    ticks: {
                        beginAtZero: true,
                        stepSize: 1,
                        callback: function(value) {
                            if (value % 1 === 0) { return value; }
                        }
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'Jumlah',
                        fontStyle: 'bold'
                    }
                }],
                yAxes: [{ 
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Ruangan',
                        fontStyle: 'bold'
                    }
                }]
            }
        }
    });
    }

    // LINE CHART
    var ctx3 = document.getElementById('lineChart').getContext('2d');
    const dataTrenPeminjaman = {
        labels: [
            @foreach ($trenPeminjaman as $item)
                /* Mengubah yyyy-mm menjadi Nama Bulan Tahun (id) */
                "{{ \Carbon\Carbon::parse($item->bulan)->locale('id')->translatedFormat('F Y') }}",
            @endforeach
        ],
        datasets: [{
            label: 'Total Peminjaman',
            data: [
                @foreach ($trenPeminjaman as $item) 
                    {{ $item->total }}, 
                @endforeach
            ],
            backgroundColor: '#00c0ef',
            borderColor: '#00c0ef',
            borderWidth: 2,
            pointRadius: 5,
            fill: false,
        }]
    };

    var myLineChart = new Chart(ctx3, {
        type: 'line',
        data: dataTrenPeminjaman,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                display: true,
                position: 'top',
            },
            scales: {
                /* Untuk Chart.js Versi 2 (AdminLTE 3) */
                xAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Bulan',
                        fontStyle: 'bold',
                    }
                }],
                yAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Jumlah',
                        fontStyle: 'bold',
                    },
                    ticks: {
                        beginAtZero: true,
                        stepSize: 2,
                        callback: function(value) {
                            if (value % 2 === 0) { return value; }
                        }
                    }
                }]
            }
        }
    });
    
    function showUploadModal() {
        $('#modalUploadFormulir').modal('show');
    }

    $(document).ready(function() {
        $('#form-upload-formulir').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            
            $.ajax({
                url: "{{ route('formulir.upload') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if(response.status){
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 2000
                    }).then((result) => {
                        window.location.reload();
                    });
                    $('#modalUploadFormulir').modal('hide');
                }else{
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message
                    });
                }
            },
            error: function(xhr){
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan sistem'
                });
            }
            });
        });
    });
</script>
@endpush