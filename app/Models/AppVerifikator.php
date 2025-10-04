<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AppVerifikator extends Model
{
    use HasFactory;

    protected $table = 'app_verifikator';

    public $timestamps = false;

    protected $fillable = [
        'pemohon_id',
        'id_data_pemohon',
        'keputusan',
        'catatan',
        'rejection_reason',
        'verified_by',
        'verified_at',
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

    /**
     * Scope untuk mencari duplicates berdasarkan pemohon_id
     */
    public function scopeWithDuplicates($query)
    {
        return $query->whereIn('pemohon_id', function ($subQuery) {
            $subQuery->select('pemohon_id')
                ->from('app_verifikator')
                ->groupBy('pemohon_id')
                ->havingRaw('COUNT(*) > 1');
        });
    }

    /**
     * Scope untuk mendapatkan record terbaru untuk setiap pemohon
     */
    public function scopeLatestPerPemohon($query)
    {
        return $query->whereIn('id', function ($subQuery) {
            $subQuery->select(DB::raw('MAX(id)'))
                ->from('app_verifikator')
                ->groupBy('pemohon_id');
        });
    }

    /**
     * Scope untuk mendapatkan record terlama untuk setiap pemohon
     */
    public function scopeOldestPerPemohon($query)
    {
        return $query->whereIn('id', function ($subQuery) {
            $subQuery->select(DB::raw('MIN(id)'))
                ->from('app_verifikator')
                ->groupBy('pemohon_id');
        });
    }

    /**
     * Get duplicate records for this pemohon
     */
    public function getDuplicatesAttribute()
    {
        return static::where('pemohon_id', $this->pemohon_id)
            ->where('id', '!=', $this->id)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Check if this record has duplicates
     */
    public function hasDuplicates(): bool
    {
        return static::where('pemohon_id', $this->pemohon_id)->count() > 1;
    }

    /**
     * Clean up duplicates for this pemohon, keeping this record
     */
    public function cleanupDuplicates(): int
    {
        $duplicates = static::where('pemohon_id', $this->pemohon_id)
            ->where('id', '!=', $this->id)
            ->get();

        $deletedCount = 0;
        foreach ($duplicates as $duplicate) {
            $duplicate->delete();
            $deletedCount++;
        }

        return $deletedCount;
    }
}
