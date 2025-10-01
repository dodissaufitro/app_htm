<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppAkad extends Model
{
    use HasFactory;

    protected $table = 'app_akad';

    public $timestamps = false;

    protected $fillable = [
        'pemohon_id',
        'masih_minat',
        'tanggal_akad',
        'saksi',
        'notaris',
        'dana_akad',
        'no_spk',
        'foto_spk_hal_depan',
        'foto_spk_hal_belakang',
        'foto_akad',
        'keputusan',
        'catatan',
        'created_at',
        'created_by',
    ];

    protected $casts = [
        'tanggal_akad' => 'date',
        'dana_akad' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function pemohon()
    {
        return $this->belongsTo(DataPemohon::class, 'pemohon_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
