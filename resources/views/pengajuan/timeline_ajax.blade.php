<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" style="font-weight: bold;">Timeline Pengajuan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        @forelse($timeline as $t)
            <div class="mb-3 pb-2" style="border-bottom: 1px solid #eee;">
                <div class="d-flex justify-content-between">
                    <span class="font-weight-bold">Tahap {{ $t->urutan_tahap }} — {{ $t->jabatanApproval->posisi_approval }}</span>
                    <span class="badge {{ $t->status_approval == 'Disetujui' ? 'badge-success' : ($t->status_approval == 'Ditolak' || $t->status_approval == 'Auto Reject' ? 'badge-danger' : 'badge-secondary') }}" style="padding: 5px 5px 5px 5px">
                        {{ $t->status_approval }}
                    </span>
                </div>
                <div class="text-secondary small">Verifikator: {{ optional($t->jabatanApproval->user)->getDisplayName() ?? '-' }}</div>
                @if($t->diproses_pada)
                    <div class="text-secondary small">Diproses: {{ $t->diproses_pada->locale('id')->translatedFormat('d F Y H:i') }}</div>
                @elseif($t->batas_waktu)
                    <div class="text-secondary small">Batas waktu: {{ $t->batas_waktu->locale('id')->translatedFormat('d F Y H:i') }}</div>
                @endif
                @if($t->alasan_penolakan)
                    <div class="text-danger small">Alasan: {{ $t->alasan_penolakan }}</div>
                @endif
            </div>
        @empty
            <div class="alert alert-warning">Belum ada tahap approval untuk pengajuan ini.</div>
        @endforelse
    </div>
    <div class="modal-footer">
        <button type="button" onclick="showPengajuan(this, '{{ $id }}')" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </button>
    </div>
</div>

<script>
    function showPengajuan(element, id) {
        // 1. Simpan elemen tombol & ubah ke indikator loading
        var btn = $(element);
        var originalHtml = btn.html();
        //btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Memuat...').prop('disabled', true);
        
        // Ambil ID langsung via Blade
        //var id = "{{ $id }}";

        // URL kembali ke show_ajax
        var url = "{{ url('/pengajuan') }}/" + id + "/show_ajax";

        $.ajax({
            url: url,
            type: "GET",
            success: function(response) {
                btn.closest('.modal-content').parent().html(response);
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                alert('Gagal memuat detail pengajuan.');
            },
            complete: function() {
                btn.html(originalHtml).prop('disabled', false);
            }
        });
    }
</script>
