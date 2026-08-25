<form action="{{ url('organisasi/ajax') }}" method="POST" id="form-tambah">
    @csrf
    <div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" style="font-weight: bold;">Tambah Data Organisasi</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="organisasi_nama">Nama</label>
                        <input type="text" class="form-control" id="organisasi_nama" name="organisasi_nama" placeholder="Nama Organisasi" required>
                        <small id="error_organisasi_nama" class="error-text form-text text-danger"></small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="organisasi_kode">Kode</label>
                        <input type="text" class="form-control" id="organisasi_kode" name="organisasi_kode" placeholder="Kode Organisasi" required>
                        <small id="error_organisasi_kode" class="error-text form-text text-danger"></small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="organisasi_logo">Logo (path/URL)</label>
                        <input type="text" class="form-control" id="organisasi_logo" name="organisasi_logo" placeholder="Path atau URL logo (opsional)">
                        <small id="error_organisasi_logo" class="error-text form-text text-danger"></small>
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
                url: '{{ url('organisasi/ajax') }}',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                beforeSend: function() {
                    $('#btn-submit').prop('disabled', true).text('Menyimpan...');
                },
                success: function(response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            $('#modalOrganisasi').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message || 'Gagal menambahkan data organisasi.',
                        });
                    }
                },
                error: function(xhr) {
                    $('#btn-submit').prop('disabled', false).text('Simpan');

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.msgField) {
                        let errors = xhr.responseJSON.msgField;
                        $.each(errors, function(key, value) {
                            $('#error_' + key).text(value[0]);
                            $('#' + key).addClass('is-invalid');
                        });
                    }
                    else if (xhr.status !== 422) {
                         Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan pada server. Cek console log!',
                        });
                        console.error('AJAX Error:', xhr.responseText);
                    }
                },
                complete: function() {
                    $('#btn-submit').prop('disabled', false).text('Simpan');
                }
            });
            return false;
        });
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 library not loaded. Please ensure it is included in your template.');
        }
    });
</script>
