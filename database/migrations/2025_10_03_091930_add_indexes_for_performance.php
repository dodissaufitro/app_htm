<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('app_verifikator', function (Blueprint $table) {
            // Tambah index untuk pemohon_id karena sering digunakan dalam WHERE clause
            $table->index('pemohon_id', 'idx_app_verifikator_pemohon_id');

            // Tambah index untuk keputusan karena sering difilter
            $table->index('keputusan', 'idx_app_verifikator_keputusan');

            // Tambah index untuk created_at untuk sorting dan filtering tanggal
            $table->index('created_at', 'idx_app_verifikator_created_at');

            // Tambah index untuk created_by untuk filter berdasarkan verifikator
            $table->index('created_by', 'idx_app_verifikator_created_by');

            // Tambah composite index untuk query yang sering digunakan bersama
            $table->index(['pemohon_id', 'keputusan'], 'idx_app_verifikator_pemohon_keputusan');
        });

        // Tambah index pada tabel data_pemohon juga jika diperlukan
        Schema::table('data_pemohon', function (Blueprint $table) {
            // Tambah index untuk kolom yang sering dicari
            $table->index('id_pendaftaran', 'idx_data_pemohon_id_pendaftaran');
            $table->index('nama', 'idx_data_pemohon_nama');
            $table->index('nik', 'idx_data_pemohon_nik');
            $table->index('status_permohonan', 'idx_data_pemohon_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_verifikator', function (Blueprint $table) {
            // Drop indexes yang ditambahkan
            $table->dropIndex('idx_app_verifikator_pemohon_id');
            $table->dropIndex('idx_app_verifikator_keputusan');
            $table->dropIndex('idx_app_verifikator_created_at');
            $table->dropIndex('idx_app_verifikator_created_by');
            $table->dropIndex('idx_app_verifikator_pemohon_keputusan');
        });

        Schema::table('data_pemohon', function (Blueprint $table) {
            // Drop indexes yang ditambahkan
            $table->dropIndex('idx_data_pemohon_id_pendaftaran');
            $table->dropIndex('idx_data_pemohon_nama');
            $table->dropIndex('idx_data_pemohon_nik');
            $table->dropIndex('idx_data_pemohon_status');
        });
    }
};
