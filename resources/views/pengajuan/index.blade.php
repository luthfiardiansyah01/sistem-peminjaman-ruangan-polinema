@extends('layouts.template')

@section('content')

@if(Auth::user()->getRole() !== 'ADM')

{{-- @if(!empty($antrian) && $antrian->isNotEmpty()) --}}
<div class="card" style="box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
    <div class="card-header">
                <div class="d-flex align-items-center gap-2">

                    <div class="d-flex align-items-center">
                        <label class="mr-2 mb-0">Ruangan:</label>

                        <select id="filter_ruangan" class="form-control form-control-sm" style="width: 150px;">

                            <option value="">Semua Ruangan</option>

                            @foreach($ruanganList as $ruangan)
                                <option value="{{ $ruangan->ruangan_id }}">
                                    {{ $ruangan->ruangan_nama }}
                                </option>
                            @endforeach

                        </select>

                    </div>

                    <div class="d-flex align-items-center">
                        <label class="ml-3 mr-2 mb-0">Tahun Ajaran:</label>
                        <select id="filter_tahun_ajaran" class="form-control form-control-sm" style="white-space: nowrap; width: 100px;">
                            <option value="">Semua</option>
                            @foreach($tahunAjaranList as $tahunAjaran)
                                <option value="{{ $tahunAjaran }}">{{ $tahunAjaran }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex align-items-center">
                        <label class="ml-3 mr-2 mb-0">Semester:</label>
                        <select id="filter_semester" class="form-control form-control-sm">
                            <option value="">Semua</option>
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center">
                        <label class="col-form-label mr-2 ml-3 mb-0">Cari:</label>
                        <input type="text" id="pengajuan_search" class="form-control form-control-sm" placeholder="Cari berdasarkan kegiatan/acara" style="width: 220px;">
                        <button type="button" class="btn btn-primary btn-sm mr-2 mb-0">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                    <div class="d-flex align-items-center">

                        <label class="ml-3 mr-2 mb-0">Show</label>

                        <select id="data-table-length" class="form-control form-control-sm">

                            <option>10</option>
                            <option>25</option>
                            <option>50</option>
                            <option>100</option>

                        </select>
                        <label class="ml-2 mb-0">entries</label>
                    </div>

                    @if(in_array(Auth::user()->getRole(), ['DSN', 'TDK', 'MHS']))
                    <button onclick="modalAction('{{ url('/pengajuan/create_ajax') }}')" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Ajukan Peminjaman
                    </button>
                    @endif

                </div>
            </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <table class="table table-sm table-hover mb-0" id="table_antrian">
            <thead style="background-color: #e3e3e3;">
                <tr>
                    <th width="5%">No.</th>
                    <th width="18%">Ruangan</th>
                    <th width="18%">Kegiatan/Acara</th>
                    <th width="17%">Pengaju</th>
                    <th width="17%">Tanggal Kegiatan/Acara</th>
                    <th width="17%">Batas Waktu Verifikasi</th>
                    <th width="8%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($antrian as $key => $a)
                <tr>
                    <td>{{ $key + 1 }}.</td>
                    <td>{{ Str::limit($a->pengajuan->ruangans->pluck('ruangan_nama')->implode(', '), 22, '...') }}</td>
                    <td>{{ Str::limit($a->pengajuan->pengajuan_nama, 22, '...') }}</td>
                    <td>{{ $a->pengajuan->user->getDisplayName() }}
                        @if(optional($a->pengajuan->organisasi)->organisasi_nama)
                            <br><small class="text-muted">({{ $a->pengajuan->organisasi->organisasi_nama }})</small>
                        @endif
                    </td>
                    <td data-order="{{ $a->pengajuan->pengajuan_tgl }}">{{ \Carbon\Carbon::parse($a->pengajuan->pengajuan_tgl)->locale('id')->translatedFormat('d F Y') }}</td>
                    <td>{{ $a->batas_waktu?->locale('id')->translatedFormat('d F Y') }}</td>
                    <td>
                        <button onclick="modalProses({{ json_encode([
                            'approval_id' => $a->approval_id,
                            'ruangan' => $a->pengajuan->ruangans->pluck('ruangan_nama')->implode(', '),
                            'peminjam' => $a->pengajuan->user->getDisplayName(),
                            'tanggal_pengajuan' => \Carbon\Carbon::parse($a->pengajuan->pengajuan_tgl)->locale('id')->translatedFormat('d F Y'),
                            'tanggal_peminjaman' => \Carbon\Carbon::parse($a->pengajuan->pengajuan_tgl)->locale('id')->translatedFormat('d F Y'),
                            'status' => $a->pengajuan->user?->level?->level_nama ?? '-',
                            'prodi' => $a->pengajuan->user?->mahasiswa?->prodi?->prodi_nama ?? $a->pengajuan->user?->dosen?->prodi?->prodi_nama ?? '-', 
                            'kelas' => $a->pengajuan->user?->mahasiswa?->kelas?->kelas_nama ?? '-',
                            'jam_mulai' => \Carbon\Carbon::parse($a->pengajuan->pengajuan_jam_mulai)->format('H:i'),
                            'jam_selesai' => \Carbon\Carbon::parse($a->pengajuan->pengajuan_jam_selesai)->format('H:i'),
                            'kegiatan' => $a->pengajuan->pengajuan_nama,
                            'peserta' => $a->pengajuan->pengajuan_jumPes,
                            'organisasi' => $a->pengajuan->organisasi?->organisasi_nama ?? '-',
                            'ketua_pelaksana' => $a->pengajuan->ketuaPelaksana?->getDisplayName() ?? '-',
                            'keterangan' => $a->pengajuan->pengajuan_keterangan,
                            'is_mahasiswa' => (bool)$a->pengajuan->user?->mahasiswa
                        ]) }})" class="btn btn-outline-warning btn-sm" title="Verifikasi">
                            <i class="fas fa-file-signature"></i>
                        </button>
                        <!--button onclick="modalProses({{ $a->approval_id }}, '{{ addslashes($a->pengajuan->pengajuan_nama) }}')" class="btn btn-outline-warning btn-sm" title="Verifikasi">
                            <i class="fas fa-file-signature"></i>
                        </button-->
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">
                        Belum ada data pengajuan yang perlu Anda verifikasi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Proses Approval --}}
