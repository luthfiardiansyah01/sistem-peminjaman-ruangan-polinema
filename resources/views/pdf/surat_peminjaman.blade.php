<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Peminjaman Ruangan</title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12px; color: #000; }
        .header { border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; overflow: hidden; }
        .header img.logo { height: 60px; float: left; }
        .header h2, .header h4 { margin: 0; text-align: center; }
        table.kop { width: 100%; margin-top: 15px; }
        table.kop td { padding: 1px 0; vertical-align: top; }
        table.kop td.label { width: 70px; }
        .tujuan { margin-top: 15px; }
        .isi p { text-align: justify; }
        .ttd-label { margin-top: 20px; margin-bottom: 0; }
        table.ttd { width: 100%; margin-top: 5px; border-collapse: collapse; }
        table.ttd td { width: 50%; vertical-align: top; padding: 4px 10px; }
        .ttd-blok { margin-bottom: 10px; }
        .ttd-blok .jabatan { margin: 0; }
        .ttd-blok .spasi { height: 45px; }
        .ttd-blok .nama { margin: 0; font-weight: bold; text-decoration: underline; }
        .ttd-blok .identitas { margin: 0; }
        .cp { margin-top: 15px; font-size: 11px; }
        .lampiran { margin-top: 25px; page-break-inside: avoid; }
        .lampiran .lampiran-kop { margin: 0 0 10px 0; }
        .lampiran table { width: 100%; border-collapse: collapse; }
        .lampiran th, .lampiran td { border: 1px solid #000; padding: 6px; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        @if($pengajuan->organisasi && $pengajuan->organisasi->organisasi_logo)
            <img class="logo" src="{{ public_path('storage/' . $pengajuan->organisasi->organisasi_logo) }}" alt="Logo">
        @endif
        <h2>SURAT PEMINJAMAN RUANGAN</h2>
        <h4>Jurusan Teknologi Informasi</h4>
    </div>

    @php
        // Rantai tanda tangan diambil dari t_pengajuan_approval, sudah terurut per urutan_tahap
        // (lihat PengajuanService::findForCetakSurat()). Tahap akhir alur saat ini selalu
        // Ketua Jurusan (belum ada eskalasi tambahan — arahan klien revisi ke-2 poin 1).
        $approvalUrut = $pengajuan->approvals->sortBy('urutan_tahap')->values();
        $tujuan = ['Ketua Jurusan Teknologi Informasi', 'Politeknik Negeri Malang'];

        $identitas = function ($user) {
            if (!$user) {
                return '-';
            }
            if ($user->dosen) {
                return 'NIDN. ' . $user->dosen->dosen_nidn;
            }
            if ($user->mahasiswa) {
                return 'NIM. ' . $user->mahasiswa->mahasiswa_nim;
            }
            return '-';
        };

        $noHp = function ($user) {
            if (!$user) {
                return null;
            }
            return $user->dosen->dosen_noHp
                ?? $user->mahasiswa->mahasiswa_noHp
                ?? $user->tendik->tendik_noHp
                ?? null;
        };

        // Ikuti pembagian dua blok tanda tangan seperti pada contoh surat resmi
        // (FRM.BAA.03.18.00): blok "Hormat kami," untuk pihak pemohon (Ketua Pelaksana
        // & Ketua Umum/Organisasi), blok "Mengetahui dan menyetujui," untuk seluruh
        // pihak yang menyetujui berjenjang sesudahnya (DPK, Presiden BEM, Ketua Jurusan).
        $isPemohon = fn ($a) => in_array(optional($a->jabatanApproval)->posisi_approval, ['Ketua Pelaksana', 'Ketua Umum'], true);
        $blokPemohon = $approvalUrut->filter($isPemohon)->values();
        $blokPenyetuju = $approvalUrut->reject($isPemohon)->values();
    @endphp

    <table class="kop">
        <tr><td class="label">Nomor</td><td>: {{ $pengajuan->nomor_surat }}</td></tr>
        <tr><td class="label">Lampiran</td><td>: 2 (dua) Lembar</td></tr>
        <tr><td class="label">Hal</td><td>: Peminjaman Ruangan {{ $pengajuan->ruangans->pluck('ruangan_nama')->implode(', ') }}</td></tr>
    </table>

    <div class="tujuan">
        <p style="margin-bottom: 0;">Yth. {{ $tujuan[0] }}</p>
        <p style="margin-top: 0;">&nbsp;&nbsp;&nbsp;&nbsp;{{ $tujuan[1] }}</p>
    </div>

    <div class="isi">
        <p>Dengan hormat,</p>
        <p>
            Sehubungan dengan adanya kegiatan &ldquo;{{ $pengajuan->pengajuan_nama }}&rdquo;,
            kami mohon bantuan peminjaman ruangan {{ $pengajuan->ruangans->pluck('ruangan_nama')->implode(', ') }}
            Politeknik Negeri Malang beserta fasilitas yang ada di dalamnya dan daya listrik
            di ruangan tersebut.
        </p>
        <p>Kegiatan tersebut akan diselenggarakan pada (data terlampir).</p>
        <p>Demikian surat peminjaman ini kami buat, atas izin dan bantuan yang diberikan kami sampaikan terima kasih.</p>
    </div>

    @if($blokPemohon->isNotEmpty())
        <p class="ttd-label">Hormat kami,</p>
        <table class="ttd">
            <tr>
                @foreach($blokPemohon as $index => $approval)
                    @php $jabatan = $approval->jabatanApproval; @endphp
                    <td>
                        <div class="ttd-blok">
                            <p class="jabatan">{{ optional($jabatan)->posisi_approval ?? '-' }},</p>
                            <div class="spasi"></div>
                            <p class="nama">{{ optional(optional($jabatan)->user)->getDisplayName() ?? '-' }}</p>
                            <p class="identitas">{{ $identitas(optional($jabatan)->user) }}</p>
                        </div>
                    </td>
                    @if($index % 2 === 1)
                        </tr><tr>
                    @endif
                @endforeach
            </tr>
        </table>
    @endif

    @if($blokPenyetuju->isNotEmpty())
        <p class="ttd-label">Mengetahui dan menyetujui,</p>
        <table class="ttd">
            <tr>
                @foreach($blokPenyetuju as $index => $approval)
                    @php $jabatan = $approval->jabatanApproval; @endphp
                    <td>
                        <div class="ttd-blok">
                            <p class="jabatan">{{ optional($jabatan)->posisi_approval ?? '-' }},</p>
                            <div class="spasi"></div>
                            <p class="nama">{{ optional(optional($jabatan)->user)->getDisplayName() ?? '-' }}</p>
                            <p class="identitas">{{ $identitas(optional($jabatan)->user) }}</p>
                        </div>
                    </td>
                    @if($index % 2 === 1)
                        </tr><tr>
                    @endif
                @endforeach
            </tr>
        </table>
    @endif

    <p class="cp">Cp. {{ $noHp($pengajuan->user) ?? '-' }} ({{ $pengajuan->user->getDisplayName() }})</p>

    <div class="lampiran">
        <h4>Lampiran 1</h4>
        <p class="lampiran-kop">Nomor: {{ $pengajuan->nomor_surat }}</p>
        <p class="lampiran-kop">Kegiatan {{ $pengajuan->pengajuan_nama }}</p>
        <table>
            <thead><tr><th>No.</th><th>Acara</th><th>Tanggal Peminjaman</th><th>Pukul</th><th>Tempat</th></tr></thead>
            <tbody>
                <tr>
                    <td>1.</td>
                    <td>{{ $pengajuan->pengajuan_nama }}</td>
                    <td>{{ \Carbon\Carbon::parse($pengajuan->pengajuan_tgl)->locale('id')->translatedFormat('d F Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($pengajuan->pengajuan_jam_selesai)->format('H:i') }} WIB</td>
                    <td>{{ $pengajuan->ruangans->pluck('ruangan_nama')->implode(', ') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="lampiran">
        <h4>Lampiran 2</h4>
        <p class="lampiran-kop">Nomor: {{ $pengajuan->nomor_surat }}</p>
        <p class="lampiran-kop">DAFTAR NAMA PANITIA/PESERTA</p>
        <table>
            <thead><tr><th>No.</th><th>Nama</th><th>Program Studi</th><th>Jabatan</th></tr></thead>
            <tbody>
                <tr>
                    <td>1.</td>
                    <td>{{ $pengajuan->user->getDisplayName() }}</td>
                    <td>{{ optional(optional($pengajuan->user->mahasiswa)->prodi)->prodi_nama ?? '-' }}</td>
                    <td>Penanggung Jawab</td>
                </tr>
                @foreach ($pengajuan->panitias as $index => $panitia)
                    <tr>
                        <td>{{ $index + 2 }}.</td>
                        <td>{{ $panitia->user->getDisplayName() }}</td>
                        <td>{{ optional(optional($panitia->user->mahasiswa)->prodi)->prodi_nama ?? '-' }}</td>
                        <td>{{ $panitia->keterangan ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
