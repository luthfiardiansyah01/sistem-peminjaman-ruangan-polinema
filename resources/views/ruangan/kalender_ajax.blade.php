@empty($ruangan)
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
<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" style="font-weight: bold;">Kalender Ketersediaan — {{ $ruangan->ruangan_nama }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="kalender-prev">
                <i class="fas fa-chevron-left"></i> Bulan Sebelumnya
            </button>
            <strong id="kalender-judul-bulan"></strong>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="kalender-next">
                Bulan Berikutnya <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <div class="mb-2">
            <span class="badge" style="background-color:#28a745; color:#fff;">&nbsp;&nbsp;</span> Tersedia
            &nbsp;
            <span class="badge" style="background-color:#ffc107; color:#212529;">&nbsp;&nbsp;</span> Ada pengajuan menunggu
            &nbsp;
            <span class="badge" style="background-color:#dc3545; color:#fff;">&nbsp;&nbsp;</span> Tidak tersedia
        </div>

        <table class="table table-bordered table-sm text-center" id="kalender-grid">
            <thead style="background-color: #e3e3e3;">
                <tr>
                    <th>Min</th><th>Sen</th><th>Sel</th><th>Rab</th><th>Kam</th><th>Jum</th><th>Sab</th>
                </tr>
            </thead>
            <tbody id="kalender-body">
                {{-- Diisi JS --}}
            </tbody>
        </table>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </button>
    </div>
</div>

<script>
(function () {
    const ruanganId = {{ (int) $ruangan->ruangan_id }};
    const bulanNama = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const now = new Date();
    let tahun = now.getFullYear();
    let bulan = now.getMonth() + 1; // 1-12

    const warna = {
        'Tersedia': '#28a745',
        'Diajukan': '#ffc107',
        'Tidak Tersedia': '#dc3545',
    };

    let request = null;

    function tampilkanLoading() {
        $('#kalender-body').html('<tr><td colspan="7" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Memuat kalender...</td></tr>');
        $('#kalender-prev, #kalender-next').prop('disabled', true);
    }

    function renderKalender() {
        $('#kalender-judul-bulan').text(bulanNama[bulan - 1] + ' ' + tahun);

        // Batalkan request sebelumnya (kalau ada) supaya tidak ada balapan hasil
        // saat user klik prev/next berulang kali dengan cepat.
        if (request) {
            request.abort();
        }

        tampilkanLoading();

        request = $.ajax({
            url: '{{ url("/ruangan") }}/' + ruanganId + '/kalender_data_ajax',
            type: 'GET',
            data: { bulan: tahun + '-' + String(bulan).padStart(2, '0') },
            dataType: 'json',
            success: function (data) {
                const body = $('#kalender-body');
                body.empty();

                const tanggalPertama = new Date(tahun, bulan - 1, 1);
                const offsetHariAwal = tanggalPertama.getDay(); // 0=Minggu
                const jumlahHari = new Date(tahun, bulan, 0).getDate();

                let sel = '<tr>';
                for (let i = 0; i < offsetHariAwal; i++) {
                    sel += '<td></td>';
                }

                let kolom = offsetHariAwal;
                for (let hari = 1; hari <= jumlahHari; hari++) {
                    const tgl = tahun + '-' + String(bulan).padStart(2, '0') + '-' + String(hari).padStart(2, '0');
                    const status = data[tgl] || 'Tersedia';
                    const warnaLatar = warna[status] || '#6c757d';
                    const warnaTeks = status === 'Diajukan' ? '#212529' : '#fff';
                    sel += '<td style="background-color:' + warnaLatar + '; color:' + warnaTeks + ';" title="' + status + '">' + hari + '</td>';

                    kolom++;
                    if (kolom % 7 === 0) {
                        sel += '</tr><tr>';
                    }
                }
                sel += '</tr>';

                body.html(sel);
            },
            error: function (xhr, status) {
                if (status === 'abort') {
                    return; // dibatalkan karena ada request baru, biarkan spinner request baru yang tampil
                }
                console.error('Gagal memuat kalender:', xhr.responseText);
                $('#kalender-body').html('<tr><td colspan="7" class="text-center py-4 text-danger">Gagal memuat kalender.</td></tr>');
            },
            complete: function (xhr, status) {
                if (status !== 'abort') {
                    $('#kalender-prev, #kalender-next').prop('disabled', false);
                }
            }
        });
    }

    $('#kalender-prev').on('click', function () {
        bulan--;
        if (bulan < 1) { bulan = 12; tahun--; }
        renderKalender();
    });

    $('#kalender-next').on('click', function () {
        bulan++;
        if (bulan > 12) { bulan = 1; tahun++; }
        renderKalender();
    });

    renderKalender();
})();
</script>
@endempty
