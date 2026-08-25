<form action="{{ url('ruangan/ajax') }}" method="POST" id="form-tambah" enctype="multipart/form-data">
    @csrf
    <div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" style="font-weight: bold;">Tambah Data Ruangan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <div class="row">

                {{-- KOLOM TENGAH (Nama) --}}
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="ruangan_nama">Nama</label>
                        <input type="text" class="form-control" id="ruangan_nama" name="ruangan_nama" placeholder="Nama Ruangan" required>
                        <small id="error_ruangan_nama" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                
                {{-- KOLOM KIRI (Kode) --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="ruangan_kode">Kode</label>
                        <input type="text" class="form-control" id="ruangan_kode" name="ruangan_kode" placeholder="Kode Ruangan" required>
                        <small id="error_ruangan_kode" class="error-text form-text text-danger"></small>
                    </div>
                </div>

                {{-- KOLOM KANAN (Kapasitas) --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="ruangan_kuota">Kapasitas</label>
                        <input type="number" class="form-control" id="ruangan_kuota" name="ruangan_kuota" required>
                        <small id="error_ruangan_kuota" class="error-text form-text text-danger"></small>
                    </div>
                </div>

                {{-- KOLOM TENGAH (Fasilitas) --}}
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="ruangan_fasilitas">Fasilitas</label>
                        <textarea class="form-control" id="ruangan_fasilitas" name="ruangan_fasilitas" placeholder="Berbagai fasilitas yang disediakan dalam ruangan"></textarea>
                        <small id="error_ruangan_fasilitas" class="error-text form-text text-danger"></small>
                    </div>
                </div>

                {{-- KOLOM KIRI (Status) --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="ruangan_status">Status</label>
                        <select class="form-control" id="ruangan_status" name="ruangan_status">
                            <option value="Tersedia" selected>Tersedia</option>
                            <option value="Diajukan">Diajukan</option>
                            <option value="Tidak Tersedia">Tidak Tersedia</option>
                        </select>
                        <small id="error_ruangan_status" class="error-text form-text text-danger"></small>
                    </div>
                </div>

                {{-- KOLOM KANAN (Kategori) --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="ruangan_kategori">Kategori</label>
                        <select class="form-control" id="ruangan_kategori" name="ruangan_kategori" required>
                            <option value="Jurusan" selected>Jurusan</option>
                            <option value="Umum">Umum</option>
                        </select>
                        <small id="error_ruangan_kategori" class="error-text form-text text-danger"></small>
                    </div>
                </div>

                {{-- KOLOM KANAN (FOTO) --}}
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="ruangan_foto">Foto</label>
                        <input type="file" class="form-control" id="ruangan_foto" name="ruangan_foto" multiple accept="image/jpg,image/jpeg,image/png" style="padding: 0px 0px 0px 0px; height: 32px;">
                        <small class="text-muted">Format: JPG/JPEG/PNG, maks. 2MB per foto. Bisa pilih lebih dari 1 file.</small>
                        <small id="error_ruangan_foto" class="error-text form-text text-danger"></small>
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
        // Gunakan event delegation untuk menangani submit form yang dimuat secara dinamis
        $(document).on('submit', '#form-tambah', function(e) {
            e.preventDefault(); // Mencegah submit form standar

            // Kosongkan semua pesan error
            $('.error-text').text('');
            $('.form-control').removeClass('is-invalid');

            $.ajax({
                url: '{{ url('ruangan/ajax') }}',
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    $('#btn-submit').prop('disabled', true).text('Menyimpan...');
                },
                success: function(response) {
                    if (response.status) {
                        // Tampilkan sweet alert untuk sukses
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            $('#modalRuangan').modal('hide'); 
                            location.reload(); 
                        });
                    } else {
                        // Tampilkan sweet alert untuk error umum
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message || 'Gagal menambahkan data ruangan.',
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
                    // Handle error lain (500, dll)
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
        // Pastikan SweetAlert2 sudah dimuat sebelum digunakan
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 library not loaded. Please ensure it is included in your template.');
        }
    });
    
</script>
