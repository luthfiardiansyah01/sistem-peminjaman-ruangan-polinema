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
<form action="{{ url('/periode/' . $periode->periode_id . '/update_ajax') }}" method="POST" id="form-edit">
    @csrf
    @method('PUT')
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" style="font-weight: bold;">Edit Data Periode</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tahun Ajaran</label>
                        <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran" value="{{ $periode->tahun_ajaran }}" required>
                        <small id="error_tahun_ajaran" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Semester</label>
                        <select class="form-control" id="semester" name="semester" required>
                            <option value="Ganjil" {{ $periode->semester === 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                            <option value="Genap" {{ $periode->semester === 'Genap' ? 'selected' : '' }}>Genap</option>
                        </select>
                        <small id="error_semester" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tanggal Mulai</label>
                        <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="{{ $periode->tanggal_mulai->format('Y-m-d') }}" required>
                        <small id="error_tanggal_mulai" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tanggal Selesai</label>
                        <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" value="{{ $periode->tanggal_selesai->format('Y-m-d') }}" required>
                        <small id="error_tanggal_selesai" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" id="periode_status" name="periode_status" required>
                            <option value="Closed" {{ $periode->periode_status === 'Closed' ? 'selected' : '' }}>Closed</option>
                            <option value="Open" {{ $periode->periode_status === 'Open' ? 'selected' : '' }}>Open</option>
                        </select>
                        <small id="error_periode_status" class="error-text form-text text-danger"></small>
                        <small class="form-text text-muted">Meng-Open-kan periode ini akan otomatis menutup periode lain yang sedang Open.</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" data-dismiss="modal" class="btn btn-secondary">
                <i class="fas fa-times"></i> Batal
            </button>
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>
    </div>
</form>
<script>
    $(document).ready(function() {
        $(document).on('submit', '#form-edit', function(e) {
            e.preventDefault();
            $('.error-text').text('');
            $('.form-control').removeClass('is-invalid');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
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
                            text: response.message || 'Gagal mengupdate data periode.',
                        });
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.msgField) {
                        let errors = xhr.responseJSON.msgField;
                        $.each(errors, function(key, value) {
                            $('#error_' + key).text(value[0]);
                            $('#' + key).addClass('is-invalid');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan pada server. Cek console log!',
                        });
                        console.error('AJAX Error:', xhr.responseText);
                    }
                }
            });
            return false;
        });
    });
</script>
@endempty
