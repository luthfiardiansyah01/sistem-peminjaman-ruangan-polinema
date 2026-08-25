@empty($pengajuan)
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
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" style="font-weight: bold;">Data Pengajuan</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            {{-- ROW 1 --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Ruangan</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-door-open mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $pengajuan->ruangans->pluck('ruangan_nama')->implode(', ') }}</span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Nama Peminjam</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-circle mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $pengajuan->user?->getDisplayName() }}</span>
                    </div>
                </div>
            </div>

            {{-- ROW 2 --}}
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="font-weight-bold text-dark">Tanggal Pengajuan</label>
                    <div class="d-flex align-items-center">
                        <i class="far fa-calendar mr-2 text-primary"></i>
                        <span class="text-secondary">{{ \Carbon\Carbon::parse($pengajuan->created_at)->locale('id')->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="font-weight-bold text-dark">Tanggal Peminjaman</label>
                    <div class="d-flex align-items-center">
                        <i class="far fa-calendar mr-2 text-primary"></i>
                        <span class="text-secondary">{{ \Carbon\Carbon::parse($pengajuan->pengajuan_tgl)->locale('id')->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Status Peminjam</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $pengajuan->user?->level?->level_nama ?? '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- ROW 3 MAHASISWA--}}
            @if($pengajuan->user?->mahasiswa)
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="font-weight-bold text-dark">Jam Mulai</label>
                    <div class="d-flex align-items-center">
                        <i class="far fa-clock mr-2 text-primary"></i>
                        <span class="text-secondary">{{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_mulai)->format('H:i') }}</span>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="font-weight-bold text-dark">Jam Selesai</label>
                    <div class="d-flex align-items-center">
                        <i class="far fa-clock mr-2 text-primary"></i>
                        <span class="text-secondary">{{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_selesai)->format('H:i') }}</span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Organisasi Peminjam</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-users-cog mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $pengajuan->organisasi?->organisasi_nama ?? '-' }}</span>
                    </div>
                </div>
            </div>
            {{-- ROW 4 MAHASISWA --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Kegiatan/Acara</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-thumbtack mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $pengajuan->pengajuan_nama }}</span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Program Studi</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-swatchbook mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $pengajuan->user?->mahasiswa?->prodi?->prodi_nama ?? '-' }}</span>
                    </div>
                </div>
            </div>
            {{-- ROW 5 MAHASISWA --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Jumlah Peserta</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-hand-paper mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $pengajuan->pengajuan_jumPes }}</span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Kelas</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-book mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $pengajuan->user?->mahasiswa?->kelas?->kelas_nama ?? '-' }}</span>
                    </div>
                </div>
            </div>
            {{-- ROW 6 MAHASISWA --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Ketua Pelaksana</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-tie mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $pengajuan->ketuaPelaksana?->getDisplayName() ?? '-' }}</span>
                    </div>
                </div>
            </div>
            {{-- ROW 7 MAHASISWA --}}
            <!--div class="row">
                @if($pengajuan->catatan_verifikator)
                <div class="col-md-12 mb-3">
                    <label class="font-weight-bold text-dark">Catatan Verifikator</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-align-left mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $pengajuan->catatan_verifikator }}</span>
                    </div>
                </div>
                @endif
            </div-->

            @else
            {{-- ROW 3 --}}
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="font-weight-bold text-dark">Jam Mulai</label>
                    <div class="d-flex align-items-center">
                        <i class="far fa-clock mr-2 text-primary"></i>
                        <span class="text-secondary">{{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_mulai)->format('H:i') }}</span>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="font-weight-bold text-dark">Jam Selesai</label>
                    <div class="d-flex align-items-center">
                        <i class="far fa-clock mr-2 text-primary"></i>
                        <span class="text-secondary">{{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_selesai)->format('H:i') }}</span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Program Studi</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-swatchbook mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $pengajuan->user?->dosen?->prodi?->prodi_nama ?? '-' }}</span>
                         {{-- @php
                            $prodi = '-';
                            if($user->mahasiswa) $prodi = $user->mahasiswa->prodi->prodi_nama ?? '-';
                            elseif($user->dosen) $prodi = $user->dosen->prodi->prodi_nama ?? '-';
                         @endphp --}}
                    </div>
                </div>
            </div>

            {{-- ROW 4 --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Kegiatan/Acara</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-thumbtack mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $pengajuan->pengajuan_nama }}</span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Kelas</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-book mr-2 text-primary"></i>
                        <span class="text-secondary">-</span>
                    </div>
                </div>
            </div>

            {{-- ROW 5 --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Jumlah Peserta</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-hand-paper mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $pengajuan->pengajuan_jumPes }}</span>
                    </div>
                </div>
            </div>
            
            {{-- ROW 6 --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Ketua Pelaksana</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-tie mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $pengajuan->ketuaPelaksana?->getDisplayName() ?? '-' }}</span>
                    </div>
                </div>
            </div>
            @endif

            {{-- ROW 7 --}}
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="font-weight-bold text-dark">Keterangan</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-align-left mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $pengajuan->pengajuan_keterangan ?? '-' }}</span>
                    </div>
                </div>
            </div>
            
            {{-- ROW 8 --}}
            <div class="row">
                @if($pengajuan->catatan_verifikator && $pengajuan->pengajuan_status === 'Ditolak')
                <div class="col-md-12 mb-3">
                    <label class="font-weight-bold text-dark">Catatan Verifikator</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $pengajuan->catatan_verifikator }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
        <!--div class="modal-body">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="font-weight-bold text-dark">Nama Kegiatan</label>
                    <div class="text-secondary">{{ $pengajuan->pengajuan_nama }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Tanggal</label>
                    <div class="text-secondary">{{ \Carbon\Carbon::parse($pengajuan->pengajuan_tgl)->locale('id')->translatedFormat('d F Y') }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Jam</label>
                    <div class="text-secondary">{{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_selesai)->format('H:i') }}</div>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="font-weight-bold text-dark">Ruangan</label>
                    <div class="text-secondary">{{ $pengajuan->ruangans->pluck('ruangan_nama')->implode(', ') }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Status Pengajuan</label>
                    <div class="text-secondary">{{ $pengajuan->pengajuan_status }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Diajukan Oleh</label>
                    <div class="text-secondary">{{ $pengajuan->user->getDisplayName() }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Ketua Pelaksana</label>
                    <div class="text-secondary">{{ optional($pengajuan->ketuaPelaksana)->getDisplayName() ?? '-' }}</div>
                </div>
                @if($pengajuan->catatan_verifikator)
                <div class="col-md-12 mb-3">
                    <label class="font-weight-bold text-dark">Catatan Verifikator</label>
                    <div class="text-secondary">{{ $pengajuan->catatan_verifikator }}</div>
                </div>
                @endif
            </div>
        </div-->
        <div class="modal-footer">
            <button type="button" data-dismiss="modal" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </button>
            @if($pengajuan->user_id === Auth::id())
            <button type="button" onclick="showTimeline(this, '{{ $pengajuan->pengajuan_id }}')" class="btn btn-info">
                <i class="fas fa-list-ol mr-1"></i> Lihat Timeline Pengajuan
            </button>
            @endif
            @if($pengajuan->pengajuan_status === 'Diterima')
            <a href="{{ url('/pengajuan/' . $pengajuan->pengajuan_id . '/cetak_surat_ajax') }}" class="btn btn-import">
                <i class="fas fa-print mr-1"></i> Cetak Surat
            </a>
            @endif
        </div>
    </div>
@endempty

<style>
    .btn-import {
        background-color: #ff851b;
        color: #fff;
    }
    .btn-import:hover {
        background-color: #e07415; /* Darker shade of orange */
        color: #fff;
    }
</style>

<script>
    function showTimeline(element, id) {
        // 1. Tangkap elemen tombol dan simpan tampilan aslinya
        var btn = $(element);
        var originalHtml = btn.html();
        //btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Memuat...').prop('disabled', true);

        //var id = "{{ $pengajuan->pengajuan_id }}";
        var url = "{{ url('/pengajuan') }}/" + id + "/timeline_ajax";
        
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                // Menimpa modal-content saat ini
                btn.closest('.modal-content').parent().html(response);
            },
            error: function(xhr) {
                console.error("Detail Error Server:", xhr.responseText);
                alert("Gagal memuat timeline. Tekan F12 -> tab Console untuk melihat detail error.");
            },
            complete: function() {
                btn.html(originalHtml).prop('disabled', false);
            }
        });
    }
</script>