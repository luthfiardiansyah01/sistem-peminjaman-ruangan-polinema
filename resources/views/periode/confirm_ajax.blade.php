@empty($periode)
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" style="font-weight: bold;">Kesalahan</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="alert alert-danger">Data yang anda cari tidak ditemukan</div>
            <button type="button" data-dismiss="modal" class="btn btn-warning">Kembali</button>
        </div>
    </div>
@else
<form action="{{ url('/periode/' . $periode->periode_id . '/delete_ajax') }}" method="POST" id="form-confirm">
    @csrf
    @method('DELETE')
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" style="font-weight: bold;">Hapus Data Periode</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div style="color: red;">
                Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak bisa dibatalkan!
                <br><br>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Tahun Ajaran</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-week mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $periode->tahun_ajaran }}</span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Semester</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-hourglass-half mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $periode->semester }}</span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Tanggal Mulai</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-plus mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $periode->tanggal_mulai->locale('id')->translatedFormat('d F Y') }}</span>    
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Tanggal Selesai</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-minus mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $periode->tanggal_selesai->locale('id')->translatedFormat('d F Y') }}</span>    
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash mr-1"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</form>
<script>
    $(document).ready(function() {
        $(document).on('submit', '#form-confirm', function(e) {
            e.preventDefault();

            $.ajax({
                url: $(this).attr('action'),
                type: 'DELETE',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            $('#modalPeriode').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message || 'Gagal menghapus data periode.',
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan pada server.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage,
                    });
                    console.error('AJAX Error:', xhr.responseText);
                }
            });
            return false;
        });
    });
</script>
@endempty
