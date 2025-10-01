<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HunianPemohon extends Model
{
    use HasFactory;

    protected $table = 'hunian_pemohon';

    public $timestamps = false;

    protected $fillable = [
        'pemohon_id',
        'username',
        'tipe_program',
        'pernah_ikut',
        'tipe_rumah',
        'harga_rumah',
        'lokasi_rumah',
        'alasan1',
        'alasan2',
        'alasan3',
        'alasan4',
        'alasan5',
        'alasan6',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
