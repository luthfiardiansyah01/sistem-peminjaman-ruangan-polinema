@empty($jadwal)
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
    @php
        // Mengambil data pengajuan dari fungsi relasi di JadwalModel
        $pengajuan = $jadwal->pengajuanTerkait;
    @endphp
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" style="font-weight: bold;">Data Jadwal</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            
            {{-- ROW 1 --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Ruangan</label>
                    <div class="d-flex align-items-center">
                         <i class="fas fa-door-open mr-2 text-primary"></i>
                         @if($jadwal->ruangans->isNotEmpty())
                            <span class="text-secondary">{{ $jadwal->ruangans->pluck('ruangan_nama')->implode(', ') }}</span>
                         @else
                            <span>-</span>
                         @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Nama Peminjam</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-circle mr-2 text-primary"></i>
                        @php
                             $user = $jadwal->user;
                             $nama = $user->username;
                             if($user->mahasiswa) $nama = $user->mahasiswa->mahasiswa_nama;
                             elseif($user->dosen) $nama = $user->dosen->dosen_nama;
                             elseif($user->tendik) $nama = $user->tendik->tendik_nama;
                             elseif($user->admin) $nama = $user->admin->admin_nama;
                        @endphp
                        <span class="text-secondary">{{ $nama }}</span>
                    </div>
                </div>
            </div>

            {{-- ROW 2 --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Tanggal</label>
                    <div class="d-flex align-items-center">
                         <i class="far fa-calendar mr-2 text-primary"></i>
                         <span class="text-secondary">{{ \Carbon\Carbon::parse($jadwal->jadwal_tgl)->locale('id')->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Status Peminjam</label>
                    <div class="d-flex align-items-center">
                         <i class="fas fa-user mr-2 text-primary"></i>
                         <span class="text-secondary">{{ $jadwal->user->level->level_nama ?? '-' }}</span>
                    </div>
                </div>
            </div>

            @if($jadwal->user?->mahasiswa)
            {{-- ROW 3 MAHASISWA --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="font-weight-bold text-dark">Jam Mulai</label>
                    <div class="d-flex align-items-center">
                         <i class="far fa-clock mr-2 text-primary"></i>
                         <span class="text-secondary">{{ \Carbon\Carbon::parse($jadwal->jadwal_jam_mulai)->format('H:i') }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold text-dark">Jam Selesai</label>
                    <div class="d-flex align-items-center">
                         <i class="far fa-clock mr-2 text-primary"></i>
                         <span class="text-secondary">{{ \Carbon\Carbon::parse($jadwal->jadwal_jam_selesai)->format('H:i') }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Organisasi</label>
                    <div class="d-flex align-items-center">
                         <i class="fas fa-user-cog mr-2 text-primary"></i>
                         <span class="text-secondary">{{ $jadwal->pengajuanTerkait?->organisasi?->organisasi_nama ?? '-' }}</span>
                    </div>
                </div>
            </div>
            {{-- ROW 4 MAHASISWA --}}
            <div class="row mb-3">
                <div class="col-md-6">
                   <label class="font-weight-bold text-dark">Kegiatan/Acara</label>
                   <div class="d-flex align-items-center">
                        <i class="fas fa-thumbtack mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $jadwal->jadwal_nama }}</span>
                   </div>
               </div>
               <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Program Studi</label>
                    <div class="d-flex align-items-center">
                         <i class="fas fa-swatchbook mr-2 text-primary"></i>
                         @php
                            $prodi = '-';
                            if($user->mahasiswa) $prodi = $user->mahasiswa->prodi->prodi_nama ?? '-';
                            elseif($user->dosen) $prodi = $user->dosen->prodi->prodi_nama ?? '-';
                         @endphp
                         <span class="text-secondary">{{ $prodi }}</span>
                    </div>
                </div>
            </div>
            {{-- ROW 5 MAHASISWA --}}
            <div class="row mb-3">
                <div class="col-md-6">
                   <label class="font-weight-bold text-dark">Peserta</label>
                   <div class="d-flex align-items-center">
                        <i class="fas fa-hand-paper mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $jadwal->jadwal_jumPes }} Orang</span>
                   </div>
               </div>
               <div class="col-md-6">
                   <label class="font-weight-bold text-dark">Kelas</label>
                   <div class="d-flex align-items-center">
                        {{-- <i class="fas fa-users-class mr-2 text-primary"></i> --}} {{-- icon placeholder --}}
                        <i class="fas fa-book mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $user->mahasiswa->kelas->kelas_nama ?? '-' }}</span>
                   </div>
               </div>
            </div>

            @else

            {{-- ROW 3 --}}
            <div class="row mb-3">
                 <div class="col-md-3">
                    <label class="font-weight-bold text-dark">Jam Mulai</label>
                    <div class="d-flex align-items-center">
                         <i class="far fa-clock mr-2 text-primary"></i>
                         <span class="text-secondary">{{ \Carbon\Carbon::parse($jadwal->jadwal_jam_mulai)->format('H:i') }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold text-dark">Jam Selesai</label>
                    <div class="d-flex align-items-center">
                         <i class="far fa-clock mr-2 text-primary"></i>
                         <span class="text-secondary">{{ \Carbon\Carbon::parse($jadwal->jadwal_jam_selesai)->format('H:i') }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold text-dark">Program Studi</label>
                    <div class="d-flex align-items-center">
                         <i class="fas fa-swatchbook mr-2 text-primary"></i>
                         @php
                            $prodi = '-';
                            if($user->mahasiswa) $prodi = $user->mahasiswa->prodi->prodi_nama ?? '-';
                            elseif($user->dosen) $prodi = $user->dosen->prodi->prodi_nama ?? '-';
                         @endphp
                         <span class="text-secondary">{{ $prodi }}</span>
                    </div>
                </div>
            </div>

            {{-- ROW 4 --}}
            <div class="row mb-3">
                <div class="col-md-6">
                   <label class="font-weight-bold text-dark">Kegiatan/Acara</label>
                   <div class="d-flex align-items-center">
                        <i class="fas fa-thumbtack mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $jadwal->jadwal_nama }}</span>
                   </div>
               </div>
               <div class="col-md-6">
                   <label class="font-weight-bold text-dark">Kelas</label>
                   <div class="d-flex align-items-center">
                        {{-- <i class="fas fa-users-class mr-2 text-primary"></i> --}} {{-- icon placeholder --}}
                        <i class="fas fa-book mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $user->mahasiswa->kelas->kelas_nama ?? '-' }}</span>
                   </div>
               </div>
            </div>

            {{-- ROW 5 --}}
            <div class="row mb-3">
                <div class="col-md-6">
                   <label class="font-weight-bold text-dark">Peserta</label>
                   <div class="d-flex align-items-center">
                        <i class="fas fa-hand-paper mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $jadwal->jadwal_jumPes }} Orang</span>
                   </div>
               </div>
            </div>
            @endif

            {{-- ROW 6 --}}
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="font-weight-bold text-dark">Ketua Pelaksana</label>
                    <div class="d-flex align-items-center">
                         <i class="fas fa-user-tie mr-2 text-primary"></i>
                         @php
                            if ($pengajuan) {
                                $mhs = $pengajuan->ketuaPelaksana?->mahasiswa;
                                $dsn = $pengajuan->ketuaPelaksana?->dosen;
                                $namaKetua = $mhs ? $mhs->mahasiswa_nama : ($dsn ? $dsn->dosen_nama : $pengajuan->ketuaPelaksana?->username ?? '-');
                            } else {
                                $namaKetua = 'Tidak Ada (Jadwal Manual)';
                            }
                         @endphp
                         
                         <span class="text-secondary">{{ $namaKetua }}</span>
                    </div>
                </div>
            </div>
            <!--div class="row mb-3">
                <div class="col-md-12">
                    <label class="font-weight-bold text-dark">Ketua Pelaksana</label>
                    <div class="d-flex align-items-center">
                         <i class="fas fa-user-tie mr-2 text-primary"></i>
                         <span class="text-secondary">{{ $jadwal->pengajuanTerkait?->ketuaPelaksana?->getDisplayName() ?? '-' }}</span>
                    </div>
                </div>
            </div-->
            {{-- ROW 7 --}}
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="font-weight-bold text-dark">Keterangan</label>
                    <div class="d-flex align-items-center">
                         <i class="fas fa-align-left mr-2 text-primary"></i>
                         <span class="text-secondary">{{ $pengajuan->pengajuan_keterangan ?? '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- ROW 8: Dokumentasi Kegiatan — tampil untuk semua role jika minimal 1 foto ada (Req 4) --}}
            @if($jadwal->foto_penggunaan || $jadwal->foto_kebersihan || $jadwal->foto_kunci)

                <hr>

                <div class="row mb-2">
                    <div class="col-md-12">
                        <label class="font-weight-bold text-dark">
                            <i class="fas fa-images mr-1 text-primary"></i>
                            Dokumentasi
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
                                         class="img-thumbnail mb-2"
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

            @endif

            {{-- ROW 9: Alasan Penolakan — untuk role non-ADM, jika pengajuan terkait berstatus Ditolak --}}
            @if(Auth::user()->getRole() !== \App\Constants\RoleConstants::ADMIN
                && $pengajuan
                && $pengajuan->pengajuan_status === 'Ditolak'
                && $pengajuan->catatan_verifikator)

                <hr>

                <div class="row mb-2">
                    <div class="col-md-12">
                        <label class="font-weight-bold text-danger">
                            <i class="fas fa-times text-danger mr-1"></i>
                            Alasan Penolakan
                        </label>
                        <div class="text-secondary">
                            {{ $pengajuan->catatan_verifikator }}
                        </div>
                    </div>
                </div>
            @endif

        </div>
        <div class="modal-footer">
            <button type="button" data-dismiss="modal" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </button>
            {{-- Munculkan Tombol Cetak Surat untuk Admin ATAU user pemilik jadwal --}}
            @if((Auth::user()->getRole() === 'ADM' || Auth::id() == $jadwal->user_id) && isset($pengajuan))
                <a href="{{ url('/pengajuan/' . $pengajuan->pengajuan_id . '/cetak_surat_ajax') }}" class="btn btn-import" target="_blank">
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
.btn-outline-import {
    border-color: #ff851b;
    color: #ff851b;
}
.btn-outline-import:hover {
    background-color: #ff851b;
    color: #fff;
}
</style>
