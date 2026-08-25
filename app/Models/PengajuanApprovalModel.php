<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanApprovalModel extends Model
{
    use HasFactory;

    protected $table = 't_pengajuan_approval';
    protected $primaryKey = 'approval_id';
    protected $fillable = [
        'pengajuan_id',
        'jabatan_id',
        'urutan_tahap',
        'status_approval',
        'alasan_penolakan',
        'batas_waktu',
        'diproses_pada',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'batas_waktu' => 'datetime',
        'diproses_pada' => 'datetime',
    ];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanModel::class, 'pengajuan_id', 'pengajuan_id');
    }

    public function jabatanApproval(): BelongsTo
    {
        return $this->belongsTo(JabatanApprovalModel::class, 'jabatan_id', 'jabatan_id');
    }
}
