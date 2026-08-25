@extends('layouts.template')

@section('content')
<div class="card" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center">
                <label class="mr-2 mb-0">Tahun Ajaran:</label>
                <select id="filter_tahun_ajaran" class="form-control form-control-sm" style="width:150px;">
                    <option value="">Semua</option>
                    @foreach($tahunAjaranList as $tahunAjaran)
                        <option value="{{ $tahunAjaran }}">{{ $tahunAjaran }}</option>
                    @endforeach
                </select>

                <label class="ml-3 mr-2 mb-0">Semester:</label>
                <select id="filter_semester" class="form-control form-control-sm" style="width:120px;">
                    <option value="">Semua</option>
                    <option value="Ganjil">Ganjil</option>
                    <option value="Genap">Genap</option>
                </select>
            </div>
            <button onclick="modalAction('{{ url('/periode/create_ajax') }}')" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Tambah
            </button>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <p class="text-secondary">
            Ketika sebuah periode berstatus <strong>Open</strong>, seluruh proses pengajuan
            peminjaman, verifikasi, pembuatan/perubahan jadwal dapat dilakukan. Hanya
            <strong>satu periode yang boleh Open dalam satu waktu</strong> — membuka periode lain otomatis menutup yang sedang aktif.
        </p>

        <table class="table table-hover table-sm" id="table_periode">
            <thead style="background-color: #e3e3e3;">
                <tr>
                    <th>No.</th>
                    <th>Tahun Ajaran</th>
                    <th>Semester</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Selesai</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($periodes as $key => $p)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td data-tahun-ajaran="{{ $p->tahun_ajaran }}">{{ $p->tahun_ajaran }}</td>
                    <td data-semester="{{ $p->semester }}">{{ $p->semester }}</td>
                    <td>{{ $p->tanggal_mulai->locale('id')->translatedFormat('d F Y') }}</td>
                    <td>{{ $p->tanggal_selesai->locale('id')->translatedFormat('d F Y') }}</td>
                    <td>
                        @if($p->periode_status === 'Open')
                            <span class="badge badge-success">Open</span>
                        @else
                            <span class="badge badge-danger">Closed</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button onclick="modalAction('{{ url('/periode/' . $p->periode_id . '/show_ajax') }}')" class="btn btn-outline-info btn-sm" title="Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="modalAction('{{ url('/periode/' . $p->periode_id . '/edit_ajax') }}')" class="btn btn-outline-warning btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="modalAction('{{ url('/periode/' . $p->periode_id . '/confirm_ajax') }}')" class="btn btn-outline-danger btn-sm" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center">Belum ada data periode.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalPeriode" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" id="modalSize">
        <div class="modal-content"></div>
    </div>
</div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
@endpush

@push('js')
    <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script>
    function modalAction(url) {
        $('#modalSize').removeClass('modal-xl modal-lg modal-md modal-sm').addClass('modal-lg');
        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                $('#modalPeriode .modal-content').html(data);
                $('#modalPeriode').modal('show');
            },
            error: function(xhr) {
                alert('Gagal memuat modal. Cek console log.');
                console.error('AJAX Error:', xhr.responseText);
            }
        });
    }

    $(function () {
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            const tahunAjaran = $('#filter_tahun_ajaran').val();
            const semester = $('#filter_semester').val();

            const rowTahunAjaran = data[1];
            const rowSemester = data[2];

            if (tahunAjaran && tahunAjaran !== rowTahunAjaran) {
                return false;
            }

            if (semester && semester !== rowSemester) {
                return false;
            }

            return true;
        });

        let table = $('#table_periode').DataTable({
            responsive: true,
            autoWidth: false,
            dom: 'rtip',
            pageLength: 10,
            columnDefs: [
                { orderable: false, targets: [0, 2, 5, 6] }
            ]
        });

        $('#filter_tahun_ajaran, #filter_semester').change(function () {
            table.draw();
        });
    });
</script>
@endpush
