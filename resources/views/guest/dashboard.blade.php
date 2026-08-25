<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Statistik Publik - Sistem Peminjaman Ruangan JTI</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">

    <style>
        body {
            background-color: #f4f6f9;
        }
        .guest-navbar {
            background-color: #023047;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #fff;
        }
        .guest-navbar .brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .guest-navbar .brand img {
            height: 36px;
            margin-right: 10px;
            border-radius: 50%;
            background: #fff;
            padding: 2px;
        }
        .guest-navbar a.btn-login {
            background-color: #ffc107;
            color: #212529;
            font-weight: bold;
            border-radius: 50px;
            padding: 6px 20px;
        }
        .guest-navbar a.btn-login:hover {
            background-color: #e0a800;
            color: #212529;
            text-decoration: none;
        }
        .guest-content {
            max-width: 1140px;
            margin: 0 auto;
            padding: 30px 15px 60px;
        }
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
        }
        .stat-card .icon i {
            font-size: 5rem;
        }
    </style>
</head>
<body>

    <nav class="guest-navbar">
        <div class="brand">
            <img src="{{ url('images/Logo JTI Polinema.png') }}" alt="Logo JTI">
            Sistem Peminjaman Ruangan JTI
        </div>
        <a href="{{ route('login') }}" class="btn-login">
            <i class="fas fa-sign-in-alt mr-1"></i> Login
        </a>
    </nav>

    <div class="guest-content">
        <h3 class="font-weight-bold mb-4">Statistik Publik</h3>

        {{-- Stat Cards --}}
        <div class="row mb-4">
            <div class="col-lg-4 col-6">
                <div class="card stat-card" style="background-color: #3b82f6;">
                    <div class="card-body">
                        <h3>{{ $totalRuangan }}</h3>
                        <p>Total Ruangan</p>
                        <div class="icon"><i class="fas fa-door-closed"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="card stat-card" style="background-color: #a855f7;">
                    <div class="card-body">
                        <h3>{{ $totalRuanganKosong }}</h3>
                        <p>Total Ruangan Kosong</p>
                        <div class="icon"><i class="fas fa-door-open"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="card stat-card" style="background-color: #fbbf24; color:#000;">
                    <div class="card-body">
                        <h3>{{ $jadwalHariIni }}</h3>
                        <p>Jadwal Hari Ini</p>
                        <div class="icon"><i class="far fa-clock"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title" style="font-weight: bold;">Top 5 Ruangan Terfavorit</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title" style="font-weight: bold;">Distribusi Peminjam</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="pieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title" style="font-weight: bold;">Tren Peminjaman (6 Bulan)</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="lineChart" style="min-height: 250px; height: 250px; max-height: 350px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/chart.js/Chart.min.js') }}"></script>

    <script>
        // PIE CHART — Distribusi Peminjam
        var ctx = document.getElementById('pieChart').getContext('2d');
        var myPieChart = new Chart(ctx, {
            type: 'pie',
            data: {
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
            },
            options: {
                responsive: true,
                legend: { position: 'top' },
                animation: { animateScale: true, animateRotate: true }
            }
        });

        // BAR CHART — Top 5 Ruangan Terfavorit
        var ctx2 = document.getElementById('barChart').getContext('2d');
        const gradasiHijau = ['#1B8A2C', '#27C840', '#2EDB4D', '#52D669', '#81E492'];
        var myBarChart = new Chart(ctx2, {
            type: 'horizontalBar',
            data: {
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
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false, position: 'top' },
                scales: {
                    xAxes: [{
                        display: true,
                        ticks: { beginAtZero: true, stepSize: 1, callback: function (value) { if (value % 1 === 0) { return value; } } },
                        scaleLabel: { display: true, labelString: 'Jumlah', fontStyle: 'bold' }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: { display: true, labelString: 'Ruangan', fontStyle: 'bold' }
                    }]
                }
            }
        });

        // LINE CHART — Tren Peminjaman
        var ctx3 = document.getElementById('lineChart').getContext('2d');
        var myLineChart = new Chart(ctx3, {
            type: 'line',
            data: {
                labels: [
                    @foreach ($trenPeminjaman as $item)
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
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: true, position: 'top' },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: { display: true, labelString: 'Bulan', fontStyle: 'bold' }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: { display: true, labelString: 'Jumlah', fontStyle: 'bold' },
                        ticks: { beginAtZero: true, stepSize: 1, callback: function (value) { if (value % 1 === 0) { return value; } } }
                    }]
                }
            }
        });
    </script>
</body>
</html>
