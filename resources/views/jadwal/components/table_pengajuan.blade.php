<table class="table table-hover table-sm" id="table_pengajuan">

    <thead style="background-color: #e3e3e3; font-color: #3F3F3F;">
        <tr>
            <th width="5%">No.</th>
            <th width="15%">Ruangan</th>
            <th width="12%">Tanggal</th>
            <th width="10%">Jam Mulai</th>
            <th width="10%">Jam Selesai</th>
            <th width="20%">Kegiatan/Acara</th>
            <th width="12%">Status</th>
            <th width="6%">Aksi</th>
        </tr>
    </thead>

    <tbody style="background-color: #ffffffff; font-color: #3F3F3F;">
        @foreach ($pengajuans as $key => $pengajuan)
            @php
                $ruanganIds = $pengajuan->ruangans->pluck('ruangan_id')->implode(' ');
                $ruanganNames = $pengajuan->ruangans->pluck('ruangan_nama')->implode(' ');
            @endphp
            <tr>
                <td>
                    {{ $key + 1 }}.
                </td>
                <td data-search="{{ $ruanganIds }} {{ $ruanganNames }}">
                    {{ Str::limit($pengajuan->ruangans->pluck('ruangan_nama')->implode(', '), 18, '...') }}
                </td>

                <td data-order="{{ $pengajuan->pengajuan_tgl }}">
                    {{ \Carbon\Carbon::parse($pengajuan->pengajuan_tgl)->locale('id')->translatedFormat('d F Y') }}
                </td>

                <td>
                    {{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_mulai)->format('H:i') }}
                </td>

                <td>
                    {{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_selesai)->format('H:i') }}
                </td>
                <td>
                    {{ Str::limit($pengajuan->pengajuan_nama, 18, '...') }}
                </td>
                <td class="text-center">
                    @switch($pengajuan->pengajuan_status)
                        @case('Diterima')
                            <span class="status-badge status-success">
                                Diterima
                            </span>
                            @break

                        @case('Ditolak')
                            <span class="status-badge status-danger">
                                Ditolak
                            </span>
                            @break

                        @default
                            <span class="status-badge status-proposed">
                                Diajukan
                            </span>
                    @endswitch
                </td>
                <td>
                    <button class="btn btn-outline-info btn-sm" onclick="modalAction('{{ url('/pengajuan/' . $pengajuan->pengajuan_id . '/show_ajax') }}')">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>

</table>
