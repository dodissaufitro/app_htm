<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPemohon extends Model
{
    use HasFactory;

    protected $table = 'data_pemohon';

    protected $fillable = [
        'id_pendaftaran',
        'username',
        'nik',
        'kk',
        'nama',
        'pendidikan',
        'npwp',
        'nama_npwp',
        'validasi_npwp',
        'status_npwp',
        'no_hp',
        'chkDomisili',
        'provinsi2_ktp',
        'kabupaten_ktp',
        'kecamatan_ktp',
        'kelurahan_ktp',
        'provinsi_dom',
        'kabupaten_dom',
        'kecamatan_dom',
        'kelurahan_dom',
        'alamat_dom',
        'sts_rumah',
        'korespondensi',
        'pekerjaan',
        'gaji',
        'status_kawin',
        'nik2',
        'nama2',
        'no_hp2',
        'is_couple_dki',
        'is_have_booking_kpr_dpnol',
        'tipe_unit',
        'harga_unit',
        'chkDomisili2',
        'provinsi2',
        'kabupaten2',
        'kecamatan2',
        'kelurahan2',
        'alamat2',
        'pendidikan2',
        'pekerjaan2',
        'gaji2',
        'chkPengajuan',
        'foto_ektp',
        'foto_npwp',
        'foto_kk',
        'lokasi_rumah',
        'tipe_rumah',
        'nama_blok',
        'bapenda',
        'bapenda_pasangan',
        'bapenda_pasangan_pbb',
        'reason_of_choose_location',
        'aset_hunian',
        'booking_files',
        'count_of_vehicle1',
        'count_of_vehicle2',
        'is_have_saving_bank',
        'is_have_home_credit',
        'atpid',
        'mounthly_expense1',
        'mounthly_expense2',
        'status_permohonan',
        'id_bank',
        'updated_by',
        'created_by',
    ];

    protected $casts = [
        'gaji' => 'decimal:2',
        'harga_unit' => 'decimal:2',
        'gaji2' => 'decimal:2',
        'mounthly_expense1' => 'decimal:0',
        'mounthly_expense2' => 'decimal:0',
        'is_couple_dki' => 'boolean',
        'is_have_booking_kpr_dpnol' => 'boolean',
        'is_have_saving_bank' => 'boolean',
        'is_have_home_credit' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'validasi_npwp' => 0,
        'status_npwp' => 0,
        'status_kawin' => 0,
        'chkPengajuan' => 'on',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function bank()
    {
        return $this->belongsTo(DaftarBank::class, 'id_bank');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_permohonan', 'kode');
    }

    public function appVerifikator()
    {
        return $this->hasOne(AppVerifikator::class, 'pemohon_id');
    }

    /**
     * Scope untuk filter berdasarkan urutan status
     */
    public function scopeWithStatusUrut($query, int $urut)
    {
        return $query->whereHas('status', function ($q) use ($urut) {
            $q->where('urut', $urut);
        });
    }

    /**
     * Scope untuk persetujuan (status urut = 1)
     */
    public function scopeForPersetujuan($query)
    {
        return $query->withStatusUrut(1);
    }
}