<div class="modal fade" id="modalProsesApproval" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-weight:bold;">Verifikasi Pengajuan</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <!--p id="proses_nama_kegiatan" class="font-weight-bold"></p-->
                {{-- ROW 1 --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-dark">Ruangan</label>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-door-open mr-2 text-primary"></i>
                            <span class="text-secondary" id="detail_ruangan">-</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-dark">Nama Peminjam</label>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-circle mr-2 text-primary"></i>
                            <span class="text-secondary" id="detail_peminjam">-</span>
                        </div>
                    </div>
                </div>

                {{-- ROW 2 --}}
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark">Tanggal Pengajuan</label>
                        <div class="d-flex align-items-center">
                            <i class="far fa-calendar mr-2 text-primary"></i>
                            <span class="text-secondary" id="detail_tanggal_pengajuan">-</span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark">Tanggal Peminjaman</label>
                        <div class="d-flex align-items-center">
                            <i class="far fa-calendar mr-2 text-primary"></i>
                            <span class="text-secondary" id="detail_tanggal_peminjaman">-</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-dark">Status Peminjam</label>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user mr-2 text-primary"></i>
                            <span class="text-secondary" id="detail_status_peminjam">-</span>
                        </div>
                    </div>
                </div>

                {{-- ROW 3 MAHASISWA--}}
                <div id="section_mahasiswa" style="display: none;">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold text-dark">Jam Mulai</label>
                            <div class="d-flex align-items-center">
                                <i class="far fa-clock mr-2 text-primary"></i>
                                <span class="text-secondary detail_jam_mulai">-</span>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold text-dark">Jam Selesai</label>
                            <div class="d-flex align-items-center">
                                <i class="far fa-clock mr-2 text-primary"></i>
                                <span class="text-secondary detail_jam_selesai">-</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-dark">Organisasi Peminjam</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-users-cog mr-2 text-primary"></i>
                                <span class="text-secondary detail_organisasi">-</span>
                            </div>
                        </div>
                    </div>
                    {{-- ROW 4 MAHASISWA --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-dark">Kegiatan/Acara</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-thumbtack mr-2 text-primary"></i>
                                <span class="text-secondary detail_kegiatan">-</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-dark">Program Studi</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-swatchbook mr-2 text-primary"></i>
                                <span class="text-secondary detail_prodi">-</span>
                            </div>
                        </div>
                    </div>
                    {{-- ROW 5 MAHASISWA --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-dark">Jumlah Peserta</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-hand-paper mr-2 text-primary"></i>
                                <span class="text-secondary detail_peserta">-</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-dark">Kelas</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-book mr-2 text-primary"></i>
                                <span class="text-secondary detail_kelas">-</span>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <div id="section_non_mahasiswa" style="display: none;">
                    {{-- ROW 3 --}}
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold text-dark">Jam Mulai</label>
                            <div class="d-flex align-items-center">
                                <i class="far fa-clock mr-2 text-primary"></i>
                                <span class="text-secondary detail_jam_mulai">-</span>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold text-dark">Jam Selesai</label>
                            <div class="d-flex align-items-center">
                                <i class="far fa-clock mr-2 text-primary"></i>
                                <span class="text-secondary detail_jam_selesai">-</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-dark">Program Studi</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-swatchbook mr-2 text-primary"></i>
                                <span class="text-secondary detail_prodi">-</span>
                            </div>
                        </div>
                    </div>

                    {{-- ROW 4 --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-dark">Kegiatan/Acara</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-thumbtack mr-2 text-primary"></i>
                                <span class="text-secondary detail_kegiatan">-</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-dark">Kelas</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-book mr-2 text-primary"></i>
                                <span class="text-secondary detail_kelas">-</span>
                            </div>
                        </div>
                    </div>

                    {{-- ROW 5 --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-dark">Jumlah Peserta</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-hand-paper mr-2 text-primary"></i>
                                <span class="text-secondary detail_peserta">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ROW 6 --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-dark">Ketua Pelaksana</label>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-tie mr-2 text-primary"></i>
                            <span class="text-secondary" id="detail_ketua_pelaksana">-</span>
                        </div>
                    </div>
                </div>
                
                {{-- ROW 7 --}}
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="font-weight-bold text-dark">Keterangan</label>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-align-left mr-2 text-primary"></i>
                            <span class="text-secondary" id="detail_keterangan">-</span>
                        </div>
                    </div>
                </div>

                {{-- ROW 8 --}}
                <div class="form-group">
                    <label>Alasan Penolakan <small class="text-muted">(wajib diisi jika menolak)</small></label>
                    <textarea class="form-control" id="alasan_penolakan"></textarea>
                    <small id="error_alasan_penolakan" class="error-text form-text text-danger"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="prosesApproval('Ditolak')">
                    <i class="fas fa-times mr-1"></i> Tolak
                </button>
                <button type="button" class="btn btn-success" onclick="prosesApproval('Disetujui')">
                    <i class="fas fa-check mr-1"></i> Terima
                </button>
            </div>
        </div>
    </div>
</div>
{{-- @endif --}}

@endif

@if(Auth::user()->getRole() === 'ADM')
<div class="card" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
    <div class="card-header">
        <div class="d-flex align-items-center gap-2">
            <div class="d-flex align-items-center">
                <label class="mr-2 mb-0">Ruangan:</label>
                <select id="filter_ruangan" class="form-control form-control-sm" style="width: 150px;">
                    <option value="">Semua Ruangan</option>
                    @foreach($ruanganList as $ruangan)
                        <option value="{{ $ruangan->ruangan_id }}">
                            {{ $ruangan->ruangan_nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex align-items-center">
                <label class="ml-3 mr-2 mb-0">Tahun Ajaran:</label>
                <select id="filter_tahun_ajaran" class="form-control form-control-sm" style="white-space: nowrap; width: 100px;">
                    <option value="">Semua</option>
                    @foreach($tahunAjaranList as $tahunAjaran)
                        <option value="{{ $tahunAjaran }}">{{ $tahunAjaran }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex align-items-center">
                <label class="ml-3 mr-2 mb-0">Semester:</label>
                <select id="filter_semester" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    <option value="Ganjil">Ganjil</option>
                    <option value="Genap">Genap</option>
                </select>
            </div>

            <div class="d-flex align-items-center">
                <label class="col-form-label mr-2 ml-3 mb-0">Cari:</label>
                    <input type="text" id="pengajuan_search" class="form-control form-control-sm" placeholder="Cari berdasarkan kegiatan/acara" style="width: 220px;">
                    <button type="button" class="btn btn-primary btn-sm mr-2 mb-0">
                        <i class="fas fa-search"></i>
                    </button>
            </div>

            <div class="d-flex align-items-center">
                <label class="mr-2 mb-0">Show</label>

                <select class="form-control form-control-sm" id="data-table-length">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>

                <label class="ml-2 mb-0">entries</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <table class="table table-hover table-sm" id="table_pengajuan">
            <thead style="background-color: #e3e3e3;">
                <tr>
                    <th>No.</th>
                    <th>Ruangan</th>
                    <th>Tanggal</th>
                    <th>Jam Mulai</th>
                    <th>Jam Selesai</th>
                    <th>Kegiatan/Acara</th>
                    <!--th>Status</th>-->
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuans as $key => $p)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $p->ruangans->pluck('ruangan_nama')->implode(', ') }}</td>
                    <td data-order="{{ $p->pengajuan_tgl }}"> {{ \Carbon\Carbon::parse($p->pengajuan_tgl)->locale('id')->translatedFormat('d F Y') }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($p->pengajuan_jam_mulai)->format('H:i') }}
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($p->pengajuan_jam_selesai)->format('H:i') }}
                    </td>
                    <td>{{ Str::limit($p->pengajuan_nama, 35, '...') }}</td>
                    <!--td>
                        @switch($p->pengajuan_status)
                            @case('Diterima')
                                <span class="badge badge-success">Diterima</span>
                                @break
                            @case('Ditolak')
                                <span class="badge badge-danger">Ditolak</span>
                                @break
                            @default
                                <span class="badge badge-secondary">Diajukan</span>
                        @endswitch
                    </td-->
                    <td>
                        <div class="btn-group" role="group">
                            {{-- Tombol Lihat Detail (semua role) --}}
                            <!--button onclick="modalAction('{{ url('/pengajuan/' . $p->pengajuan_id . '/show_ajax') }}')" class="btn btn-outline-info btn-sm" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button-->
                            @if(Auth::user()->getRole() === 'ADM')
                                @if($p->status_operasional === 'Ditinjau')
                                <button onclick="modalAction('{{ url('/penyelesaian/' . $p->pengajuan_id . '/edit_ajax') }}')" class="btn btn-outline-success btn-sm" title="Verifikasi">
                                    <i class="fas fa-calendar-check"></i>
                                </button>
                                @else
                                <span class="text-muted" title="Status sudah {{ $p->pengajuan_status }}"><i class="fas fa-lock"></i></span>
                                @endif
                            @elseif(Auth::user()->getRole() === 'VRF')
                                @if($p->pengajuan_status === 'Diajukan')
                                <button onclick="modalAction('{{ url('/pengajuan/' . $p->pengajuan_id . '/verifikasi_ajax') }}')" class="btn btn-outline-primary btn-sm" title="Verifikasi">
                                    <i class="fas fa-gavel"></i>
                                </button>
                                @else
                                <button class="btn btn-outline-secondary btn-sm" disabled title="Status operasional sudah {{ $p->status_operasional ?? 'Selesai' }}">
                                    <i class="fas fa-lock"></i>
                                </button>
                                <!--<span class="text-muted" title="Status sudah {{ $p->pengajuan_status }}"><i class="fas fa-lock"></i></span>-->
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center">Belum ada data pengajuan penyelesaian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="modal fade" id="modalPengajuan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" id="modalSize">
        <div class="modal-content"></div>
    </div>
</div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
@endpush

@push('js')
    <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script>
    function modalAction(url) {
        $('#modalSize').removeClass('modal-xl modal-lg modal-md modal-sm').addClass('modal-lg');
        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                $('#modalPengajuan .modal-content').html(data);
                $('#modalPengajuan').modal('show');
            },
            error: function(xhr) {
                alert('Gagal memuat modal. Cek console log.');
                console.error('AJAX Error:', xhr.responseText);
            }
        });
    }

    let currentApprovalId = null;

    function modalProses(data) {
        currentApprovalId = data.approval_id;

        // Mengisi data ke elemen modal
        $('#detail_ruangan').text(data.ruangan);
        $('#detail_peminjam').text(data.peminjam);
        $('#detail_tanggal_pengajuan').text(data.tanggal_pengajuan);
        $('#detail_tanggal_peminjaman').text(data.tanggal_peminjaman);
        $('#detail_ketua_pelaksana').text(data.ketua_pelaksana);
        $('#detail_status_peminjam').text(data.status);
        /*$('#detail_jam_mulai').text(data.jam_mulai);
        $('#detail_jam_selesai').text(data.jam_selesai);
        $('#detail_prodi').text(data.prodi);
        $('#detail_kegiatan').text(data.kegiatan);
        $('#detail_kelas').text(data.kelas);
        $('#detail_peserta').text(data.peserta);*/
        $('#detail_keterangan').text(data.keterangan);

        //Mengisi data di class yang sama
        $('.detail_jam_mulai').text(data.jam_mulai);
        $('.detail_jam_selesai').text(data.jam_selesai);
        $('.detail_prodi').text(data.prodi);
        $('.detail_kegiatan').text(data.kegiatan);
        $('.detail_kelas').text(data.kelas);
        $('.detail_peserta').text(data.peserta);
        $('.detail_organisasi').text(data.organisasi);

        // LOGIKA IF - ELSE DARI JAVASCRIPT
        if (data.is_mahasiswa) {
            $('#section_mahasiswa').show();
            $('#section_non_mahasiswa').hide();
        } else {
            $('#section_mahasiswa').hide();
            $('#section_non_mahasiswa').show();
        }

        // Reset input penolakan & error
        $('#alasan_penolakan').val('');
        $('#error_alasan_penolakan').text('');

        // Tampilkan modal
        $('#modalProsesApproval').modal('show');
    }

    function prosesApproval(status) {
        $('#error_alasan_penolakan').text('');
        $.ajax({
            url: '{{ url('/pengajuan/approval') }}/' + currentApprovalId + '/proses_ajax',
            type: 'PUT',
            data: {
                status_approval: status,
                alasan_penolakan: $('#alasan_penolakan').val(),
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message, timer: 2000, showConfirmButton: false })
                        .then(() => { $('#modalProsesApproval').modal('hide'); location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: response.message });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.msgField) {
                    $('#error_alasan_penolakan').text(xhr.responseJSON.msgField.alasan_penolakan[0]);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: (xhr.responseJSON && xhr.responseJSON.message) || 'Terjadi kesalahan pada server.' });
                }
            }
        });
    }

    function modalAction(url) {
        $('#modalSize').removeClass('modal-xl modal-lg modal-md modal-sm').addClass('modal-lg');
        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                $('#modalPengajuan .modal-content').html(data);
                $('#modalPengajuan').modal('show');
            },
            error: function(xhr) {
                const pesan = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal memuat modal. Cek console log.';
                alert(pesan);
                console.error('AJAX Error:', xhr.responseText);
            }
        });
    }

    // Data periode (m_periode) untuk filter Tahun Ajaran/Semester — dari PengajuanService::indexData().
    // Tanggal sudah dalam format 'Y-m-d' (string) sehingga bisa dibandingkan langsung sebagai string
    // (perbandingan leksikografis 'Y-m-d' identik dengan perbandingan tanggal aslinya).
    const periodeFilterData = @json($periodeList);

    /**
     * Cari rentang tanggal_mulai/tanggal_selesai gabungan dari SELURUH baris periode yang cocok
     * dengan tahun_ajaran dan/atau semester yang dipilih. Kalau keduanya kosong -> null (tidak
     * ada filter aktif). Kalau ada filter tapi tidak ada baris periode yang cocok -> range kosong
     * (tidak ada baris tabel yang match, ditandai dengan mulai=null).
     */
    function periodeRangeFor(tahunAjaran, semester) {
        if (!tahunAjaran && !semester) {
            return null;
        }

        const matches = periodeFilterData.filter(function (p) {
            return (!tahunAjaran || p.tahun_ajaran === tahunAjaran) && (!semester || p.semester === semester);
        });

        if (matches.length === 0) {
            return { mulai: null, selesai: null };
        }

        return {
            mulai: matches.reduce((min, p) => p.tanggal_mulai < min ? p.tanggal_mulai : min, matches[0].tanggal_mulai),
            selesai: matches.reduce((max, p) => p.tanggal_selesai > max ? p.tanggal_selesai : max, matches[0].tanggal_selesai),
        };
    }

    $(function () {
        // 1. Inisialisasi DataTable untuk table_antrian
        let tableAntrian = $('#table_antrian').DataTable({
            responsive: true,
            autoWidth: false,
            // dom 'rtip': (t) table, (i) info "Showing..", (p) pagination
            dom: 'rtip', 
            pageLength: 10,
            columnDefs: [
                // Matikan fitur orderable untuk kolom No. (0) dan Aksi (6)
                { orderable: false, targets: [0, 6] } 
            ]
        });

        // 2. Hubungkan form Cari (Custom Search) dengan DataTable
        $('#pengajuan_search').on('keyup', function () {
            tableAntrian.search(this.value).draw();
        });

        // 3. Hubungkan dropdown Show [..] entries (Custom Length) dengan DataTable
        $('#data-table-length').change(function () {
            tableAntrian.page.len($(this).val()).draw();
        });

        // 4. Hubungkan filter dropdown Ruangan dengan DataTable
        $('#filter_ruangan').change(function() {
            let selectedText = $(this).find("option:selected").text().trim();
            if($(this).val() === "") {
                tableAntrian.column(1).search('').draw(); // Tampilkan semua jika "Semua Ruangan" dipilih
            } else {
                tableAntrian.column(1).search(selectedText).draw(); // Filter berdasarkan kolom ke-1 (Ruangan)
            }
        });

        /* --- KODE LAMA UNTUK TABLE PENGAJUAN (Boleh dihapus jika tabel pengajuan di-comment) --- */
        // Ext search untuk table_pengajuan...
        // let table = $('#table_pengajuan').DataTable({ ... });
        // dst...
    });

    $(function () {

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.sTableId !== 'table_pengajuan' && settings.sTableId !== 'table_antrian') {
                return true;
            }

            const tahunAjaran = $('#filter_tahun_ajaran').val();
            const semester = $('#filter_semester').val();
            const range = periodeRangeFor(tahunAjaran, semester);

            // Indeks kolom tanggal berbeda antara Admin (2) dan Non-Admin (4)
            const dateColumnIndex = settings.sTableId === 'table_antrian' ? 4 : 2

            /*if (range === null) {
                return true;
            }

            if (range.mulai === null) {
                return false;
            }*/

            const tanggal = $(settings.aoData[dataIndex].nTr).find('td').eq(dateColumnIndex).attr('data-order');
            if (!tanggal) return true;

            return tanggal >= range.mulai && tanggal <= range.selesai;
        });

        let table = $('#table_pengajuan').DataTable({
            responsive: true,
            autoWidth: false,
            dom: 'rtip',
            pageLength: 10,
            columnDefs: [
                { orderable: false, targets: [0, 6] }
            ]
        });

        $('#pengajuan_search').on('keyup', function () {
            table.search(this.value).draw();
        });

        $('#data-table-length').change(function () {
            table.page.len($(this).val()).draw();
        });

        // Pastikan dropdown men-trigger draw() untuk table_antrian juga
        $('#filter_tahun_ajaran, #filter_semester').change(function(){
            if (typeof table !== 'undefined') table.draw();
            if (typeof tableAntrian !== 'undefined') tableAntrian.draw();
            //table.draw();
        });

        $('#filter_ruangan').change(function() {
            let selectedText = $(this).find("option:selected").text().trim();
            if($(this).val() === "") {
                table.column(1).search('').draw();
            } else {
                table.column(1).search(selectedText).draw();
            }
        });

    });
</script>
@endpush
