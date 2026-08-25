<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanPanitiaModel extends Model
{
    use HasFactory;

    protected $table = 't_pengajuan_panitia';
    protected $primaryKey = 'panitia_id';
    protected $fillable = ['pengajuan_id', 'user_id', 'keterangan', 'created_at', 'updated_at'];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanModel::class, 'pengajuan_id', 'pengajuan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'user_id');
    }
}
