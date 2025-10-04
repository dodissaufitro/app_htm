<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DaftarBank extends Model
{
    use HasFactory;

    protected $table = 'daftar_bank';

    // Use standard auto-increment integer primary key
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'nama_bank',
        'kode_bank',
        'kode_bank_legacy',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
    ];
}
