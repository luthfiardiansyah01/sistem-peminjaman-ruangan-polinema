@extends('layouts.template')

@section('content')

<div class="card card-primary card-outline card-outline-tabs">

    <div class="card-header p-0 border-bottom-0">

        {{-- TAB --}}
        <ul class="nav nav-tabs" id="jadwalPribadiTab" role="tablist">

            <li class="nav-item">
                <a class="nav-link active"
                id="tab-daftar"
                data-toggle="pill"
                href="#daftarJadwal"
                role="tab">

                    <i class="fas fa-calendar-alt mr-1"></i>
                    Daftar Jadwal
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link"
                id="tab-pengajuan"
                data-toggle="pill"
                href="#riwayatPengajuan"
                role="tab">

                    <i class="fas fa-file-signature mr-1"></i>
                    Riwayat Pengajuan
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link"
                id="tab-riwayat"
                data-toggle="pill"
                href="#riwayatJadwal"
                role="tab">

                    <i class="fas fa-history mr-1"></i>
                    Riwayat Jadwal
                </a>
            </li>

        </ul>

        {{-- Req 6: Layout filter inline flex (sesuai SKRIPSI.zip) --}}
        <!--div class="d-flex flex-wrap align-items-center p-3" style="gap: 12px;">

            <div class="d-flex align-items-center" style="gap:6px;">
                <label class="mb-0" style="white-space:nowrap;">Ruangan:</label>
                <select id="filter_ruangan" class="form-control form-control-sm" style="min-width:160px;">
                    <option value="">- Semua Ruangan -</option>
                    @foreach($ruanganList as $ruangan)
                        <option value="{{ $ruangan->ruangan_id }}">{{ $ruangan->ruangan_nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex align-items-center" style="gap:6px;">
                <label class="mb-0" style="white-space:nowrap;">Tahun Ajaran:</label>
                <select id="filter_tahun_ajaran" class="form-control form-control-sm" style="min-width:130px;">
                    <option value="">Semua</option>
                    @foreach($tahunAjaranList as $tahun)
                        <option value="{{ $tahun }}">{{ $tahun }}</option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex align-items-center" style="gap:6px;">
                <label class="mb-0">Semester:</label>
                <select id="filter_semester" class="form-control form-control-sm" style="min-width:100px;">
                    <option value="">Semua</option>
                    <option>Ganjil</option>
                    <option>Genap</option>
                </select>
            </div>

            <div class="d-flex align-items-center" style="gap:6px;">
                <label class="mb-0">Show</label>
                <select id="data-table-length" class="form-control form-control-sm" style="width:70px;">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                    <option>100</option>
                </select>
                <label class="mb-0">entries</label>
            </div>

            <div class="ml-auto" id="btnAjukan">
                {{-- Req 6: URL diubah ke /pengajuan/create_ajax (sesuai SKRIPSI.zip) --}}
                <button class="btn btn-success" onclick="modalAction('{{ url('pengajuan/create_ajax') }}')">
                    <i class="fas fa-plus"></i> Ajukan Peminjaman
                </button>
            </div>

        </div-->

    </div>

    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center gap-2">

                {{-- Filter Ruangan --}}
                <div class="d-flex align-items-center">
                    <label class="mr-2 mb-0">Ruangan:</label>
                    <select class="form-control form-control-sm" id="filter_ruangan" style="width: 200px;">
                        <option value="">- Semua Ruangan -</option>
                        @foreach ($ruanganList as $ruangan)
                            <option value="{{ $ruangan->ruangan_id }}">{{ $ruangan->ruangan_nama }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Tahun Ajaran --}}
                <div class="d-flex align-items-center">
                    <label class="ml-3 mr-2 mb-0" style="white-space: nowrap;">Tahun Ajaran:</label>
                    <select id="filter_tahun_ajaran" class="form-control form-control-sm" style="width:120px;">
                        <option value="">- Semua -</option>
                        @foreach($tahunAjaranList as $tahunAjaran)
                            <option value="{{ $tahunAjaran }}">{{ $tahunAjaran }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Semester --}}
                <div class="d-flex align-items-center">
                    <label class="ml-3 mr-2 mb-0">Semester:</label>
                    <select id="filter_semester" class="form-control form-control-sm" style="width:120px;">
                        <option value="">- Semua -</option>
                        <option value="Ganjil">Ganjil</option>
                        <option value="Genap">Genap</option>
                    </select>
                </div>

                {{-- Show Entries --}}
                <div class="d-flex align-items-center">
                    <label class="ml-3 mr-2 mb-0" for="data-table-length">Show</label>
                    <select class="form-control form-control-sm" id="data-table-length" style="width:70px;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <label class="ml-2 mb-0">entries</label>
                </div>

            </div>
            @if(in_array(Auth::user()->getRole(), ['DSN', 'TDK', 'MHS']))
                {{-- @if(jadwal.components.table_daftar) --}}
                <button id="btn-ajukan" onclick="modalAction('{{ url('/pengajuan/create_ajax') }}')" class="btn btn-success btn-sm">
                    <i class="fas fa-pen-alt"></i> Ajukan Peminjaman
                </button>
                {{-- @endif --}}
            @endif
        </div>

    </div>

    <div class="card-body">

        <div class="tab-content">

            <div class="tab-pane fade show active"
                 id="daftarJadwal"
                 role="tabpanel">

                @include('jadwal.components.table_daftar',[
                    'jadwals'=>$jadwals,
                    'showEdit'=> Auth::user()->getRole() != \App\Constants\RoleConstants::ADMIN,
                    'showView'=> false,
                    'showSelesaikan'=> Auth::user()->getRole() != \App\Constants\RoleConstants::ADMIN,
                ])

            </div>

            <div class="tab-pane fade"
                 id="riwayatPengajuan"
                 role="tabpanel">

                @include('jadwal.components.table_pengajuan',[
                    'pengajuans'=>$pengajuans
                ])

            </div>

            <div class="tab-pane fade"
                 id="riwayatJadwal"
                 role="tabpanel">

                @include('jadwal.components.table_riwayat',[
                    'riwayats'=>$riwayats,
                    'showView'=> true,
                ])

            </div>

        </div>

    </div>

