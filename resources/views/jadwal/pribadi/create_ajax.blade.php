<form action="{{ url('/jadwal/pribadi/store_ajax') }}"
      method="POST"
      id="form-create-pribadi">

    @csrf

    <div class="modal-content">

        <div class="modal-header">

            <h5 class="modal-title">

                Tambah Jadwal Pribadi

            </h5>

            <button
                type="button"
                class="close"
                data-dismiss="modal">

                <span>&times;</span>

            </button>

        </div>

        <div class="modal-body">

            {{-- Ruangan --}}

            <div class="form-group">

                <label>Ruangan</label>

                <select
                    class="select2"
                    name="ruangan_ids[]"
                    multiple
                    required
                    style="width:100%;">

                    @foreach($ruanganList as $ruangan)

                        <option value="{{ $ruangan->ruangan_id }}">

                            {{ $ruangan->ruangan_nama }}

                        </option>

                    @endforeach

                </select>

                <small
                    class="text-danger"
                    id="error_ruangan_ids">
                </small>

            </div>

            <div class="row">

                <div class="col-md-4">

                    <label>Tanggal</label>

                    <input
                        type="date"
                        class="form-control"
                        name="jadwal_tgl"
                        required>

                    <small id="error_jadwal_tgl"
                           class="text-danger">
                    </small>

                </div>

                <div class="col-md-4">

                    <label>Jam Mulai</label>

                    <input
                        type="time"
                        class="form-control"
                        name="jadwal_jam_mulai"
                        required>

                    <small id="error_jadwal_jam_mulai"
                           class="text-danger">
                    </small>

                </div>

                <div class="col-md-4">

                    <label>Jam Selesai</label>

                    <input
                        type="time"
                        class="form-control"
                        name="jadwal_jam_selesai"
                        required>

                    <small id="error_jadwal_jam_selesai"
                           class="text-danger">
                    </small>

                </div>

            </div>

            <div class="row mt-3">

                <div class="col-md-9">

                    <label>Kegiatan</label>

                    <input
                        type="text"
                        class="form-control"
                        name="jadwal_nama"
                        required>

                    <small
                        id="error_jadwal_nama"
                        class="text-danger">
                    </small>

                </div>

                <div class="col-md-3">

                    <label>Peserta</label>

                    <input
                        type="number"
                        class="form-control"
                        name="jadwal_jumPes"
                        required>

                    <small
                        id="error_jadwal_jumPes"
                        class="text-danger">
                    </small>

                </div>

            </div>

        </div>

        <div class="modal-footer">

            <button
                class="btn btn-secondary"
                data-dismiss="modal"
                type="button">

                Batal

            </button>

            <button
                class="btn btn-success"
                type="submit">

                Simpan

            </button>

        </div>

    </div>

</form>

<script>

$('.select2').select2({

    dropdownParent:$('#modalJadwal')

});

$('#form-create-pribadi').submit(function(e){

    e.preventDefault();

    $('.text-danger').html('');

    $.ajax({

        url:$(this).attr('action'),

        type:'POST',

        data:$(this).serialize(),

        success:function(res){

            if(res.status){

                $('#modalJadwal').modal('hide');

                Swal.fire({

                    icon:'success',

                    title:'Berhasil',

                    text:res.message

                }).then(()=>{

                    location.reload();

                });

            }

            else{

                Swal.fire({

                    icon:'error',

                    title:'Gagal',

                    text:res.message

                });

            }

        },

        error:function(xhr){

            if(xhr.status==422){

                $.each(xhr.responseJSON.msgField,function(k,v){

                    $('#error_'+k).html(v[0]);

                });

            }

        }

    });

});

</script>