@empty($jadwal)

<div class="modal-content">

    <div class="modal-header">
        <h5 class="modal-title">Kesalahan</h5>
        <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="alert alert-danger">
            Data jadwal tidak ditemukan.
        </div>
    </div>

</div>

@else

<form
    id="form-selesaikan-pribadi"
    method="POST"
    action="{{ url('/jadwal/pribadi/'.$jadwal->jadwal_id.'/selesaikan_ajax') }}"
    enctype="multipart/form-data"
    onsubmit="submitSelesaikanAjax(event, this)">

    @csrf

    <div class="modal-content">

        <div class="modal-header">
            <h5 class="modal-title" style="font-weight: bold;">Ajukan Penyelesaian</h5>
            <button type="button" class="close" data-dismiss="modal">
                <span>&times;</span>
            </button>
        </div>

        <div class="modal-body">

            <p>
                Unggah 3 foto dokumentasi berikut untuk menyelesaikan peminjaman 
                <strong>{{ $jadwal->ruangans->pluck('ruangan_nama')->implode(', ') }}</strong> untuk jadwal 
                <strong>{{ $jadwal->jadwal_nama }}</strong>. Dokumentasi akan ditinjau terlebih  
                dahulu oleh Admin sebelum peminjaman bisa diselesaikan.
            </p>

            {{-- KONDISI JIKA DITOLAK: TAMPILKAN FOTO LAMA & ALASAN ADMIN --}}
            @if($jadwal->jadwal_status === 'Dokumentasi Tidak Sesuai')
                <hr>
                
                {{-- 1. Dokumentasi Sebelumnya --}}
                <div class="row mb-2">
                    <div class="col-md-12">
                        <label class="font-weight-bold text-dark">
                            <i class="fas fa-images mr-1 text-primary"></i>
                            Dokumentasi Sebelumnya
                        </label>
                    </div>
                </div>

                <div class="row mb-3">
                    @php
                        $dokumentasi = [
                            'foto_penggunaan' => 'Foto Pelaksanaan Kegiatan',
                            'foto_kebersihan' => 'Foto Pembersihan Ruangan',
                            'foto_kunci' => 'Foto Pengembalian Kunci',
                        ];
                    @endphp

                    @foreach($dokumentasi as $field => $label)
                        <div class="col-md-4 text-center mb-3">
                            <label class="text-dark d-block">{{ $label }}</label>

                            @if($jadwal->$field)
                                <a href="{{ asset('storage/'.$jadwal->$field) }}"
                                   target="_blank"
                                   title="Lihat ukuran penuh">
                                    <img src="{{ asset('storage/'.$jadwal->$field) }}"
                                         alt="{{ $label }}"
                                         class="img-thumbnail mb-2 border-danger"
                                         style="width:100%; max-width:180px; height:130px; object-fit:cover;">
                                </a>
                                <br>
                                <a href="{{ asset('storage/'.$jadwal->$field) }}"
                                   download
                                   class="btn btn-outline-import btn-sm">
                                    <i class="fas fa-download"></i> Unduh
                                </a>
                            @else
                                <div class="text-muted small">
                                    <i class="fas fa-image"></i><br>
                                    Belum diunggah
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- 2. Keterangan Alasan Penolakan Admin --}}
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label class="font-weight-bold text-dark">Catatan Admin</label>
                        <div class="d-flex align-items-center">
                             <i class="fas fa-exclamation mr-2 text-primary"></i>
                             <span class="text-secondary">{{ $jadwal->pengajuanTerkait?->catatan_verifikator ?? 'Dokumentasi yang diunggah sebelumnya tidak sesuai atau kurang jelas.' }}</span>
                        </div>
                    </div>
                </div>
                
                <hr>
                <!--p class="font-weight-bold text-primary mb-3"><i class="fas fa-upload mr-1"></i> Form Unggah Ulang Dokumentasi</p-->
            @endif
            {{-- AKHIR KONDISI DITOLAK --}}

            <div class="form-group">
                <label for="foto_penggunaan">Foto Penggunaan Ruangan</label>
                <input
                    type="file"
                    class="form-control"
                    id="foto_penggunaan"
                    name="foto_penggunaan"
                    accept="image/jpg,image/jpeg,image/png"
                    style="padding: 0px 0px 0px 0px; height: 32px;"
                    required>
                <small class="text-muted">Format: JPG/JPEG/PNG</small>
                <small id="error_foto_penggunaan" class="text-danger"></small>
            </div>

            <div class="form-group">
                <label for="foto_kebersihan">Foto Kebersihan Ruangan</label>
                <input
                    type="file"
                    class="form-control"
                    id="foto_kebersihan"
                    name="foto_kebersihan"
                    accept="image/jpg,image/jpeg,image/png"
                    style="padding: 0px 0px 0px 0px; height: 32px;"
                    required>
                <small class="text-muted">Format: JPG/JPEG/PNG</small>
                <small id="error_foto_kebersihan" class="text-danger"></small>
            </div>

            <div class="form-group">
                <label for="foto_kunci">Foto Pengembalian Kunci</label>
                <input
                    type="file"
                    class="form-control"
                    id="foto_kunci"
                    name="foto_kunci"
                    accept="image/jpg,image/jpeg,image/png"
                    style="padding: 0px 0px 0px 0px; height: 32px;"
                    required>
                <small class="text-muted">Format: JPG/JPEG/PNG</small>
                <small id="error_foto_kunci" class="text-danger"></small>
            </div>

        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                <i class="fas fa-times mr-1"></i>
                Batal
            </button>
            <button type="submit" class="btn btn-success" id="btn-submit-selesaikan">
                <i class="fas fa-upload mr-1"></i>
                Ajukan
            </button>
        </div>

    </div>

