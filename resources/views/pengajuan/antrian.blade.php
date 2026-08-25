@extends('layouts.template')

@section('content')
<div class="card" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
    <div class="card-header">
        <div class="card-title">{{ $page->title }}</div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-sm">
            <thead style="background-color: #e3e3e3;">
                <tr>
                    <th>No.</th>
                    <th>Nama Kegiatan</th>
                    <th>Pengaju</th>
                    <th>Tanggal Acara</th>
                    <th>Batas Waktu Approval</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($antrian as $key => $a)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ Str::limit($a->pengajuan->pengajuan_nama, 35, '...') }}</td>
                    <td>{{ $a->pengajuan->user->getDisplayName() }} ({{ optional($a->pengajuan->organisasi)->organisasi_nama }})</td>
                    <td>{{ \Carbon\Carbon::parse($a->pengajuan->pengajuan_tgl)->locale('id')->translatedFormat('d F Y') }}</td>
                    <td>{{ $a->batas_waktu?->translatedFormat('d F Y H:i') }}</td>
                    <td>
                        <button onclick="modalProses({{ $a->approval_id }}, '{{ addslashes($a->pengajuan->pengajuan_nama) }}')" class="btn btn-primary btn-sm">
                            <i class="fas fa-gavel"></i> Proses
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center">Tidak ada pengajuan yang menunggu persetujuan Anda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalProsesApproval" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-weight: bold;">Proses Approval</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="proses_nama_kegiatan"></p>
                <div class="form-group">
                    <label>Alasan Penolakan (wajib jika menolak)</label>
                    <textarea class="form-control" id="alasan_penolakan"></textarea>
                    <small id="error_alasan_penolakan" class="error-text form-text text-danger"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="prosesApproval('Ditolak')">Tolak</button>
                <button type="button" class="btn btn-success" onclick="prosesApproval('Disetujui')">Setujui</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    let currentApprovalId = null;

    function modalProses(approvalId, namaKegiatan) {
        currentApprovalId = approvalId;
        $('#proses_nama_kegiatan').text(namaKegiatan);
        $('#alasan_penolakan').val('');
        $('#error_alasan_penolakan').text('');
        $('#modalProsesApproval').modal('show');
    }

    function prosesApproval(status) {
        $('#error_alasan_penolakan').text('');
        $.ajax({
            url: '{{ url('/pengajuan/approval') }}/' + currentApprovalId + '/proses_ajax',
            type: 'PUT',
            data: {
                status_approval: status,
                alasan_penolakan: $('#alasan_penolakan').val(),
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message, timer: 2000, showConfirmButton: false })
                        .then(() => { $('#modalProsesApproval').modal('hide'); location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: response.message });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.msgField) {
                    $('#error_alasan_penolakan').text(xhr.responseJSON.msgField.alasan_penolakan[0]);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: (xhr.responseJSON && xhr.responseJSON.message) || 'Terjadi kesalahan pada server.' });
                }
            }
        });
    }
</script>
@endpush
