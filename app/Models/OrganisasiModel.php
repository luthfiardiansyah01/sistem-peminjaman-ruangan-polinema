<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrganisasiModel extends Model
{
    use HasFactory;

    protected $table = 'm_organisasi';
    protected $primaryKey = 'organisasi_id';
    protected $fillable = ['organisasi_kode', 'organisasi_nama', 'organisasi_logo', 'created_at', 'updated_at'];

    public function jabatanApprovals(): HasMany
    {
        return $this->hasMany(JabatanApprovalModel::class, 'organisasi_id', 'organisasi_id');
    }

    public function mahasiswas(): BelongsToMany
    {
        return $this->belongsToMany(
            MahasiswaModel::class,
            'm_mahasiswa_organisasi',
            'organisasi_id',
            'mahasiswa_id'
        )->withTimestamps();
    }
}