</div>

<div class="modal fade" id="modalJadwal" tabindex="-1" role="dialog" aria-labelledby="modalJadwalTitle" aria-hidden="true">
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
<style>
/* ==========================
   TAB BAR
========================== */

/*#jadwalPribadiTab{
    background:#17a2b8;
    border-bottom:none;
    padding:0;
}*/

/* Semua tab */
/*#jadwalPribadiTab .nav-link{
    color:#fff;
    font-weight:500;
    border:none;
    border-radius:0;
    padding:14px 20px;
    transition:.2s;
}*/

/* Hover */
/*#jadwalPribadiTab .nav-link:hover{
    background:rgba(255,255,255,.15);
    color:#fff;
}*/

/* Tab aktif */
/*#jadwalPribadiTab .nav-link.active{
    background:#fff;
    color:#343a40;
    border:none;
}*/

/* Icon */
/*#jadwalPribadiTab .nav-link i{
    margin-right:6px;
}

.card-header{
    padding: 0;
    border-bottom: none;
}

.card-header .nav-tabs{
    margin-bottom: 1.25rem;
}

.card-header .row{
    padding:20px;
}

.card-header label{
    font-weight: 600;
    margin-bottom: .35rem;
}

.card-header .btn{
    min-width: 190px;
}

.btn-group-sm .btn{
    min-width:34px;
}*/

/* ==========================================
   TABLE RIWAYAT, PENGAJUAN, DAFTAR
========================================== */

#table_daftar thead th,
#table_pengajuan thead th,
#table_riwayat thead th{
    /*background:#17a2b8 !important;
    color:#fff !important;
    font-weight:600;
    text-align:center;*/
    vertical-align:middle;
    /*border-color:#1496a8;*/
}

/*#table_daftar tbody tr:nth-child(even),
#table_pengajuan tbody tr:nth-child(even),
#table_riwayat tbody tr:nth-child(even){
    background:#f8f9fa;
}

#table_daftar tbody tr:hover,
#table_pengajuan tbody tr:hover,
#table_riwayat tbody tr:hover{
    background:#e8f8fb;
    transition:.2s;
}*/

#table_daftar tbody td,
#table_pengajuan tbody td,
#table_riwayat tbody td{
    vertical-align:middle;
}

/* Badge Status */
.status-badge{
    display:inline-block;
    /*min-width:140px;*/
    white-space:normal;
    text-align:center;
    padding:4px 8px;
    border-radius:8px;
    /*border: 2px solid;*/
    font-size:12px;
    font-weight:600;
}

.status-proposed{
    background:#DAE8FC;
    color:#111827;
    border-color: #6C8EBF;
}

