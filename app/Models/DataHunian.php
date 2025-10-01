<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataHunian extends Model
{
    use HasFactory;

    protected $table = 'data_hunian';

    public $timestamps = false;

    protected $fillable = [
        'nama_pemukiman',
        'alamat_pemukiman',
        'kode_lokasi',
        'kode_hunian',
        'tipe_hunian',
        'ukuran',
        'harga',
        'tahun5',
        'tahun10',
        'tahun15',
        'tahun20',
        'deleted',
        'create_date',
        'update_date',
    ];

    protected $casts = [
        'create_date' => 'datetime',
        'update_date' => 'datetime',
    ];

    protected $attributes = [
        'kode_lokasi' => '0',
    ];
}
