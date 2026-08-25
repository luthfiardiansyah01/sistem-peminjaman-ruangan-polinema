<form action="{{ url('periode/ajax') }}" method="POST" id="form-tambah">
    @csrf
    <div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" style="font-weight: bold;">Tambah Data Periode</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tahun_ajaran">Tahun Ajaran</label>
                    <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran" placeholder="2025/2026" required>
                    <small id="error_tahun_ajaran" class="error-text form-text text-danger"></small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="semester">Semester</label>
                    <select class="form-control" id="semester" name="semester" required>
                        <option value="Ganjil">Ganjil</option>
                        <option value="Genap">Genap</option>
                    </select>
                    <small id="error_semester" class="error-text form-text text-danger"></small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tanggal_mulai">Tanggal Mulai</label>
                    <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                    <small id="error_tanggal_mulai" class="error-text form-text text-danger"></small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tanggal_selesai">Tanggal Selesai</label>
                    <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required>
                    <small id="error_tanggal_selesai" class="error-text form-text text-danger"></small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="periode_status">Status</label>
                    <select class="form-control" id="periode_status" name="periode_status" required>
                        <option value="Closed">Closed</option>
                        <option value="Open">Open</option>
                    </select>
                    <small id="error_periode_status" class="error-text form-text text-danger"></small>
                    <small class="form-text text-muted">Meng-Open-kan periode ini akan otomatis menutup periode lain yang sedang Open.</small>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Batal
        </button>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Simpan
        </button>
    </div>
    </div>
</form>

<script>
    $(document).ready(function() {
        $(document).on('submit', '#form-tambah', function(e) {
            e.preventDefault();

            $('.error-text').text('');
            $('.form-control').removeClass('is-invalid');

            $.ajax({
                url: '{{ url('periode/ajax') }}',
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
                            text: response.message || 'Gagal menambahkan data periode.',
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