.status-upcoming{
    background:#b0e3e6;
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

.status-success{
    background:#28a745;
    color:#ffffff;
    border-color: #0f743eff;
}

.status-danger{
    background:#dc3545;
    color:#ffffff;
    border-color: #7A1B1E;
}

.status-warning{
    background:#856404;
    color:#ffffff;
    border-color: #5b4502ff;
}
.btn-import {
    background-color: #ff851b;
    color: #fff;
}
.btn-import:hover {
    background-color: #e07415; /* Darker shade of orange */
    color: #fff;
}
.nav-tabs .nav-link{
    font-weight:600;
}

.nav-tabs .nav-link i{
    margin-right:6px;
}
</style>
@endpush

@push('js')
<script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script>
$(document).ready(function () {

    $('.tab-btn').on('click', function () {

        let target = $(this).data('target');

        $('.tab-btn').removeClass('active');
        $(this).addClass('active');

        $('.tab-pane').removeClass('active');
        $(target).addClass('active');

        // jika menggunakan DataTables
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust().responsive.recalc();
    });

});

// Data periode (m_periode) untuk filter Tahun Ajaran/Semester
const periodeFilterData = @json($periodeList);

function periodeRangeFor(tahunAjaran, semester) {
    if (!tahunAjaran && !semester) return null;
    const matches = periodeFilterData.filter(function(p) {
        return (!tahunAjaran || p.tahun_ajaran === tahunAjaran) && (!semester || p.semester === semester);
    });
    if (matches.length === 0) return { mulai: null, selesai: null };
    return {
        mulai: matches.reduce((min, p) => p.tanggal_mulai < min ? p.tanggal_mulai : min, matches[0].tanggal_mulai),
        selesai: matches.reduce((max, p) => p.tanggal_selesai > max ? p.tanggal_selesai : max, matches[0].tanggal_selesai),
    };
}

// Filter Tahun Ajaran & Semester untuk ketiga tabel.
// Req 6: Semester difilter langsung dari teks kolom 6 (sama dengan fix Req 3 di jadwal/index).
$.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
    if (!['table_daftar', 'table_pengajuan', 'table_riwayat'].includes(settings.sTableId)) return true;

    const tahunAjaran = $('#filter_tahun_ajaran').val();
    const semester    = $('#filter_semester').val();

    // Semester: bandingkan langsung teks kolom 6
    if (semester) {
        const rowSemester = (data[6] || '')
            .replace(/<[^>]*>/g, '')   // strip HTML tags
            .replace(/\s+/g, ' ')      // normalkan whitespace (newline → spasi)
            .trim();
        if (rowSemester !== semester) return false;
    }

    // Tahun Ajaran: gunakan date-range dari m_periode
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

let dataDaftar, dataPengajuan, dataRiwayat;

$(function(){

    dataDaftar = $('#table_daftar').DataTable({
        responsive:true,
        autoWidth:false,
        dom: 'rtip',
        pageLength: 10
    });

    dataPengajuan = $('#table_pengajuan').DataTable({
        responsive:true,
        autoWidth:false,
        dom: 'rtip',
        pageLength: 10
    });

    dataRiwayat = $('#table_riwayat').DataTable({
        responsive:true,
        autoWidth:false,
        dom: 'rtip',
        pageLength: 10
    });

    $('a[data-toggle="pill"]').on('shown.bs.tab', function () {

        let target = $(this).attr('href');

        if (target === '#daftarJadwal') {
            $('#btn-ajukan').show();
            //$('#btnAjukan').show();
        } else {
            $('#btn-ajukan').hide();
            //$('#btnAjukan').hide();
        }

        $($.fn.dataTable.tables(true))
            .DataTable()
            .columns.adjust()
            .responsive.recalc();

    });

    // Kondisi awal
    $('#btn-ajukan').show();
    //$('#btnAjukan').show();

    // Filter Ruangan — kolom Ruangan (index 1) di ketiga tabel punya data-search
    // berisi ruangan_id + ruangan_nama (lihat komponen tabel terkait).
    $('#filter_ruangan').on('change', function() {
        const selectedRuangan = $(this).val();
        dataDaftar.column(1).search(selectedRuangan).draw();
        dataPengajuan.column(1).search(selectedRuangan).draw();
        dataRiwayat.column(1).search(selectedRuangan).draw();
    });

    // Filter Tahun Ajaran & Semester (custom filter via ext.search.push di atas)
    $('#filter_tahun_ajaran, #filter_semester').on('change', function() {
        dataDaftar.draw();
        dataPengajuan.draw();
        dataRiwayat.draw();
    });

    // Show entries
    $('#data-table-length').on('change', function() {
        const len = $(this).val();
        dataDaftar.page.len(len).draw();
        dataPengajuan.page.len(len).draw();
        dataRiwayat.page.len(len).draw();
    });

});
// Fungsi global untuk menampilkan modal
    function modalAction(url) {
        // Disable semua tombol pemicu modal selama request
        $('[onclick^="modalAction"]').prop('disabled', true);
        $('#modalSize').removeClass('modal-xl modal-lg modal-md modal-sm').addClass('modal-lg');
        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                $('#modalJadwal .modal-content').html(data);
                $('#modalJadwal').modal('show');
            },
            error: function(xhr) {
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
            complete: function() {
                $('[onclick^="modalAction"]').prop('disabled', false);
            }
        });
    }

    // Auto-refresh ringan supaya status jadwal (Akan Datang -> Berlangsung, dst.)
    // yang berubah di background (lihat command jadwal:auto-update-status) langsung
    // terlihat tanpa perlu user membuka ulang halaman secara manual. Dilewati kalau
    // ada modal yang sedang terbuka, supaya tidak mengganggu aksi yang sedang berjalan
    // (mis. sedang mengisi form Selesaikan Peminjaman).
    setInterval(function () {
        if ($('.modal.show').length === 0) {
            location.reload();
        }
    }, 60000);
</script>
@endpush