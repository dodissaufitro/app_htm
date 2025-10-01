<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppBast extends Model
{
    use HasFactory;

    protected $table = 'app_bast';

    public $timestamps = false;

    protected $fillable = [
        'pemohon_id',
        'no_bast',
        'tgl_bast',
        'file_bast',
        'foto_bast',
        'foto_serah_kunci',
        'menerima_hasil_kerja',
        'komplain',
        'sesuai',
        'keputusan',
        'catatan',
        'verifikasi_pemohon',
        'dihuni',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $casts = [
        'tgl_bast' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'verifikasi_pemohon' => 'belum',
        'dihuni' => 'belum',
    ];

    public function pemohon()
    {
        return $this->belongsTo(DataPemohon::class, 'pemohon_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
