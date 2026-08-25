<form action="{{ url('pengajuan/' . $pengajuan->pengajuan_id . '/update_ajax') }}" method="POST" id="form-edit-pengajuan">
    @csrf
    @method('PUT')
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" style="font-weight: bold;">Edit Pengajuan Peminjaman Ruangan</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Nama Kegiatan</label>
                        <input type="text" class="form-control" name="pengajuan_nama" value="{{ $pengajuan->pengajuan_nama }}" required>
                        <small id="error_pengajuan_nama" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Organisasi Pengaju</label>
                        <select class="form-control select2-organisasi" name="organisasi_id" required>
                            <option value="">- Pilih Organisasi -</option>
                            @foreach($organisasiList as $o)
                                <option value="{{ $o->organisasi_id }}" {{ $pengajuan->organisasi_id == $o->organisasi_id ? 'selected' : '' }}>{{ $o->organisasi_nama }}</option>
                            @endforeach
                        </select>
                        <small id="error_organisasi_id" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Ketua Pelaksana</label>
                        <select class="form-control select2-kapel" name="ketua_pelaksana_user_id" required>
                            <option value="">- Pilih Ketua Pelaksana -</option>
                            @foreach($ketuaPelaksanaList as $k)
                                <option value="{{ $k->user_id }}" {{ $pengajuan->ketua_pelaksana_user_id == $k->user_id ? 'selected' : '' }}>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                        <small id="error_ketua_pelaksana_user_id" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" class="form-control" name="pengajuan_tgl" value="{{ \Carbon\Carbon::parse($pengajuan->pengajuan_tgl)->format('Y-m-d') }}" required>
                        <small id="error_pengajuan_tgl" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Jam Mulai</label>
                        <input type="time" class="form-control" name="pengajuan_jam_mulai" value="{{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_mulai)->format('H:i') }}" required>
                        <small id="error_pengajuan_jam_mulai" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Jam Selesai</label>
                        <input type="time" class="form-control" name="pengajuan_jam_selesai" value="{{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_selesai)->format('H:i') }}" required>
                        <small id="error_pengajuan_jam_selesai" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Jumlah Peserta</label>
                        <input type="number" class="form-control" name="pengajuan_jumPes" value="{{ $pengajuan->pengajuan_jumPes }}" required>
                        <small id="error_pengajuan_jumPes" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Ruangan</label>
                        @php $ruanganTerpilih = $pengajuan->ruangans->pluck('ruangan_id')->toArray(); @endphp
                        <select class="form-control select2-ruangan" name="ruangan_ids[]" data-placeholder="- Pilih Ruangan -" multiple required>
                            @foreach($ruanganList as $r)
                                <option value="{{ $r->ruangan_id }}" {{ in_array($r->ruangan_id, $ruanganTerpilih) ? 'selected' : '' }}>{{ $r->ruangan_nama }}</option>
                            @endforeach
                        </select>
                        <small id="error_ruangan_ids" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-12">
                    <div id="ruangan-availability" style="display:none;">
                        <label class="font-weight-bold text-dark small">Status Ruangan pada Tanggal Terpilih</label>
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
                        <small class="form-text text-muted" id="panitia-hint"></small>
                        <small id="error_panitia_user_id" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea class="form-control" name="pengajuan_keterangan">{{ $pengajuan->pengajuan_keterangan }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-warning">Simpan</button>
        </div>
    </div>
</form>

<script>
    $(document).ready(function() {
        const badgeWarna = { 'Tersedia': 'success', 'Diajukan': 'warning', 'Tidak Tersedia': 'danger' };

        $('.select2-organisasi').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#form-edit-pengajuan').closest('.modal'),
            placeholder: "- Pilih Organisasi -",
            allowClear: true,
            width: '100%'
        });

        $('.select2-kapel').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#form-edit-pengajuan').closest('.modal'),
            placeholder: "- Pilih Ketua Pelaksana -",
            allowClear: true,
            width: '100%'
        });

        $('.select2-ruangan').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#form-edit-pengajuan').closest('.modal'),
            placeholder: "- Pilih Ruangan -",
            allowClear: true,
            width: '100%'
        });

        function muatStatusRuangan(tanggal) {
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
                    let adaRuanganTerpakai = false;
                    data.forEach(function(r) {
                        // Hanya tampilkan di tabel jika statusnya 'Tidak Tersedia' atau 'Diajukan'
                        // (konsisten dengan create_ajax.blade.php) supaya form tidak terlalu panjang.
                        if (r.status === 'Tidak Tersedia' || r.status === 'Diajukan') {
                            const warna = badgeWarna[r.status] || 'secondary';
                            body.append(
                                '<tr><td>' + r.ruangan_nama + '</td>' +
                                '<td class="text-right"><span class="badge badge-' + warna + '">' + r.status + '</span></td></tr>'
                            );
                            adaRuanganTerpakai = true;
                        }
                    });
                    if (!adaRuanganTerpakai) {
                        body.append('<tr><td colspan="2" class="text-center text-success">Semua ruangan tersedia pada tanggal ini <i class="fas fa-check"></i></td></tr>');
                    }
                    $('#ruangan-availability').show();
                },
                error: function(xhr) {
                    console.error('Gagal memuat status ruangan:', xhr.responseText);
                }
            });
        }

        $(document).on('change', '#form-edit-pengajuan input[name="pengajuan_tgl"]', function() {
            muatStatusRuangan($(this).val());
        });

        // Panitia — sama seperti create_ajax.blade.php, tapi baris awal diisi dari
        // panitia yang sudah tersimpan ($pengajuan->panitias).
        let panitiaOptions = [];
        const panitiaTersimpan = @json($pengajuan->panitias->map(fn ($p) => ['user_id' => $p->user_id, 'keterangan' => $p->keterangan])->values());

        function opsiPanitiaHtml(selectedUserId) {
            let html = '<option value="">- Pilih Panitia -</option>';
            panitiaOptions.forEach(function(p) {
                const selected = String(p.user_id) === String(selectedUserId) ? 'selected' : '';
                html += '<option value="' + p.user_id + '" ' + selected + '>' + p.nama + '</option>';
            });
            return html;
        }

        function tambahBarisPanitia(selectedUserId, keterangan) {
            const row = $('<tr></tr>');
            row.append('<td><select class="form-control form-control-sm select2-panitia" name="panitia_user_id[]" style="width:100%">' + opsiPanitiaHtml(selectedUserId) + '</select></td>');
            row.append('<td><input type="text" class="form-control form-control-sm" name="panitia_keterangan[]" maxlength="100" value="' + (keterangan || '') + '" placeholder="Contoh: Koordinator Acara"></td>');
            row.append('<td><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-panitia"><i class="fas fa-trash"></i></button></td>');
            $('#panitia-rows').append(row);

            row.find('.select2-panitia').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#form-edit-pengajuan').closest('.modal'),
                placeholder: '- Pilih Panitia -',
                allowClear: true,
                width: '100%'
            });
        }

        $(document).on('click', '#btn-tambah-panitia', function() {
            tambahBarisPanitia(null, '');
        });

        $(document).on('click', '.btn-hapus-panitia', function() {
            $(this).closest('tr').remove();
        });

        function muatPanitia(organisasiId, isiUlangDariTersimpan) {
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

                    if (isiUlangDariTersimpan && panitiaTersimpan.length) {
                        panitiaTersimpan.forEach(function(p) {
                            tambahBarisPanitia(p.user_id, p.keterangan);
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Gagal memuat daftar panitia:', xhr.responseText);
                    $('#panitia-hint').text('Gagal memuat daftar anggota organisasi.').show();
                }
            });
        }

        $(document).on('change', '#form-edit-pengajuan select[name="organisasi_id"]', function() {
            muatPanitia($(this).val(), false);
        });

        muatPanitia('{{ $pengajuan->organisasi_id }}', true);

        $(document).on('submit', '#form-edit-pengajuan', function(e) {
            e.preventDefault();
            $('.error-text').text('');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message, timer: 2000, showConfirmButton: false })
                            .then(() => { $('#modalPengajuan').modal('hide'); location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: response.message || 'Gagal memperbarui pengajuan.' });
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.msgField) {
                        let errors = xhr.responseJSON.msgField;
                        $.each(errors, function(key, value) {
                            $('#error_' + key).text(value[0]);
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan pada server.' });
                        console.error('AJAX Error:', xhr.responseText);
                    }
                }
            });
            return false;
        });
    });
</script>
