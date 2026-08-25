<table class="table table-hover table-sm w-100" id="table_daftar">
    <thead style="background-color: #e3e3e3; font-color: #3F3F3F;">
        <tr>
            <th width="5%">No.</th>
            <th width="15%">Ruangan</th>
            <th width="14%">Tanggal</th>
            <th width="10%">Jam Mulai</th>
            <th width="10%">Jam Selesai</th>
            <th width="20%">Kegiatan/Acara</th>
            <th width="8%">Semester</th>
            <th width="12%">Status</th>
            <th width="6%">Aksi</th>
        </tr>
    </thead>

    <tbody style="background-color: #ffffffff; font-color: #3F3F3F;">
        @foreach ($jadwals as $key => $jadwal)

            @php
                $ruanganIds = $jadwal->ruangans->pluck('ruangan_id')->implode(' ');
                $ruanganNames = $jadwal->ruangans->pluck('ruangan_nama')->implode(' ');

                $statusClass = match($jadwal->jadwal_status){
                    'Akan Datang' => 'status-upcoming',
                    'Berlangsung' => 'status-running',
                    'Ditinjau' => 'status-review',
                    'Dokumentasi Tidak Sesuai' => 'status-invalid',
                    default => 'status-review'
                };
            @endphp

            <tr>
                <td>{{ $key + 1 }}.</td>
                <td data-search="{{ $ruanganIds }} {{ $ruanganNames }}">
                    {{ Str::limit($jadwal->ruangans->pluck('ruangan_nama')->implode(', '), 18, '...') }}
                </td>
                <td data-order="{{ $jadwal->jadwal_tgl }}">
                    {{-- Str::limit(
                        collect($jadwal->jadwal_tgl)->map(fn($tgl) => \Carbon\Carbon::parse($tgl)->locale('id')->translatedFormat('d F Y'))->implode(', '),
                        15,
                        '...'
                    ) --}}
                    {{ \Carbon\Carbon::parse($jadwal->jadwal_tgl)->locale('id')->translatedFormat('d F Y') }}
                </td>
                <td>
                    {{ \Carbon\Carbon::parse($jadwal->jadwal_jam_mulai)->format('H:i') }}
                </td>
                <td>
                    {{ \Carbon\Carbon::parse($jadwal->jadwal_jam_selesai)->format('H:i') }}
                </td>
                <td>
                    {{ Str::limit($jadwal->jadwal_nama, 18, '...') }}
                </td>
                <td>
                    {{ $jadwal->semester }}
                </td>
                <td class="text-center">
                    @switch($jadwal->jadwal_status)

                        @case('Akan Datang')
                            <span class="status-badge status-upcoming">
                                Akan Datang
                            </span>
                            @break

                        @case('Berlangsung')
                            <span class="status-badge status-running">
                                Berlangsung
                            </span>
                            @break

                        @case('Ditinjau')
                            <span class="status-badge status-review">
                                Ditinjau
                            </span>
                            @break

                        @case('Dokumentasi Tidak Sesuai')
                            <span class="status-badge status-invalid">
                                Dokumentasi Tidak Sesuai
                            </span>
                            @break

                        @default
                            <span class="status-badge status-review">
                                {{ $jadwal->jadwal_status }}
                            </span>

                    @endswitch
                </td>

                <td class="text-center">

                    <div class="btn-group btn-group-sm">
                        @if(request()->is('jadwal/pribadi*'))
                            <button
                                class="btn btn-outline-info btn-sm"
                                title="Detail"
                                onclick="modalAction('{{ url('/jadwal/pribadi/'.$jadwal->jadwal_id.'/show_ajax') }}')">
                                <i class="fas fa-eye"></i>
                            </button>
                        @endif

                        {{-- @if(($showEdit ?? false) && $jadwal->jadwal_status == 'Akan Datang') --}}

                            <!--button
                                class="btn btn-outline-warning btn-sm"
                                title="Edit"
                                onclick="modalAction('{{ url('/jadwal/pribadi/'.$jadwal->jadwal_id.'/edit_ajax') }}')">

                                <i class="fas fa-file-signature"></i>

                            </button-->

                        {{-- @endif --}}

                        @if($showView ?? false)

                            <button
                                class="btn btn-outline-info btn-sm"
                                title="View"
                                onclick="modalAction('{{ url('/jadwal/'.$jadwal->jadwal_id.'/show_ajax') }}')">
                                <i class="fas fa-eye"></i>
                            </button>

                            <!--a href="{{-- url('/pengajuan/' . $pengajuan->pengajuan_id . '/cetak_surat_ajax') --}}" 
                                target="_blank" 
                                class="btn btn-outline-export btn-sm" 
                                title="Unduh Surat">
                                <i class="fas fa-download"></i>
                            </a-->

                        @endif

                        @if(($showSelesaikan ?? false) && in_array($jadwal->jadwal_status, ['Berlangsung', 'Dokumentasi Tidak Sesuai']))

                            <button
                                class="btn btn-outline-success btn-sm"
                                title="Selesaikan Peminjaman"
                                onclick="modalAction('{{ url('/jadwal/pribadi/'.$jadwal->jadwal_id.'/selesaikan_ajax') }}')">

                                <i class="fas fa-calendar-check"></i>

                            </button>

                        @endif

                    </div>

                </td>

            </tr>

        @endforeach
    </tbody>
</table>

@push('css')
    <style>
        /* Custom CSS for Export Button */
        .btn-outline-export {
            border-color: #ff851b;
            /*background-color: #ff851b;*/
            color: #ff851b;
        }
        .btn-outline-export:hover {
            border-color: #e07415;
            background-color: #e07415;
            color: #fff;
        }
    </style>
@endpush