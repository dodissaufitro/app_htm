<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogReport extends Model
{
    use HasFactory;

    protected $table = 'log_report';

    public $timestamps = false;

    protected $fillable = [
        'pemohon_id',
        'status_id',
        'keputusan',
        'catatan',
        'api_sent',
        'created_at',
        'created_by',
    ];

    protected $casts = [
        'api_sent' => 'boolean',
        'created_at' => 'datetime',
    ];

    protected $attributes = [
        'api_sent' => 0,
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
