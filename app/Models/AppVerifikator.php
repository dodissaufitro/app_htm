<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppVerifikator extends Model
{
    use HasFactory;

    protected $table = 'app_verifikator';

    public $timestamps = false;

    protected $fillable = [
        'pemohon_id',
        'keputusan',
        'catatan',
        'created_at',
        'created_by',
    ];

    protected $casts = [
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

    public function dataPemohon()
    {
        return $this->belongsTo(DataPemohon::class, 'pemohon_id');
    }
}
