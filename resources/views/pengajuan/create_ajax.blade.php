<form action="{{ url('pengajuan/ajax') }}" method="POST" id="form-tambah">
    @csrf
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" style="font-weight: bold;">Ajukan Peminjaman</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Nama Kegiatan</label>
                        <input type="text" class="form-control" name="pengajuan_nama" required>
                        <small id="error_pengajuan_nama" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                @if(Auth::user()->getRole() == 'MHS')
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Organisasi Pengaju</label>
                        <select class="form-control select2-organisasi" name="organisasi_id" required>
                            <option value="">- Pilih Organisasi -</option>
                            @foreach($organisasiList as $o)
                                <option value="{{ $o->organisasi_id }}">{{ $o->organisasi_nama }}</option>
                            @endforeach
                        </select>
                        <small id="error_organisasi_id" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                @endif
                <!--div class="col-md-12">
                    <div class="form-group">
                        <label>Organisasi Pengaju</label>
                        <select class="form-control" name="organisasi_id" required>
                            <option value="">- Pilih Organisasi -</option>
                            @foreach($organisasiList as $o)
                                <option value="{{ $o->organisasi_id }}">{{ $o->organisasi_nama }}</option>
                            @endforeach
                        </select>
                        <small id="error_organisasi_id" class="error-text form-text text-danger"></small>
                    </div>
                </div-->
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Ketua Pelaksana</label>
                        <select class="form-control select2-kapel" name="ketua_pelaksana_user_id" required>
                            <option value="">- Pilih Ketua Pelaksana -</option>
                            @foreach($ketuaPelaksanaList as $k)
                                <option value="{{ $k->user_id }}">{{ $k->nama }}</option>
                            @endforeach
                        </select>
                        <small id="error_ketua_pelaksana_user_id" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" class="form-control" name="pengajuan_tgl" required>
                        <small id="error_pengajuan_tgl" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Jam Mulai</label>
                        <input type="time" class="form-control" name="pengajuan_jam_mulai" required>
                        <small id="error_pengajuan_jam_mulai" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Jam Selesai</label>
                        <input type="time" class="form-control" name="pengajuan_jam_selesai" required>
                        <small id="error_pengajuan_jam_selesai" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Jumlah Peserta</label>
                        <input type="number" class="form-control" name="pengajuan_jumPes" required>
                        <small id="error_pengajuan_jumPes" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Ruangan</label>
                        <select class="form-control select2-ruangan" name="ruangan_ids[]" data-placeholder="- Pilih Ruangan -" multiple required>
                            @foreach($ruanganList as $r)
                                <option value="{{ $r->ruangan_id }}">{{ $r->ruangan_nama }}</option>
                            @endforeach
                        </select>
                        <small id="error_ruangan_ids" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-12">
                    <div id="ruangan-availability" style="display:none;">
                        <label>Status Ruangan pada Tanggal Terpilih</label>
                        <table class="table table-sm table-bordered mb-3">
                            <tbody id="ruangan-availability-body"></tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Panitia</label>
                        <table class="table table-sm table-bordered mb-2" id="panitia-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Keterangan</th>
                                    <th style="width:1%">
                                        <button type="button" class="btn btn-sm btn-outline-success" id="btn-tambah-panitia" disabled title="Tambah Panitia">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="panitia-rows"></tbody>
                        </table>
                        <small class="form-text text-muted" id="panitia-hint">Pilih Organisasi Pengaju terlebih dahulu.</small>
                        <small id="error_panitia_user_id" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea class="form-control" name="pengajuan_keterangan"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                <i class="fas fa-times mr-1"></i> Batal
            </button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-pen-alt mr-1"></i> Ajukan
            </button>
        </div>
    </div>
</form>

<style>
    /* Paksa warna chip ruangan menjadi Primary (Biru) untuk semua tema Select2 */
    .select2-selection__choice {
        background-color: #007bff !important;
        border-color: #006fe6 !important;
        color: #ffffff !important;
    }
    
    /* Paksa warna tombol silang (x) pada chip */
    .select2-selection__choice__remove {
        color: #ffffff !important;
        margin-right: 5px !important;
        border-right: 1px solid #006fe6 !important; /* Tambahan garis pembatas agar lebih rapi */
    }
    
    .select2-selection__choice__remove:hover {
        background-color: #006fe6 !important;
        color: #ffdddd !important;
    }
