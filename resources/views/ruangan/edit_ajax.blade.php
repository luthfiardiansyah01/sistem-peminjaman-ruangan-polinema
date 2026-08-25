@empty($ruangan)
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" style="font-weight: bold;">Kesalahan</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="alert alert-danger">
                <h5><i class="icon fas fa-ban"></i> Kesalahan!!!</h5>
                Data yang anda cari tidak ditemukan
            </div>
            <button type="button" data-dismiss="modal" class="btn btn-warning">Kembali</button>
        </div>
    </div>
@else
{{-- Req 2: enctype multipart/form-data untuk mendukung upload foto --}}
<form action="{{ url('/ruangan/' . $ruangan->ruangan_id . '/update_ajax') }}" method="POST" id="form-edit" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" style="font-weight: bold;">Edit Data Ruangan</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" class="form-control" id="ruangan_nama" name="ruangan_nama" value="{{ $ruangan->ruangan_nama }}" required>
                        <small id="error_ruangan_nama" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Kode</label>
                        <input type="text" class="form-control" id="ruangan_kode" name="ruangan_kode" value="{{ $ruangan->ruangan_kode }}" required>
                        <small id="error_ruangan_kode" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Kapasitas</label>
                        <input type="number" class="form-control" id="ruangan_kuota" name="ruangan_kuota" value="{{ $ruangan->ruangan_kuota }}" required>
                        <small id="error_ruangan_kuota" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Fasilitas</label>
                        <textarea class="form-control" id="ruangan_fasilitas" name="ruangan_fasilitas">{{ $ruangan->ruangan_fasilitas }}</textarea>
                        <small id="error_ruangan_fasilitas" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" id="ruangan_status" name="ruangan_status">
                            <option value="Tersedia" {{ $ruangan->ruangan_status == 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                            <option value="Diajukan" {{ $ruangan->ruangan_status == 'Diajukan' ? 'selected' : '' }}>Diajukan</option>
                            <option value="Tidak Tersedia" {{ $ruangan->ruangan_status == 'Tidak Tersedia' ? 'selected' : '' }}>Tidak Tersedia</option>
                        </select>
                        <small id="error_ruangan_status" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Kategori</label>
                        <select class="form-control" id="ruangan_kategori" name="ruangan_kategori" required>
                            <option value="Jurusan" {{ $ruangan->ruangan_kategori == 'Jurusan' ? 'selected' : '' }}>Jurusan</option>
                            <option value="Umum" {{ $ruangan->ruangan_kategori == 'Umum' ? 'selected' : '' }}>Umum</option>
                        </select>
                        <small id="error_ruangan_kategori" class="error-text form-text text-danger"></small>
                    </div>
                </div>

                {{-- Req 2: Foto-foto yang sudah ada, bisa dihapus satu per satu --}}
                @php $existingFotos = $ruangan->fotos; @endphp
                @if (!empty($existingFotos))
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Foto</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($existingFotos as $foto)
                            <div class="mr-2 mb-2 text-center">
                                <img src="{{ asset('storage/' . $foto) }}" alt="Foto" class="img-thumbnail mb-2" style="width:80px;height:80px;object-fit:cover;">
                                <div class="d-flex align-items-center">
                                    <input type="checkbox" name="hapus_foto[]" value="{{ $foto }}" id="hapus_{{ $loop->index }}" class="mr-1">
                                    <label for="hapus_{{ $loop->index }}" class="text-danger small mb-0">Hapus</label>
                                </div>
                                <small class="text-muted">Centang untuk menghapus.</small>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="file" class="form-control" name="ruangan_foto_baru[]" id="ruangan_foto_baru" multiple accept="image/jpg,image/jpeg,image/png" style="padding: 0px 0px 0px 0px; height: 32px;">
                        <small class="text-muted">Format: JPG/JPEG/PNG, maks. 2MB per foto. Bisa pilih lebih dari 1 file.</small>
                        <small id="error_ruangan_foto_baru" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                @else

                {{-- Upload foto baru (boleh lebih dari 1) --}}
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Foto</label>
                        <input type="file" class="form-control" name="ruangan_foto_baru[]" id="ruangan_foto_baru" multiple accept="image/jpg,image/jpeg,image/png" style="padding: 0px 0px 0px 0px; height: 32px;">
                        <small class="text-muted">Format: JPG/JPEG/PNG, maks. 2MB per foto. Bisa pilih lebih dari 1 file.</small>
                        <small id="error_ruangan_foto_baru" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                @endif
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" data-dismiss="modal" class="btn btn-secondary">
                <i class="fas fa-times"></i> Batal
            </button>
            <button type="submit" id="btn-submit-edit" class="btn btn-warning">
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
            $('.form-control, .form-control-file').removeClass('is-invalid');

            // Req 2: Gunakan FormData agar file foto ikut terkirim
            var formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    $('#btn-submit-edit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
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
                            $('#modalRuangan').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: response.message || 'Gagal mengupdate data ruangan.' });
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
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Terjadi kesalahan pada server.' });
                        console.error('AJAX Error:', xhr.responseText);
                    }
                },
                complete: function() {
                    $('#btn-submit-edit').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
                }
            });
            return false;
        });
    });
</script>
@endempty