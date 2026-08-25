<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengajuanModel extends Model
{
    use HasFactory;

    protected $table = 't_pengajuan';
    protected $primaryKey = 'pengajuan_id';
    protected $fillable = [
        'user_id',
        'organisasi_id',
        'ketua_pelaksana_user_id',
        'formulir_id',
        'pengajuan_nama',
        'pengajuan_tgl',
        'pengajuan_jam_mulai',
        'pengajuan_jam_selesai',
        'pengajuan_jumPes',
        'pengajuan_keterangan',
        'pengajuan_status',
        'nomor_surat',
        'catatan_verifikator',
        'created_at',
        'updated_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'user_id');
    }

    public function organisasi(): BelongsTo
    {
        return $this->belongsTo(OrganisasiModel::class, 'organisasi_id', 'organisasi_id');
    }

    public function formulir(): BelongsTo
    {
        return $this->belongsTo(FormulirModel::class, 'formulir_id', 'formulir_id');
    }

    public function ketuaPelaksana(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'ketua_pelaksana_user_id', 'user_id');
    }

    public function ruangans(): BelongsToMany
    {
        return $this->belongsToMany(
            RuanganModel::class,
            't_pengajuan_ruangan',
            'pengajuan_id',
            'ruangan_id'
        )->withTimestamps();
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PengajuanApprovalModel::class, 'pengajuan_id', 'pengajuan_id');
    }

    public function panitias(): HasMany
    {
        return $this->hasMany(PengajuanPanitiaModel::class, 'pengajuan_id', 'pengajuan_id');
    }

}
