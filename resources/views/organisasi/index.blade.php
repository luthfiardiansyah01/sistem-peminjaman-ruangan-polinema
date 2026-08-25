@extends('layouts.template')

@section('content')
<div class="card" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
    <div class="card-header">
        <div class="d-flex align-items-center w-100">
            <div class="col-md-3 col-sm-6 d-flex align-items-center mb-2 mb-md-0">
                <label class="col-form-label mr-2 mb-0" for="data-table-length">Show</label>
                <select class="form-control form-control-sm" id="data-table-length" style="width: 70px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <label class="col-form-label ml-2 mb-0">entries</label>
            </div>

            @if(Auth::user()->getRole() == 'ADM')
            <div class="card-tools ml-auto my-1">
                <button onclick="modalAction('{{ url('/organisasi/create_ajax') }}')" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Tambah
                </button>
            </div>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <table class="table table-hover table-sm" id="table_organisasi">
            <thead style="background-color: #e3e3e3; font-color: #3F3F3F;">
                <tr>
                    <th>No.</th>
                    <th>Nama</th>
                    <th>Kode</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody style="background-color: #ffffffff; font-color: #3F3F3F;">
                @foreach($organisasi as $key => $o)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ Str::limit($o->organisasi_nama, 50, '...') }}</td>
                    <td>{{ $o->organisasi_kode }}</td>
                    <td>
                        <div class="btn-group" role="group" aria-label="Aksi">
                            <button onclick="modalAction('{{ url('/organisasi/' . $o->organisasi_id . '/show_ajax') }}')" class="btn btn-outline-info btn-sm" title="Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if(Auth::user()->getRole() == 'ADM')
                            <button onclick="modalAction('{{ url('/organisasi/' . $o->organisasi_id . '/edit_ajax') }}')" class="btn btn-outline-warning btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="modalAction('{{ url('/organisasi/' . $o->organisasi_id . '/confirm_ajax') }}')" class="btn btn-outline-danger btn-sm" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Modal untuk CRUD AJAX --}}
<div class="modal fade" id="modalOrganisasi" tabindex="-1" role="dialog" aria-labelledby="modalOrganisasiTitle" aria-hidden="true">
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
                $('#modalOrganisasi .modal-content').html(data);
                $('#modalOrganisasi').modal('show');
            },
            error: function(xhr) {
                alert('Gagal memuat modal. Cek console log.');
                console.error('AJAX Error:', xhr.responseText);
            }
        });
    }

    $(document).ready(function() {
        var dataOrganisasi = $('#table_organisasi').DataTable({
            autoWidth: false,
            responsive: true,
            dom: 'rtip',
            columnDefs: [
                { orderable: false, targets: 3 }
            ],
        });

        $('#data-table-length').change(function() {
            dataOrganisasi.page.len($(this).val()).draw();
        });
    });
</script>
@endpush
