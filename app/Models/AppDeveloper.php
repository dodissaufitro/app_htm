<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppDeveloper extends Model
{
    use HasFactory;

    protected $table = 'app_developer';

    public $timestamps = false;

    protected $fillable = [
        'pemohon_id',
        'hadir',
        'idle',
        'masih_minat',
        'perubahan_unit',
        'history_visit',
        'foto_kehadiran',
        'keputusan',
        'catatan',
        'created_at',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $attributes = [
        'keputusan' => 'ditunda',
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
