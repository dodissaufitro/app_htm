<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DaftarBank extends Model
{
    use HasFactory;

    protected $table = 'daftar_bank';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = true; // Enable timestamps

    protected $fillable = [
        'id',
        'nama_bank',
        'kode_bank', // Include kode_bank in fillable
    ];
}
