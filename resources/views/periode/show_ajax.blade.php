@empty($periode)
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" style="font-weight: bold;">Kesalahan</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <h5><i class="icon fas fa-ban"></i> Kesalahan!!!</h5>
            <div class="alert alert-danger">Data yang anda cari tidak ditemukan</div>
            <button type="button" data-dismiss="modal" class="btn btn-warning">Kembali</button>
        </div>
    </div>
@else
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" style="font-weight: bold;">Data Periode</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Tahun Ajaran</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-week mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $periode->tahun_ajaran }}</span>    
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Semester</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-hourglass-half mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $periode->semester }}</span>    
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Tanggal Mulai</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-plus mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $periode->tanggal_mulai->locale('id')->translatedFormat('d F Y') }}</span>    
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Tanggal Selesai</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-minus mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $periode->tanggal_selesai->locale('id')->translatedFormat('d F Y') }}</span>    
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Status</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle mr-2 text-primary"></i>
                        @if($periode->periode_status === 'Open')
                            <span class="badge badge-success">Open</span>
                        @else
                            <span class="badge badge-danger">Closed</span>
                        @endif
                    </div>
                </div>
                @if($periode->updater)
                <!--div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">Terakhir diubah oleh</label>
                    <div class="text-secondary">{{ $periode->updater->getDisplayName() }} pada {{ $periode->updated_at?->translatedFormat('d F Y H:i') }}</div>
                </div-->
                @endif
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" data-dismiss="modal" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i> Kembali</button>
        </div>
    </div>
@endempty
