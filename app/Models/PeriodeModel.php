<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodeModel extends Model
{
    use HasFactory;

    protected $table = 'm_periode';
    protected $primaryKey = 'periode_id';
    protected $fillable = ['tahun_ajaran', 'semester', 'tanggal_mulai', 'tanggal_selesai', 'periode_status', 'updated_by', 'created_at', 'updated_at'];
    protected $casts = ['tanggal_mulai' => 'date', 'tanggal_selesai' => 'date'];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'updated_by', 'user_id');
    }
}
