<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppBank extends Model
{
    use HasFactory;

    protected $table = 'app_bank';

    public $timestamps = false;

    protected $fillable = [
        'pemohon_id',
        'data_lengkap',
        'data_pendukung_valid',
        'bi_checking',
        'info_biaya',
        'masih_minat',
        'keputusan',
        'alasan_tolak',
        'catatan',
        'dok_pm1',
        'dok_slip_gaji',
        'created_at',
        'created_by',
    ];

    protected $casts = [
        'data_lengkap' => 'boolean',
        'data_pendukung_valid' => 'boolean',
        'bi_checking' => 'boolean',
        'info_biaya' => 'boolean',
        'created_at' => 'datetime',
    ];

    protected $attributes = [
        'masih_minat' => 'Y',
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
