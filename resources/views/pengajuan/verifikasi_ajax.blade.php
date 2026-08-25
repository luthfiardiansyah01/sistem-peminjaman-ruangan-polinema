@php
    $aksiPrefix = Auth::user()->getRole() === 'ADM' ? '/penyelesaian/' : '/pengajuan/';
@endphp
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
@php
    $isAdmView = Auth::user()->getRole() === 'ADM';
    $user = $pengajuan->user;
    $statusPeminjam = optional($user->level)->level_nama ?? '-';

    // 1. CARI DATA JADWAL UNTUK MENGAMBIL FOTO DOKUMENTASI
    $jadwal = \App\Models\JadwalModel::where('user_id', $pengajuan->user_id)
                ->where('jadwal_nama', $pengajuan->pengajuan_nama)
                ->whereDate('jadwal_tgl', \Carbon\Carbon::parse($pengajuan->pengajuan_tgl)->format('Y-m-d'))
                ->first();

    $dokumentasi = [
        'Foto Pelaksanaan Kegiatan' => $jadwal->foto_penggunaan ?? null,
        'Foto Pembersihan Ruangan' => $jadwal->foto_kebersihan ?? null,
        'Foto Pengembalian Kunci' => $jadwal->foto_kunci ?? null,
    ];
@endphp
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" style="font-weight: bold;">{{ $isAdmView ? 'Verifikasi Penyelesaian' : 'Verifikasi Pengajuan' }}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            
            {{-- ROW 1: UMUM --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Ruangan</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-door-open text-primary mr-2"></i>
                        <span>{{ $pengajuan->ruangans->pluck('ruangan_nama')->implode(', ') }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Nama Peminjam</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-circle text-primary mr-2"></i>
                        <span>{{ $user->getDisplayName() }}</span>
                    </div>
                </div>
            </div>

            {{-- ROW 2: UMUM --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Tanggal</label>
                    <div class="d-flex align-items-center">
                        <i class="far fa-calendar text-primary mr-2"></i>
                        <span>{{ \Carbon\Carbon::parse($pengajuan->pengajuan_tgl)->locale('id')->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Status Peminjam</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user text-primary mr-2"></i>
                        <span>{{ $statusPeminjam }}</span>
                    </div>
                </div>
            </div>

            {{-- BLOK PEMISAHAN TAMPILAN BERDASARKAN ROLE MAHASISWA VS DOSEN/TENDIK --}}
            @if($user?->mahasiswa)
            
            {{-- TAMPILAN KHUSUS MAHASISWA --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="font-weight-bold text-dark">Jam Mulai</label>
                    <div class="d-flex align-items-center">
                        <i class="far fa-clock text-primary mr-2"></i>
                        <span>{{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_mulai)->format('H:i') }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold text-dark">Jam Selesai</label>
                    <div class="d-flex align-items-center">
                        <i class="far fa-clock text-primary mr-2"></i>
                        <span>{{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_selesai)->format('H:i') }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Organisasi Peminjam</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-users-cog text-primary mr-2"></i>
                        <span>{{ $pengajuan->organisasi?->organisasi_nama ?? '-' }}</span>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Kegiatan/Acara</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-thumbtack text-primary mr-2"></i>
                        <span>{{ $pengajuan->pengajuan_nama }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Program Studi</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-swatchbook text-primary mr-2"></i>
                        <span>{{ $user->mahasiswa->prodi?->prodi_nama ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Peserta</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-hand-paper text-primary mr-2"></i>
                        <span>{{ $pengajuan->pengajuan_jumPes }} Orang</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Kelas</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-book text-primary mr-2"></i>
                        <span>{{ $user->mahasiswa->kelas?->kelas_nama ?? '-' }}</span>
                    </div>
                </div>
            </div>

            @else
            
            {{-- TAMPILAN KHUSUS DOSEN / TENDIK / ADMIN --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="font-weight-bold text-dark">Jam Mulai</label>
                    <div class="d-flex align-items-center">
                        <i class="far fa-clock text-primary mr-2"></i>
                        <span>{{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_mulai)->format('H:i') }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold text-dark">Jam Selesai</label>
                    <div class="d-flex align-items-center">
                        <i class="far fa-clock text-primary mr-2"></i>
                        <span>{{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_selesai)->format('H:i') }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Program Studi</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-swatchbook text-primary mr-2"></i>
                        <span>{{ $user->dosen?->prodi?->prodi_nama ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Kegiatan/Acara</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-thumbtack text-primary mr-2"></i>
                        <span>{{ $pengajuan->pengajuan_nama }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Kelas</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-book text-primary mr-2"></i>
                        <span>-</span>
                    </div>
                </div>
            </div>

            @endif
            
            {{-- ROW BAWAH: UMUM --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Peserta</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-hand-paper text-primary mr-2"></i>
                        <span>{{ $pengajuan->pengajuan_jumPes }} Orang</span>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="font-weight-bold text-dark">Ketua Pelaksana</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-tie text-primary mr-2"></i>
                        <span>{{ $pengajuan->ketuaPelaksana?->getDisplayName() ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="font-weight-bold text-dark">Keterangan</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-align-left text-primary mr-2"></i>
                        <span>{{ $pengajuan->pengajuan_keterangan ?? '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- DOKUMENTASI FOTO --}}
            @if(collect($dokumentasi)->filter()->isNotEmpty())
            
            <hr>
            <div class="row mb-2">
                <div class="col-md-12">
                    <label class="font-weight-bold text-dark">
                        <i class="fas fa-images mr-1 text-primary"></i>
                        Dokumentasi Kegiatan
                    </label>
                </div>
            </div>

            <div class="row mb-3">
                @foreach($dokumentasi as $label => $file)
                    <div class="col-md-4 text-center mb-3">
                        <label class="text-dark d-block">{{ $label }}</label>
                        @if($file)
                            <a href="{{ asset('storage/'.$file) }}" target="_blank" title="Lihat ukuran penuh">
                                <img src="{{ asset('storage/'.$file) }}" alt="{{ $label }}" class="img-thumbnail mb-2" style="width:100%; max-width:180px; height:130px; object-fit:cover;">
                            </a>
                            <br>
                            <a href="{{ asset('storage/'.$file) }}" download class="btn btn-outline-import btn-sm">
                                <i class="fas fa-download"></i> Unduh
                            </a>
                        @else
                            <div class="text-muted small">
                                <i class="fas fa-image"></i><br>Belum diunggah
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            
            <hr>
            @endif

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Alasan Penolakan <small class="text-muted">(isi jika menolak)</small></label>
                        <textarea class="form-control" id="catatan_verifikator" placeholder="Alasan penolakan penyelesaian peminjaman ruangan (mis. dokumentasi tidak sesuai, dsb)."></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <a href="{{ url('/pengajuan/' . $pengajuan->pengajuan_id . '/cetak_surat_ajax') }}" class="btn btn-import">
                <i class="fas fa-print mr-1"></i> Cetak Surat
            </a>
            <button type="button" class="btn btn-danger" onclick="prosesLegacy('{{ url($aksiPrefix . $pengajuan->pengajuan_id . '/tolak_ajax') }}')">
                <i class="fas fa-times mr-1"></i> Tolak
            </button>
            <button type="button" class="btn btn-success" onclick="prosesLegacy('{{ url($aksiPrefix . $pengajuan->pengajuan_id . '/terima_ajax') }}')">
                <i class="fas fa-check mr-1"></i> Terima
            </button>
        </div>
    </div>

    <script>
        function prosesLegacy(url) {
            $.ajax({
                url: url,
                type: 'PUT',
                data: { catatan_verifikator: $('#catatan_verifikator').val(), _token: '{{ csrf_token() }}' },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message, timer: 2000, showConfirmButton: false })
                            .then(() => { $('#modalPengajuan').modal('hide'); location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: response.message });
                    }
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Terjadi kesalahan pada server.' });
                    console.error('AJAX Error:', xhr.responseText);
                }
            });
        }
    </script>
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
.btn-outline-import {
    border-color: #ff851b;
    color: #ff851b;
}
.btn-outline-import:hover {
    background-color: #ff851b;
    color: #fff;
}
</style>