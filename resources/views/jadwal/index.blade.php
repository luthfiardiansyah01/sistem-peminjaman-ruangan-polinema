@extends('layouts.template')

@section('content')

    <div class="card card-primary card-outline card-outline-tabs">

        {{-- Req 5: Tab dan filter ditampilkan untuk semua role (hapus guard @if ADM) --}}
        <div class="card-header p-0 pt-1 border-bottom-0">

            <ul class="nav nav-tabs" id="jadwalTab" role="tablist">

                <li class="nav-item">

                    <a class="nav-link active" id="tab-daftar" data-toggle="pill" href="#daftarJadwal">

                        <i class="fas fa-calendar-alt"></i>

                        Daftar Jadwal

                    </a>

                </li>

                <li class="nav-item">

                    <a class="nav-link" id="tab-riwayat" data-toggle="pill" href="#riwayatJadwal">

                        <i class="fas fa-history"></i>

                        Riwayat Jadwal

                    </a>

                </li>

            </ul>

            {{-- FILTER --}}
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">

                    <div class="d-flex align-items-center">
                        <label class="mr-2 mb-0">Ruangan:</label>

                        <select id="filter_ruangan" class="form-control form-control-sm" style="width: 150px;">

                            <option value="">Semua Ruangan</option>

                            @foreach($ruanganList as $ruangan)
                                <option value="{{ $ruangan->ruangan_id }}">
                                    {{ $ruangan->ruangan_nama }}
                                </option>
                            @endforeach

                        </select>

                    </div>

                    <div class="d-flex align-items-center">

                        <label class="ml-3 mr-2 mb-0" style="white-space: nowrap;">Tahun Ajaran:</label>

                        <select id="filter_tahun_ajaran" class="form-control form-control-sm">

                            <option value="">Semua</option>

                            @foreach($tahunAjaranList as $tahun)

                                <option value="{{ $tahun }}">
                                    {{ $tahun }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="d-flex align-items-center">

                        <label class="ml-3 mr-2 mb-0">Semester:</label>

                        <select id="filter_semester" class="form-control form-control-sm">

                            <option value="">Semua</option>
                            <option>Ganjil</option>
                            <option>Genap</option>

                        </select>

                    </div>

                    <div class="d-flex align-items-center">
                        <label class="col-form-label mr-2 ml-3 mb-0">Cari:</label>
                        <input type="text" id="jadwal_search" class="form-control form-control-sm" placeholder="Cari berdasarkan kegiatan/acara" style="width: 220px;">
                        <button type="button" class="btn btn-primary btn-sm mr-2 mb-0">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                    <div class="d-flex align-items-center">

                        <label class="ml-3 mr-2 mb-0">Show</label>

                        <select id="data-table-length" class="form-control form-control-sm">

                            <option>10</option>
                            <option>25</option>
                            <option>50</option>
                            <option>100</option>

                        </select>
                        <label class="ml-2 mb-0">entries</label>
                    </div>

                </div>
            </div>

        </div>

        <div class="card-body">

            <div class="tab-content">

                <div class="tab-pane fade show active" id="daftarJadwal">

                    @include('jadwal.components.table_daftar', [
                        'showEdit' => false,
                        'showView' => true,
                    ])

                </div>

                <div class="tab-pane fade" id="riwayatJadwal">

                    @include('jadwal.components.table_riwayat', [
                        'showView' => true,
                    ])

                </div>

            </div>

        </div>

    </div>

    <div class="modal fade" id="modalJadwal" tabindex="-1" role="dialog" aria-labelledby="modalJadwalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" id="modalSize">
            <div class="modal-content">
                {{-- Konten modal akan diisi oleh AJAX --}}
            </div>
        </div>
    </div>

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .table {
            width: 100% !important;
        }

        .table th,
        .table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        #table_daftar,
        #table_riwayat {
            width: 100% !important;
            table-layout: fixed;
        }

        /* Khusus kolom Ruangan (kolom 2) & Kegiatan (kolom 6), dibungkus rapi jika panjang */
        #table_daftar td:nth-child(2), #table_daftar td:nth-child(6),
        #table_riwayat td:nth-child(2), #table_riwayat td:nth-child(6) {
            white-space: normal;
            word-break: normal;
        }

        .select2-container .select2-selection--single {
            height: calc(2.25rem + 2px) !important;
            padding: 0 0.75rem;
            display: flex;
            align-items: center;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: normal !important;
            padding-left: 0 !important;
            margin-top: 0 !important;
            color: #495057;
            font-family: inherit;
        }

        .select2-container .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            top: 0 !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .select2-container .select2-selection--multiple {
            min-height: calc(2.25rem + 2px) !important;
            padding: 0.375rem 0.75rem;
            line-height: 1.5;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }

        .select2-container .select2-search--inline .select2-search__field {
            font-family: inherit !important;
            margin-top: 0 !important;
            line-height: 1.5 !important;
            height: auto !important;
            vertical-align: middle !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #007bff;
            border: 1px solid #006fe6;
            color: #fff;
            padding: 2px 8px 2px 24px;
            margin: 2px 4px 2px 0;
            border-radius: 4px;
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: rgba(255, 255, 255, 0.8);
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-right: 1px solid rgba(255, 255, 255, 0.3);
            margin: 0;
            padding: 0;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            background-color: rgba(0, 0, 0, 0.1);
            color: #fff;
        }

        .status-badge{
            display:inline-block;
            /*min-width:140px;*/
            /*max-width: 120px;*/
            white-space:normal;
            text-align:center;
            padding:4px 8px;
            border-radius:8px;
            /*border: 2px solid;*/
            font-size:12px;
            font-weight:600;
            /*line-height: 0.9;*/
        }

        .status-proposed{
            background:#DAE8FC;
            color:#111827;
            border-color: #6C8EBF;
        }

        .status-upcoming{
            background: #b0e3e6;
            color:#111827;
            border-color: #0E8088;
        }

        .status-running{
            background: #b1d3f0ff;
            color:#111827;
            border-color: #10739E;
        }

        .status-review{
            background:#bac8d3;
            color:#000000;
            border-color: #23445D;
        }

        .status-invalid{
            /*background:#ffcccc;*/
            background:#FFC7CB;
            color:#271118;
            border-color: #36393D;
            max-width: 90px;
            line-height: 0.9;
        }

        .status-completed{
            background:#BDE5C8;
            color:#082614;
            border-color: #287442;
        }

        .nav-tabs .nav-link {
            font-weight: 600;
        }

        .nav-tabs .nav-link i {
            margin-right: 6px;
        }
    </style>
