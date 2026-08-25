<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Jabatan approval yang menempel ke user yang SUDAH ADA (dosen/mahasiswa),
 * menggantikan m_verifikator yang dulu berupa akun tersendiri (level VRF).
 */
class JabatanApprovalModel extends Model
{
    use HasFactory;

    protected $table = 'm_jabatan_approval';
    protected $primaryKey = 'jabatan_id';
    protected $fillable = ['user_id', 'organisasi_id', 'posisi_approval', 'urutan_approval', 'created_at', 'updated_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'user_id');
    }

    public function organisasi(): BelongsTo
    {
        return $this->belongsTo(OrganisasiModel::class, 'organisasi_id', 'organisasi_id');
    }
}
