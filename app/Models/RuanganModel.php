<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RuanganModel extends Model
{
    use HasFactory;
    protected $table= 'm_ruangan'; //mendefinisikan nama tabel yang akan digunakan
    protected $primaryKey = 'ruangan_id';
    protected $fillable = ['ruangan_kode','ruangan_nama', 'ruangan_fasilitas', 'ruangan_kuota', 'ruangan_status', 'ruangan_kategori', 'ruangan_foto', 'created_at','updated_at'];

    /**
     * Req 2: Accessor untuk mendukung multi-foto.
     * ruangan_foto disimpan sebagai JSON array ["path1","path2",...].
     * Format lama (plain string) tetap didukung agar backward-compatible.
     *
     * @return string[]
     */
    public function getFotosAttribute(): array
    {
        if (is_null($this->ruangan_foto)) {
            return [];
        }
        $decoded = json_decode($this->ruangan_foto, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter($decoded));
        }
        // Format lama: plain string path
        return [$this->ruangan_foto];
    }

    public function jadwal(): BelongsToMany
    {
        // Tabel pivot: t_jadwal_ruangan
        // FK di pivot untuk Ruangan: ruangan_id
        // FK di pivot untuk Jadwal: jadwal_id
        return $this->belongsToMany(
            JadwalModel::class,
            't_jadwal_ruangan',
            'ruangan_id',
            'jadwal_id'
        )->withTimestamps();
    }

    public function pengajuans(): BelongsToMany
    {
        // Tabel pivot: t_pengajuan_ruangan (lihat PengajuanModel::ruangans() untuk arah sebaliknya)
        return $this->belongsToMany(
            PengajuanModel::class,
            't_pengajuan_ruangan',
            'ruangan_id',
            'pengajuan_id'
        )->withTimestamps();
    }
}