@endpush

@push('js')
    <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Deklarasi global variable untuk DataTable
        var dataJadwal = null;
        var dataRiwayat = null;

        // Data periode (m_periode) untuk filter Tahun Ajaran/Semester
        const periodeFilterData = @json($periodeList);

        function periodeRangeFor(tahunAjaran, semester) {
            if (!tahunAjaran && !semester) return null;
            const matches = periodeFilterData.filter(function (p) {
                return (!tahunAjaran || p.tahun_ajaran === tahunAjaran) && (!semester || p.semester === semester);
            });
            if (matches.length === 0) return { mulai: null, selesai: null };
            return {
                mulai: matches.reduce((min, p) => p.tanggal_mulai < min ? p.tanggal_mulai : min, matches[0].tanggal_mulai),
                selesai: matches.reduce((max, p) => p.tanggal_selesai > max ? p.tanggal_selesai : max, matches[0].tanggal_selesai),
            };
        }

        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (settings.sTableId !== 'table_daftar' && settings.sTableId !== 'table_riwayat') return true;

            const tahunAjaran = $('#filter_tahun_ajaran').val();
            const semester = $('#filter_semester').val();

            // REQ 3: Filter Semester — bandingkan langsung teks di kolom index 6 ("Semester").
            // Tidak bergantung pada periodeRangeFor() / data m_periode sehingga selalu
            // berfungsi meskipun tabel m_periode kosong.
            // Strip HTML tags dan normalisasi seluruh whitespace (termasuk newline/tab dari
            // indentasi Blade) ke satu spasi sebelum dibandingkan.
            if (semester) {
                const rowSemester = (data[6] || '')
                    .replace(/<[^>]*>/g, '')   // strip HTML tags
                    .replace(/\s+/g, ' ')      // normalkan whitespace (newline → spasi)
                    .trim();
                if (rowSemester !== semester) return false;
            }

            // Filter Tahun Ajaran: gunakan date-range dari m_periode.
            // Dipanggil SETELAH filter semester agar bail-out lebih cepat.
            if (tahunAjaran) {
                const range = periodeRangeFor(tahunAjaran, null);
                if (range === null) return true;
                if (range.mulai === null) return false;
                const tanggal = $(settings.aoData[dataIndex].nTr).find('td').eq(2).attr('data-order');
                if (!tanggal) return true;
                return tanggal >= range.mulai && tanggal <= range.selesai;
            }

            return true;
        });

        // Fungsi global untuk menampilkan modal
        function modalAction(url) {
            // Disable semua tombol pemicu modal selama request
            $('[onclick^="modalAction"]').prop('disabled', true);
            $('#modalSize').removeClass('modal-xl modal-lg modal-md modal-sm').addClass('modal-lg');
            $.ajax({
                url: url,
                type: 'GET',
                success: function (data) {
                    $('#modalJadwal .modal-content').html(data);
                    $('#modalJadwal').modal('show');
                },
                error: function (xhr) {
                    let msg = 'Gagal memuat modal.';
                    if (xhr.status === 403) {
                        msg = xhr.responseJSON?.message || 'Akses ditolak!';
                    } else if (xhr.status === 404) {
                        msg = 'Data tidak ditemukan.';
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error!', text: msg });
                    } else {
                        alert(msg);
                    }
                    console.error('AJAX Error:', xhr.responseText);
                },
                complete: function () {
                    $('[onclick^="modalAction"]').prop('disabled', false);
                }
            });
        }

        $(document).ready(function () {
            // Destroy existing DataTable instance jika sudah ada (cegah duplikasi)
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#table_daftar')) {
                $('#table_daftar').DataTable().destroy();
            }

            // Inisialisasi DataTables Client Side
            dataJadwal = $('#table_daftar').DataTable({
                responsive: true,
                autoWidth: false,
                width: '100%',
                dom: 'rtip',
                columnDefs: [
                    { orderable: false, targets: [0, 6, 7, 8] },
                    { searchable: false, targets: [0, 7, 8] },
                    { visible: true, targets: [6] }
                ],
                initComplete: function () {
                    $('#table_daftar').removeClass('d-none');
                    console.log('[Jadwal] DataTable initialized successfully.');
                }
            });

            dataRiwayat = $('#table_riwayat').DataTable({
                responsive: true,
                autoWidth: false,
                width: '100%',
                dom: 'rtip',
                columnDefs: [
                    { orderable: false, targets: [0, 6, 7, 8] },
                    { searchable: false, targets: [0, 7, 8] },
                    { visible: true, targets: [6] }
                ],
                initComplete: function () {
                    $('#table_riwayat').removeClass('d-none');
                    console.log('[Riwayat Jadwal] DataTable initialized successfully.');
                }
            });

            $('a[data-toggle="pill"]').on('shown.bs.tab', function () {

                if (dataJadwal) {
                    dataJadwal.columns.adjust().responsive.recalc();
                }

                if (dataRiwayat) {
                    dataRiwayat.columns.adjust().responsive.recalc();
                }

            });

            // Filter Ruangan
            $('#filter_ruangan').change(function () {
                var selectedRuangan = $(this).val();
                dataJadwal.column(1).search(selectedRuangan).draw();
                dataRiwayat.column(1).search(selectedRuangan).draw();
            });

            // Filter Tahun Ajaran & Semester (custom filter via ext.search.push)
            $('#filter_tahun_ajaran, #filter_semester').change(function () {
                dataJadwal.draw();
                dataRiwayat.draw();
            });

            // FUNGSI PENCARIAN CUSTOM UNTUK KEDUA TABEL
            $('#jadwal_search').on('keyup', function () {
                let keyword = $(this).val();
                dataJadwal.search(keyword).draw();
                dataRiwayat.search(keyword).draw();
            });

            // Show Entries
            $('#data-table-length').change(function () {
                dataJadwal.page.len($(this).val()).draw();
                dataRiwayat.page.len($(this).val()).draw();
            });

            // Bersihkan modal saat ditutup
            $('#modalJadwal').on('hidden.bs.modal', function () {
                $(this).find('.modal-content').html('');
            });

            // Auto-refresh ringan supaya status jadwal (Akan Datang -> Berlangsung, dst.)
            // yang berubah di background (lihat command jadwal:auto-update-status)
            // langsung terlihat tanpa perlu user membuka ulang halaman secara manual.
            // Dilewati kalau ada modal yang sedang terbuka, supaya tidak mengganggu
            // aksi yang sedang berjalan.
            setInterval(function () {
                if ($('.modal.show').length === 0) {
                    location.reload();
                }
            }, 60000);
        });
    </script>
@endpush