</form>

<style>
/* Tombol Import (Orange) */
.btn-import {
    background-color: #ff851b;
    color: #fff;
}
.btn-import:hover {
    background-color: #e07415;
    color: #fff;
}

/* Tombol Outline Import (Digunakan untuk Unduh Foto) */
.btn-outline-import {
    border-color: #ff851b;
    color: #ff851b;
}
.btn-outline-import:hover {
    background-color: #ff851b;
    color: #fff;
}
</style>

<script>
    $(document).ready(function() {
        // Gunakan Event Delegation dengan return false untuk mencegah hard-submit
        $(document).off('submit', '#form-selesaikan-pribadi').on('submit', '#form-selesaikan-pribadi', function(e){
            e.preventDefault();

            $('.text-danger').html('');
            $('.form-control').removeClass('is-invalid');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    $('#btn-submit-selesaikan').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengunggah...');
                },
                success: function(res){
                    if (res.status) {
                        $('#form-selesaikan-pribadi').closest('.modal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message
                        });
                    }
                },
                error: function(xhr){
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.msgField) {
                        $.each(xhr.responseJSON.msgField, function(k, v){
                            $('#error_' + k).html(v[0]);
                            $('#' + k).addClass('is-invalid');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan.'
                        });
                    }
                },
                complete: function() {
                    $('#btn-submit-selesaikan').prop('disabled', false).html('<i class="fas fa-upload mr-1"></i> Ajukan');
                }
            });

            return false; // PENTING: Penjaga ganda agar browser tidak reload ke halaman JSON
        });
    });
</script>

<!--script>
    // Gunakan Event Delegation (document.on) agar form AJAX tidak bocor
    $(document).off('submit', '#form-selesaikan-pribadi').on('submit', '#form-selesaikan-pribadi', function(e){
        e.preventDefault();

        $('.text-danger').html('');
        $('.form-control').removeClass('is-invalid');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                $('#btn-submit-selesaikan').prop('disabled', true).text('Mengunggah...');
            },
            success: function(res){
                if (res.status) {
                    $('#modalJadwal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message
                    });
                }
            },
            error: function(xhr){
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.msgField) {
                    $.each(xhr.responseJSON.msgField, function(k, v){
                        $('#error_' + k).html(v[0]);
                        $('#' + k).addClass('is-invalid');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan.'
                    });
                }
            },
            complete: function() {
                // Kembalikan tombol ke kondisi semula lengkap dengan ikonnya
                $('#btn-submit-selesaikan').prop('disabled', false).html('<i class="fas fa-upload mr-1"></i> Ajukan');
            }
        });
    });
</script-->

@endempty