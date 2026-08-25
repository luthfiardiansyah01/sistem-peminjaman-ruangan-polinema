@empty($jadwal)

<div class="modal-content">

    <div class="modal-header">

        <h5 class="modal-title">
            Kesalahan
        </h5>

        <button
            type="button"
            class="close"
            data-dismiss="modal">

            <span>&times;</span>

        </button>

    </div>

    <div class="modal-body">

        <div class="alert alert-danger">

            Data jadwal tidak ditemukan.

        </div>

    </div>

</div>

@else

<form
    id="form-edit-pribadi"
    method="POST"
    action="{{ url('/jadwal/pribadi/'.$jadwal->jadwal_id.'/update_ajax') }}">

    @csrf
    @method('PUT')

    <div class="modal-content">

        <div class="modal-header">

            <h5 class="modal-title">

                Edit Jadwal Pribadi

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
                    multiple
                    name="ruangan_ids[]"
                    required
                    style="width:100%;">

                    @php

                        $selected =
                        $jadwal->ruangans
                               ->pluck('ruangan_id')
                               ->toArray();

                    @endphp

                    @foreach($ruanganList as $ruangan)

                        <option
                            value="{{ $ruangan->ruangan_id }}"
                            {{ in_array($ruangan->ruangan_id,$selected)
                                ? 'selected'
                                : '' }}>

                            {{ $ruangan->ruangan_nama }}

                        </option>

                    @endforeach

                </select>

                <small
                    id="error_ruangan_ids"
                    class="text-danger">
                </small>

            </div>

            <div class="row">

                <div class="col-md-4">

                    <label>Tanggal</label>

                    <input
                        type="date"
                        name="jadwal_tgl"
                        class="form-control"
                        value="{{ $jadwal->jadwal_tgl }}"
                        required>

                    <small
                        id="error_jadwal_tgl"
                        class="text-danger">
                    </small>

                </div>

                <div class="col-md-4">

                    <label>Jam Mulai</label>

                    <input
                        type="time"
                        name="jadwal_jam_mulai"
                        class="form-control"
                        value="{{ \Carbon\Carbon::parse($jadwal->jadwal_jam_mulai)->format('H:i') }}"
                        required>

                    <small
                        id="error_jadwal_jam_mulai"
                        class="text-danger">
                    </small>

                </div>

                <div class="col-md-4">

                    <label>Jam Selesai</label>

                    <input
                        type="time"
                        name="jadwal_jam_selesai"
                        class="form-control"
                        value="{{ \Carbon\Carbon::parse($jadwal->jadwal_jam_selesai)->format('H:i') }}"
                        required>

                    <small
                        id="error_jadwal_jam_selesai"
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
                        value="{{ $jadwal->jadwal_nama }}"
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
                        value="{{ $jadwal->jadwal_jumPes }}"
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
                type="button"
                class="btn btn-secondary"
                data-dismiss="modal">

                Batal

            </button>

            <button
                type="submit"
                class="btn btn-warning">

                Simpan

            </button>

        </div>

    </div>

</form>

<script>

$('.select2').select2({

    dropdownParent:$('#modalJadwal')

});

$('#form-edit-pribadi').submit(function(e){

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

            else{

                Swal.fire({

                    icon:'error',

                    title:'Error',

                    text:'Terjadi kesalahan.'

                });

            }

        }

    });

});

</script>

@endempty