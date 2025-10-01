<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppPenetapan extends Model
{
    use HasFactory;

    protected $table = 'app_penetapan';

    public $timestamps = false;

    protected $fillable = [
        'pemohon_id',
        'masih_minat',
        'perubahan_unit',
        'keputusan',
        'catatan',
        'created_at',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $attributes = [
        'perubahan_unit' => 'N',
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
