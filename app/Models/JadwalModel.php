<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JadwalModel extends Model
{
    use HasFactory;
    protected $table= 't_jadwal'; //mendefinisikan nama tabel yang akan digunakan
    protected $primaryKey = 'jadwal_id';
    protected $fillable = ['user_id','jadwal_nama','jadwal_tgl','jadwal_jam_mulai','jadwal_jam_selesai','jadwal_jumPes','jadwal_status','foto_penggunaan','foto_kebersihan','foto_kunci'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke pengajuan yang menghasilkan jadwal ini.
     * Catatan: t_pengajuan TIDAK memiliki kolom jadwal_id. Jadwal dibuat sebagai entitas
     * baru saat pengajuan diterima (lihat PengajuanService::createJadwalFromPengajuan()),
     * dengan pencocokan heuristik berdasarkan user_id + nama + tanggal kegiatan.
     * Relasi hasMany standar tidak bisa digunakan — gunakan query langsung di Service.
     *
     * @deprecated Relasi ini tidak dapat digunakan karena t_pengajuan tidak memiliki
     *             foreign key jadwal_id. Gunakan PengajuanService::operationalStatusFor()
     *             untuk matching heuristik.
     */
    public function pengajuans()
    {
        // Tidak ada FK jadwal_id di t_pengajuan — method ini dipertahankan untuk
        // backward compatibility, namun akan selalu mengembalikan collection kosong.
        return $this->hasMany(PengajuanModel::class, 'jadwal_id', 'jadwal_id')
            ->whereRaw('1 = 0');
    }

    public function ruangans(): BelongsToMany
    {
        // Tabel pivot: t_jadwal_ruangan
        // FK di pivot untuk Jadwal: jadwal_id
        // FK di pivot untuk Ruangan: ruangan_id
        return $this->belongsToMany(
            RuanganModel::class,
            't_jadwal_ruangan',
            'jadwal_id',
            'ruangan_id'
        )->withTimestamps();
    }

    /**
     * Tentukan semester dari jadwal_tgl (FR-10.1).
     * Konvensi umum: Ganjil = Agustus–Januari, Genap = Februari–Juli.
     */
    public function getSemesterAttribute(): string
    {
        $tanggal = $this->jadwal_tgl;
        if ($tanggal instanceof \Carbon\Carbon) {
            $month = (int) $tanggal->format('n');
        } else {
            $month = (int) date('n', strtotime((string) $tanggal));
        }
        return ($month >= 8 || $month === 1) ? 'Ganjil' : 'Genap';
    }

    /**
     * Mengambil data pengajuan terkait secara manual menggunakan Accessor.
     * Cara ini aman untuk pencocokan multi-kolom dibanding relasi hasOne().
     */
    public function getPengajuanTerkaitAttribute()
    {
        // Pastikan format tanggal diseragamkan menjadi Y-m-d untuk pencocokan
        $tanggal = $this->jadwal_tgl instanceof \Carbon\Carbon 
            ? $this->jadwal_tgl->format('Y-m-d') 
            : date('Y-m-d', strtotime($this->jadwal_tgl));

        return \App\Models\PengajuanModel::with(['ketuaPelaksana.dosen', 'ketuaPelaksana.mahasiswa', 'organisasi'])
            ->where('user_id', $this->user_id)
            ->where('pengajuan_nama', $this->jadwal_nama)
            ->whereDate('pengajuan_tgl', $tanggal)
            ->first();
    }
}
