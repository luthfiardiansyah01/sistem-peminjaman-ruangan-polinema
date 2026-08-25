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
            <h5 class="modal-title" style="font-weight: bold;">Data Ruangan</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="row">
                {{-- Slideshow kanan-kiri untuk multi-foto --}}
                <div class="col-md-12 mb-4">
                    @php $fotos = $ruangan->fotos; @endphp
                    @if (!empty($fotos))
                        @if (count($fotos) === 1)
                            {{-- Hanya 1 foto: tampilkan langsung tanpa carousel --}}
                            <div class="text-center">
                                <img src="{{ asset('storage/' . $fotos[0]) }}" alt="Foto Ruangan" class="img-fluid rounded" style="max-height: 250px; object-fit: cover;">
                            </div>
                        @else
                            {{-- Bootstrap 4 Carousel untuk multi-foto --}}
                            <div id="carousel-ruangan-{{ $ruangan->ruangan_id }}" class="carousel slide" data-ride="carousel">
                                <ol class="carousel-indicators">
                                    @foreach ($fotos as $i => $foto)
                                        <li data-target="#carousel-ruangan-{{ $ruangan->ruangan_id }}" data-slide-to="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}"></li>
                                    @endforeach
                                </ol>
                                <div class="carousel-inner rounded" style="max-height: 260px; background: #f0f0f0;">
                                    @foreach ($fotos as $i => $foto)
                                        <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                                            <img src="{{ asset('storage/' . $foto) }}" class="d-block mx-auto" alt="Foto {{ $i + 1 }}" style="max-height: 260px; max-width: 100%; object-fit: contain;">
                                        </div>
                                    @endforeach
                                </div>
                                <a class="carousel-control-prev" href="#carousel-ruangan-{{ $ruangan->ruangan_id }}" role="button" data-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="sr-only">Previous</span>
                                </a>
                                <a class="carousel-control-next" href="#carousel-ruangan-{{ $ruangan->ruangan_id }}" role="button" data-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="sr-only">Next</span>
                                </a>
                            </div>
                            <div class="text-center mt-1">
                                <small class="text-muted">{{ count($fotos) }} foto — geser kiri/kanan</small>
                            </div>
                        @endif
                    @else
                        <div class="d-flex flex-column align-items-center justify-content-center bg-light rounded" style="height: 150px;">
                            <i class="fas fa-image fa-2x text-muted mb-2"></i>
                            <span class="text-muted">Tidak ada foto</span>
                        </div>
                    @endif
                </div>
                <div class="col-md-12 mb-4">
                    <label class="font-weight-bold text-dark">Nama</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-door-open mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $ruangan->ruangan_nama }}</span>
                    </div>
                </div>
                <div class="col-md-12 mb-4">
                    <label class="font-weight-bold text-dark">Kode</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-font mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $ruangan->ruangan_kode }}</span>
                    </div>
                </div>
                <div class="col-md-12 mb-4">
                    <label class="font-weight-bold text-dark">Kapasitas</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-hand-paper mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $ruangan->ruangan_kuota }}</span>
                    </div>
                </div>
                <div class="col-md-12 mb-4">
                    <label class="font-weight-bold text-dark">Fasilitas</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-desktop mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $ruangan->ruangan_fasilitas }}</span>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="font-weight-bold text-dark">Status</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $ruangan->ruangan_status }}</span>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="font-weight-bold text-dark">Kategori</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-tags mr-2 text-primary"></i>
                        <span class="text-secondary">{{ $ruangan->ruangan_kategori }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" data-dismiss="modal" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </button>
        </div>
    </div>
@endempty
