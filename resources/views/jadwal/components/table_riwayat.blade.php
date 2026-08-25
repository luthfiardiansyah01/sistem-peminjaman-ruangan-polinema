<table class="table table-hover table-sm" id="table_riwayat">


    <thead style="background-color: #e3e3e3; font-color: #3F3F3F;">
        <tr>
            <th width="5%">No.</th>
            <th width="15%">Ruangan</th>
            <th width="12%">Tanggal</th>
            <th width="10%">Jam Mulai</th>
            <th width="10%">Jam Selesai</th>
            <th width="20%">Kegiatan/Acara</th>
            <th width="10%">Semester</th>
            <th width="12%">Status</th>
            <th width="6%">Aksi</th>
        </tr>
    </thead>

    <tbody style="background-color: #ffffffff; font-color: #3F3F3F;">

        @foreach ($riwayats as $key => $jadwal)

            @php
                $ruangans = $jadwal->ruangans;
                $ruanganNamesArray = $ruangans->pluck('ruangan_nama')->toArray();
                $ruanganIds = $ruangans->pluck('ruangan_id')->implode(' ');
                $ruanganNamesSearch = implode(' ', $ruanganNamesArray);
                $ruanganNamesDisplay = implode(', ', $ruanganNamesArray);

                $statusClass = match($jadwal->jadwal_status) {
                    'Selesai' => 'status-completed',
                    'Dokumentasi Tidak Sesuai' => 'status-invalid',
                    default => 'status-completed'
                };
            @endphp
            <tr>
                <td>
                    {{ $key + 1 }}.
                </td>

                <td data-search="{{ $ruanganIds }} {{ $ruanganNamesSearch }}">
                    {{ Str::limit($ruanganNamesDisplay, 18, '...') }}
                </td>

                <td data-order="{{ $jadwal->jadwal_tgl }}">
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
                    <span class="status-badge {{ $statusClass }}">
                        {{ $jadwal->jadwal_status }}
                    </span>
                </td>

                <td>
                    @if($showView ?? false)
                        <button class="btn btn-outline-info btn-sm" onclick="modalAction('{{ url('/jadwal/' . $jadwal->jadwal_id . '/show_ajax') }}')">
                            <i class="fas fa-eye"></i>
                        </button>
                    @endif
                </td>

            </tr>
        @endforeach

    </tbody>

</table>