</style>

<script>
    $(document).ready(function() {
        // Status ruangan per-tanggal (poin 2) — informational only, tidak memblokir submit.
        const badgeWarna = { 'Tersedia': 'success', 'Diajukan': 'warning', 'Tidak Tersedia': 'danger' };

        // Init Select2 for Organisasi
        $('.select2-organisasi').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#form-tambah').closest('.modal'),
            placeholder: "- Pilih Organisasi -",
            allowClear: true,
            width: '100%'
        });

        // Init Select2 for Ketua Pelaksana
        $('.select2-kapel').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#form-tambah').closest('.modal'),
            placeholder: "- Pilih Ketua Pelaksana -",
            allowClear: true,
            width: '100%'
        });

        // Init Select2 for Ruangan
        $('.select2-ruangan').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#form-tambah').closest('.modal'),
            placeholder: "- Pilih Ruangan -",
            allowClear: true,
            width: '100%'
        });

        function muatStatusRuangan(tanggal) {
            const $ruanganSelect = $('.select2-ruangan');
            
            // Kosongkan dropdown dan nonaktifkan sementara memuat
            $ruanganSelect.empty().prop('disabled', true);
            
            // Reset tabel ketersediaan (jika masih ingin dipakai)
            $('#ruangan-availability').hide();
            $('#ruangan-availability-body').empty();

            if (!tanggal) {
                $ruanganSelect.select2({ placeholder: "- Pilih Tanggal Terlebih Dahulu -", width: '100%' });
                return;
            }

            $ruanganSelect.select2({ placeholder: "Memuat ruangan...", width: '100%' });

            $.ajax({
                url: '{{ url('/ruangan/availability_ajax') }}',
                type: 'GET',
                data: { tanggal: tanggal },
                dataType: 'json',
                success: function(data) {
                    let adaRuanganTersedia = false;
                    let adaRuanganTerpakai = false; // Tambahan: Deteksi apakah ada tabel yang harus diisi
                    
                    // Tambahkan elemen option kosong di awal agar placeholder Select2 berfungsi
                    $ruanganSelect.append(new Option('', '', false, false));

                    const body = $('#ruangan-availability-body');

                    data.forEach(function(r) {
                        // Hanya tampilkan di tabel jika statusnya 'Tidak Tersedia' atau 'Diajukan'
                        if (r.status === 'Tidak Tersedia' || r.status === 'Diajukan') {
                            const warna = badgeWarna[r.status] || 'secondary';
                            body.append(
                                '<tr><td>' + r.ruangan_nama + '</td>' +
                                '<td class="text-right"><span class="badge badge-' + warna + '">' + r.status + '</span></td></tr>'
                            );
                            adaRuanganTerpakai = true; // Tandai bahwa ada ruangan yang terpakai
                        }

                        // Logika filter dropdown Select2: HANYA masukkan jika statusnya 'Tersedia'
                        if (r.status === 'Tersedia') {
                            const option = new Option(r.ruangan_nama, r.ruangan_id, false, false);
                            $ruanganSelect.append(option);
                            adaRuanganTersedia = true;
                        }
                    });

                    // PERBAIKAN: Jika semua ruangan berstatus 'Tersedia', beri pesan rapi
                    if (!adaRuanganTerpakai) {
                        body.append('<tr><td colspan="2" class="text-center text-success">Semua ruangan tersedia pada tanggal ini <i class="fas fa-check"></i></td></tr>');
                    }
                    
                    $('#ruangan-availability').show(); // Tampilkan tabel status

                    if (adaRuanganTersedia) {
                        $ruanganSelect.prop('disabled', false);
                        $ruanganSelect.select2({ placeholder: "- Pilih Ruangan -", width: '100%' });
                    } else {
                        $ruanganSelect.select2({ placeholder: "- Tidak ada ruangan tersedia di tanggal ini -", width: '100%' });
                    }

                    // Beritahu Select2 bahwa opsi telah berubah
                    $ruanganSelect.trigger('change');
                },
                error: function(xhr) {
                    console.error('Gagal memuat status ruangan:', xhr.responseText);
                    $ruanganSelect.select2({ placeholder: "- Gagal memuat ruangan -", width: '100%' });
                }
            });
        }

        /*function muatStatusRuangan(tanggal) {
            if (!tanggal) {
                $('#ruangan-availability').hide();
                return;
            }

            $.ajax({
                url: '{{ url('/ruangan/availability_ajax') }}',
                type: 'GET',
                data: { tanggal: tanggal },
                dataType: 'json',
                success: function(data) {
                    const body = $('#ruangan-availability-body');
                    body.empty();
                    data.forEach(function(r) {
                        const warna = badgeWarna[r.status] || 'secondary';
                        body.append(
                            '<tr><td>' + r.ruangan_nama + '</td>' +
                            '<td class="text-right"><span class="badge badge-' + warna + '">' + r.status + '</span></td></tr>'
                        );
                    });
                    $('#ruangan-availability').show();
                },
                error: function(xhr) {
                    console.error('Gagal memuat status ruangan:', xhr.responseText);
                }
            });
        }*/

        $(document).on('change', 'input[name="pengajuan_tgl"]', function() {
            muatStatusRuangan($(this).val());
        });

        // Panitia: daftar kandidat diambil ulang tiap organisasi_id berganti, baris lama direset
        // karena kandidat lama (organisasi sebelumnya) sudah tidak relevan.
        let panitiaOptions = [];

        function opsiPanitiaHtml() {
            let html = '<option value="">- Pilih Panitia -</option>';
            panitiaOptions.forEach(function(p) {
                html += '<option value="' + p.user_id + '">' + p.nama + '</option>';
            });
            return html;
        }

        function tambahBarisPanitia() {
            const row = $('<tr></tr>');
            row.append('<td><select class="form-control form-control-sm select2-panitia" name="panitia_user_id[]" style="width:100%">' + opsiPanitiaHtml() + '</select></td>');
            row.append('<td><input type="text" class="form-control form-control-sm" name="panitia_keterangan[]" maxlength="100" placeholder="Contoh: Koordinator Acara"></td>');
            row.append('<td><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-panitia"><i class="fas fa-trash"></i></button></td>');
            $('#panitia-rows').append(row);

            row.find('.select2-panitia').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#form-tambah').closest('.modal'),
                placeholder: '- Pilih Panitia -',
                allowClear: true,
                width: '100%'
            });
        }

        $(document).on('click', '#btn-tambah-panitia', function() {
            tambahBarisPanitia();
        });

        $(document).on('click', '.btn-hapus-panitia', function() {
            $(this).closest('tr').remove();
        });

        $(document).on('change', 'select[name="organisasi_id"]', function() {
            const organisasiId = $(this).val();
            $('#panitia-rows').empty();

            if (!organisasiId) {
                panitiaOptions = [];
                $('#btn-tambah-panitia').prop('disabled', true);
                $('#panitia-hint').text('Pilih Organisasi Pengaju terlebih dahulu.').show();
                return;
            }

            $('#panitia-hint').text('Memuat daftar anggota organisasi...').show();
            $('#btn-tambah-panitia').prop('disabled', true);

            $.ajax({
                url: '{{ url('pengajuan/panitia_by_organisasi') }}/' + organisasiId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    panitiaOptions = data;
                    $('#btn-tambah-panitia').prop('disabled', panitiaOptions.length === 0);
                    $('#panitia-hint').text(panitiaOptions.length ? '' : 'Belum ada mahasiswa anggota organisasi ini.').toggle(panitiaOptions.length === 0);
                },
                error: function(xhr) {
                    console.error('Gagal memuat daftar panitia:', xhr.responseText);
                    $('#panitia-hint').text('Gagal memuat daftar anggota organisasi.').show();
                }
            });
        });

        $(document).on('submit', '#form-tambah', function(e) {
            e.preventDefault();
            $('.error-text').text('');

            $.ajax({
                url: '{{ url('pengajuan/ajax') }}',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message, timer: 2000, showConfirmButton: false })
                            .then(() => { $('#modalPengajuan').modal('hide'); location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: response.message || 'Gagal mengajukan peminjaman.' });
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.msgField) {
                        let errors = xhr.responseJSON.msgField;
                        $.each(errors, function(key, value) {
                            $('#error_' + key).text(value[0]);
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Terjadi kesalahan pada server.' });
                        console.error('AJAX Error:', xhr.responseText);
                    }
                }
            });
            return false;
        });
    });
</script>
